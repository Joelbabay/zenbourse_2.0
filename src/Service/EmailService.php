<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Psr\Log\LoggerInterface;

class EmailService
{
    private MailerInterface $mailer;
    private UserRepository $userRepository;
    private LoggerInterface $logger;
    private string $fromEmail;
    private string $fromName;

    public function __construct(
        MailerInterface $mailer,
        UserRepository $userRepository,
        LoggerInterface $logger,
        ?string $fromEmail = null,
        ?string $fromName = null
    ) {
        $this->mailer = $mailer;
        $this->userRepository = $userRepository;
        $this->logger = $logger;
        $this->fromEmail = $fromEmail ?? 'no-reply@zenbourse.fr';
        $this->fromName = $fromName ?? 'Zenbourse';
    }

    /**
     * Envoie un email à un utilisateur spécifique
     */
    public function sendToUser(User $user, string $subject, ?string $htmlContent = null, ?string $textContent = null): bool
    {
        try {
            $email = (new Email())
                ->from(new Address($this->fromEmail, $this->fromName))
                ->to(new Address($user->getEmail(), $user->getFirstname() . ' ' . $user->getLastname()))
                ->subject($subject);

            if ($htmlContent) {
                $email->html($htmlContent);
            }

            if ($textContent) {
                $email->text($textContent);
            }

            // Si ni HTML ni texte, utiliser le texte comme fallback
            if (!$htmlContent && !$textContent) {
                throw new \InvalidArgumentException('Au moins un contenu (HTML ou texte) doit être fourni');
            }

            $this->mailer->send($email);
            $this->logger->info('Email envoyé avec succès', [
                'to' => $user->getEmail(),
                'subject' => $subject
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'envoi de l\'email', [
                'to' => $user->getEmail(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Relancer l'exception pour que le contrôleur puisse la gérer
            throw $e;
        }
    }

    /**
     * Envoie un email à plusieurs utilisateurs
     */
    public function sendToUsers(array $users, string $subject, ?string $htmlContent = null, ?string $textContent = null): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($users as $user) {
            try {
                if ($this->sendToUser($user, $subject, $htmlContent, $textContent)) {
                    $results['success']++;
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = $user->getEmail() . ' (' . $e->getMessage() . ')';
            }
        }

        return $results;
    }

    /**
     * Envoie un email à tous les utilisateurs selon des critères
     */
    public function sendToAllUsers(
        string $subject,
        ?string $htmlContent = null,
        ?string $textContent = null,
        ?array $filters = null
    ): array {
        $users = $this->getUsersByFilters($filters);
        return $this->sendToUsers($users, $subject, $htmlContent, $textContent);
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
}
