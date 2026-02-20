<?php

namespace App\Command;

use App\Repository\VisitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:diagnose-visits',
    description: 'Diagnostique le système de tracking des visites'
)]
class DiagnoseVisitsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private VisitRepository $visitRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Diagnostic du système de tracking des visites');

        // Vérifier si la table existe
        $connection = $this->entityManager->getConnection();
        $schemaManager = $connection->createSchemaManager();
        
        try {
            $tables = $schemaManager->listTableNames();
            $tableExists = in_array('visits', $tables);
            
            if ($tableExists) {
                $io->success('✓ La table "visits" existe dans la base de données');
                
                // Compter les visites
                $totalVisits = $this->visitRepository->count([]);
                $io->info("Nombre total de visites enregistrées : {$totalVisits}");
                
                // Afficher les dernières visites
                $recentVisits = $this->visitRepository->createQueryBuilder('v')
                    ->orderBy('v.visitedAt', 'DESC')
                    ->setMaxResults(5)
                    ->getQuery()
                    ->getResult();
                
                if (count($recentVisits) > 0) {
                    $io->section('Dernières visites :');
                    foreach ($recentVisits as $visit) {
                        $io->text(sprintf(
                            '- [%s] Session: %s, IP: %s, Bot: %s, Admin: %s',
                            $visit->getVisitedAt()->format('Y-m-d H:i:s'),
                            substr($visit->getSessionId() ?? 'N/A', 0, 20),
                            $visit->getIpAddress() ?? 'N/A',
                            $visit->isBot() ? 'Oui' : 'Non',
                            $visit->isAdmin() ? 'Oui' : 'Non'
                        ));
                    }
                } else {
                    $io->warning('Aucune visite enregistrée pour le moment');
                }
                
                // Afficher les stats
                $stats = $this->visitRepository->getVisitStats();
                $io->section('Statistiques actuelles :');
                $io->table(
                    ['Période', 'Visites'],
                    [
                        ['Aujourd\'hui', $stats['today']],
                        ['Cette semaine', $stats['this_week']],
                        ['Ce mois', $stats['this_month']],
                        ['Total', $stats['total']],
                    ]
                );
            } else {
                $io->error('✗ La table "visits" n\'existe pas dans la base de données');
                $io->note('Exécutez la migration : php bin/console doctrine:migrations:migrate');
            }
        } catch (\Exception $e) {
            $io->error('Erreur lors de la vérification : ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
