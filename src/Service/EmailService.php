<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Message\SendEmailMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Psr\Log\LoggerInterface;

class EmailService
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private UserRepository $userRepository,
        private LoggerInterface $logger,
        private MailerInterface $mailer
    ) {}

    public function getUserRepository(): UserRepository
    {
        return $this->userRepository;
    }

    public function getMailer(): MailerInterface
    {
        return $this->mailer;
    }

    /**
     * Envoie un email de manière asynchrone
     */
    public function sendToUserAsync(User $user, string $subject, ?string $htmlContent = null, ?string $textContent = null): void
    {
        $message = new SendEmailMessage(
            $user->getId(),
            $subject,
            $textContent // SendEmailMessage attend textContent en 3ème paramètre
        );

        $this->messageBus->dispatch($message);

        $this->logger->info('Email queued', [
            'user_id' => $user->getId(),
            'subject' => $subject
        ]);
    }

    /**
     * Envoie des emails à plusieurs utilisateurs de manière asynchrone
     */
    public function sendToUsersAsync(array $users, string $subject, ?string $htmlContent = null, ?string $textContent = null): int
    {
        $count = 0;
        foreach ($users as $user) {
            $this->sendToUserAsync($user, $subject, $htmlContent, $textContent);
            $count++;
        }

        $this->logger->info('Emails queued', ['count' => $count]);
        return $count;
    }

    /**
     * Envoie à tous les utilisateurs selon des critères
     */
    public function sendToAllUsersAsync(
        string $subject,
        ?string $htmlContent = null,
        ?string $textContent = null,
        ?array $filters = null
    ): int {
        $users = $this->getUsersByFilters($filters);
        return $this->sendToUsersAsync($users, $subject, $htmlContent, $textContent);
    }

    /**
     * Récupère les utilisateurs selon des filtres
     */
    public function getUsersByFilters(?array $filters = null): array
    {
        if (!$filters) {
            return $this->userRepository->findAll();
        }

        $criteria = [];

        if (isset($filters['statut']) && !empty($filters['statut'])) {
            $criteria['statut'] = $filters['statut'];
        }

        if (isset($filters['isInvestisseur']) && $filters['isInvestisseur'] !== null) {
            $criteria['isInvestisseur'] = $filters['isInvestisseur'];
        }

        if (isset($filters['isIntraday']) && $filters['isIntraday'] !== null) {
            $criteria['isIntraday'] = $filters['isIntraday'];
        }

        if (isset($filters['hasTemporaryInvestorAccess']) && $filters['hasTemporaryInvestorAccess'] !== null) {
            $criteria['hasTemporaryInvestorAccess'] = $filters['hasTemporaryInvestorAccess'];
        }

        return $this->userRepository->findBy($criteria);
    }

    /**
     * Récupère le nombre d'utilisateurs selon des filtres
     */
    public function countUsersByFilters(?array $filters = null): int
    {
        return count($this->getUsersByFilters($filters));
    }

    /**
     * Envoie des emails directement (sans Messenger) par lots pour éviter le timeout
     * Utile quand le transport Messenger n'est pas compatible (ex: MariaDB/MySQL < 8)
     */
    public function sendToUsersDirect(array $users, string $subject, ?string $htmlContent = null, ?string $textContent = null, int $batchSize = 5): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        $totalUsers = count($users);
        $batches = array_chunk($users, $batchSize);
        $batchCount = count($batches);

        foreach ($batches as $batchIndex => $batch) {
            foreach ($batch as $user) {
                try {
                    $email = (new Email())
                        ->from(new Address('no-reply@zenbourse.fr', 'Zenbourse'))
                        ->to(new Address($user->getEmail(), $user->getFirstname() . ' ' . $user->getLastname()))
                        ->subject($subject);

                    if ($htmlContent) {
                        $email->html($htmlContent);
                    }
                    if ($textContent) {
                        $email->text($textContent);
                    }
                    if (!$htmlContent && !$textContent) {
                        throw new \InvalidArgumentException('Au moins un contenu (HTML ou texte) doit être fourni');
                    }

                    $this->mailer->send($email);
                    $results['success']++;

                    // Log seulement tous les 10 emails pour éviter de surcharger les logs
                    if ($results['success'] % 10 === 0) {
                        $this->logger->info('Email sent directly (progress)', [
                            'sent' => $results['success'],
                            'total' => $totalUsers,
                            'subject' => $subject
                        ]);
                    }
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'user_id' => $user->getId(),
                        'email' => $user->getEmail(),
                        'error' => $e->getMessage()
                    ];

                    $this->logger->error('Email failed (direct send)', [
                        'user_id' => $user->getId(),
                        'email' => $user->getEmail(),
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Pause minimale entre les lots (réduite pour accélérer)
            if ($batchIndex < $batchCount - 1) {
                usleep(50000); // 0.05 seconde au lieu de 0.1
            }
        }

        $this->logger->info('Bulk email finished', [
            'total' => $totalUsers,
            'success' => $results['success'],
            'failed' => $results['failed'],
            'subject' => $subject
        ]);

        return $results;
    }
}
