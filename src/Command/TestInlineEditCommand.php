<?php

namespace App\Command;

use App\Entity\StockExample;
use App\Repository\StockExampleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-inline-edit',
    description: 'Test de l\'édition inline des stock examples'
)]
class TestInlineEditCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StockExampleRepository $stockExampleRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test de l\'édition inline des stock examples');

        // Vérifier qu'il y a des stock examples
        $stocks = $this->stockExampleRepository->findAll();

        if (empty($stocks)) {
            $io->error('Aucun stock example trouvé. Créez d\'abord des exemples.');
            return Command::FAILURE;
        }

        $io->success(sprintf('Trouvé %d stock examples', count($stocks)));

        // Afficher les premiers exemples
        $io->section('Exemples disponibles :');
        foreach (array_slice($stocks, 0, 3) as $stock) {
            $io->text(sprintf(
                'ID: %d | Titre: %s | Ticker: %s | Catégorie: %s',
                $stock->getId(),
                $stock->getTitle(),
                $stock->getTicker(),
                $stock->getCategory()
            ));
        }

        // Tester la mise à jour d'un champ
        $io->section('Test de mise à jour :');
        $firstStock = $stocks[0];
        $originalTitle = $firstStock->getTitle();
        $testTitle = $originalTitle . ' (TEST)';

        $io->text(sprintf('Mise à jour du titre de "%s" vers "%s"', $originalTitle, $testTitle));

        $firstStock->setTitle($testTitle);
        $this->entityManager->flush();

        $io->success('Mise à jour effectuée avec succès');

        // Restaurer le titre original
        $firstStock->setTitle($originalTitle);
        $this->entityManager->flush();

        $io->success('Titre restauré');

        $io->section('Instructions pour tester l\'interface :');
        $io->text([
            '1. Allez dans l\'admin : /admin',
            '2. Cliquez sur "Exemples de stocks"',
            '3. Cliquez sur le bouton "Édition Inline"',
            '4. Cliquez sur n\'importe quel champ pour le modifier',
            '5. Les modifications sont sauvegardées automatiquement après 2 secondes',
            '6. Ou utilisez les boutons "Sauvegarder" / "Annuler"'
        ]);

        $io->success('Test terminé avec succès !');

        return Command::SUCCESS;
    }
}
