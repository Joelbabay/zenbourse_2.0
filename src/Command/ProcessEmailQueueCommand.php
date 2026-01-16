<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\EmailQueueService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;

#[AsCommand(
    name: 'app:process-email-queue',
    description: 'Traite la queue d\'emails en attente'
)]
class ProcessEmailQueueCommand extends Command
{
    public function __construct(
        private EmailQueueService $emailQueueService,
        private UserRepository $userRepository,
        private MailerInterface $mailer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Nombre maximum d\'emails à traiter', 50)
            ->addOption('clean', null, InputOption::VALUE_NONE, 'Nettoie les emails échoués de plus de 7 jours')
            ->setHelp('Cette commande traite les emails mis en queue par le système d\'envoi en masse.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Nettoyage optionnel
        if ($input->getOption('clean')) {
            $cleaned = $this->emailQueueService->cleanOldFailed();
            $io->info(sprintf('%d fichiers d\'emails échoués supprimés', $cleaned));
        }

        // Afficher les stats de la queue
        $stats = $this->emailQueueService->getQueueStats();
        $io->title('Statistiques de la queue');
        $io->table(
            ['Statut', 'Nombre'],
            [
                ['En attente', $stats['pending']],
                ['En cours', $stats['processing']],
                ['Échoués', $stats['failed']],
                ['Total', $stats['total']],
            ]
        );

        if ($stats['total'] === 0) {
            $io->success('Aucun email en queue');
            return Command::SUCCESS;
        }

        $limit = (int) $input->getOption('limit');
        $io->info(sprintf('Traitement de maximum %d emails...', $limit));

        // Traiter la queue
        $results = $this->emailQueueService->processQueue($this->userRepository, $this->mailer, $limit);

        // Afficher les résultats
        $io->success(sprintf(
            'Traitement terminé : %d traité(s), %d succès, %d échec(s)',
            $results['processed'],
            $results['success'],
            $results['failed']
        ));

        if (!empty($results['errors']) && $output->isVerbose()) {
            $io->section('Erreurs rencontrées');
            foreach ($results['errors'] as $error) {
                $io->error(sprintf('User ID %s: %s', $error['user_id'], $error['error']));
            }
        }

        // Afficher les stats restantes
        $remainingStats = $this->emailQueueService->getQueueStats();
        if ($remainingStats['total'] > 0) {
            $io->note(sprintf(
                'Il reste %d email(s) en queue (dont %d en attente)',
                $remainingStats['total'],
                $remainingStats['pending']
            ));
        }

        return Command::SUCCESS;
    }
}
