<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

/**
 * Service de queue simple basé sur des fichiers pour les emails
 * Permet un traitement asynchrone sans dépendre de Messenger/Doctrine
 */
class EmailQueueService
{
    private string $queueDirectory;

    public function __construct(
        private LoggerInterface $logger,
        string $projectDir
    ) {
        $this->queueDirectory = $projectDir . '/var/email_queue';
        
        // Créer le répertoire s'il n'existe pas
        $filesystem = new Filesystem();
        if (!$filesystem->exists($this->queueDirectory)) {
            $filesystem->mkdir($this->queueDirectory, 0755);
        }
    }

    /**
     * Ajoute des emails à la queue
     */
    public function queueEmails(array $users, string $subject, ?string $textContent): int
    {
        $queued = 0;
        $timestamp = time();
        
        foreach ($users as $user) {
            if (!$user instanceof User) {
                continue;
            }
            
            $filename = $this->queueDirectory . '/email_' . $timestamp . '_' . uniqid() . '_' . $user->getId() . '.json';
            $data = [
                'user_id' => $user->getId(),
                'user_email' => $user->getEmail(),
                'user_firstname' => $user->getFirstname(),
                'user_lastname' => $user->getLastname(),
                'subject' => $subject,
                'text_content' => $textContent,
                'created_at' => $timestamp,
                'status' => 'pending'
            ];
            
            if (file_put_contents($filename, json_encode($data, JSON_UNESCAPED_UNICODE)) !== false) {
                $queued++;
            }
        }

        $this->logger->info('Emails queued to file system', [
            'count' => $queued,
            'total' => count($users),
            'subject' => $subject
        ]);

        return $queued;
    }

    /**
     * Traite les emails en queue (à appeler via une commande)
     */
    public function processQueue(UserRepository $userRepository, MailerInterface $mailer, int $limit = 50): array
    {
        $results = [
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        $files = glob($this->queueDirectory . '/email_*.json');
        
        if (empty($files)) {
            return $results;
        }

        // Trier par date de création (plus anciens en premier)
        usort($files, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });

        // Limiter le nombre de fichiers traités
        $files = array_slice($files, 0, $limit);

        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            
            if (!$data || !isset($data['user_id'])) {
                // Fichier invalide, on le supprime
                @unlink($file);
                continue;
            }

            // Marquer comme en cours de traitement
            $data['status'] = 'processing';
            file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE));

            try {
                // Récupérer l'utilisateur
                $user = $userRepository->find($data['user_id']);
                
                if (!$user) {
                    throw new \Exception('User not found: ' . $data['user_id']);
                }

                // Envoyer l'email
                $email = (new Email())
                    ->from(new Address('no-reply@zenbourse.fr', 'Zenbourse'))
                    ->to(new Address($user->getEmail(), $user->getFirstname() . ' ' . $user->getLastname()))
                    ->subject($data['subject'])
                    ->text($data['text_content']);

                $mailer->send($email);
                
                // Marquer comme traité et supprimer
                @unlink($file);
                $results['processed']++;
                $results['success']++;

                // Log tous les 10 emails
                if ($results['success'] % 10 === 0) {
                    $this->logger->info('Queue processing progress', [
                        'processed' => $results['success'],
                        'subject' => $data['subject']
                    ]);
                }
            } catch (\Exception $e) {
                // Marquer comme échoué
                $data['status'] = 'failed';
                $data['error'] = $e->getMessage();
                $data['failed_at'] = time();
                file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE));
                
                $results['failed']++;
                $results['errors'][] = [
                    'file' => basename($file),
                    'user_id' => $data['user_id'],
                    'error' => $e->getMessage()
                ];
                
                $this->logger->error('Failed to process queued email', [
                    'file' => $file,
                    'user_id' => $data['user_id'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->logger->info('Queue processing finished', [
            'processed' => $results['processed'],
            'success' => $results['success'],
            'failed' => $results['failed']
        ]);

        return $results;
    }

    /**
     * Compte les emails en queue
     */
    public function countQueued(): int
    {
        $files = glob($this->queueDirectory . '/email_*.json');
        return $files ? count($files) : 0;
    }

    /**
     * Compte les emails par statut
     */
    public function getQueueStats(): array
    {
        $files = glob($this->queueDirectory . '/email_*.json');
        $stats = [
            'pending' => 0,
            'processing' => 0,
            'failed' => 0,
            'total' => 0
        ];

        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && isset($data['status'])) {
                $stats[$data['status']] = ($stats[$data['status']] ?? 0) + 1;
            }
            $stats['total']++;
        }

        return $stats;
    }

    /**
     * Nettoie les fichiers échoués de plus de 7 jours
     */
    public function cleanOldFailed(int $daysOld = 7): int
    {
        $files = glob($this->queueDirectory . '/email_*.json');
        $cleaned = 0;
        $cutoffTime = time() - ($daysOld * 24 * 60 * 60);

        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && isset($data['status']) && $data['status'] === 'failed') {
                $failedAt = $data['failed_at'] ?? filemtime($file);
                if ($failedAt < $cutoffTime) {
                    @unlink($file);
                    $cleaned++;
                }
            }
        }

        return $cleaned;
    }
}
