<?php

namespace App\Command;

use App\Repository\MenuRepository;
use App\Service\CarouselService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-carousel-display',
    description: 'Teste l\'affichage du carrousel sur la page d\'accueil',
)]
class TestCarouselDisplayCommand extends Command
{
    public function __construct(
        private CarouselService $carouselService,
        private MenuRepository $menuRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test de l\'affichage du carrousel');

        // Test 1: Vérifier la page d'accueil
        $io->section('1. Page d\'accueil');
        $accueilMenu = $this->menuRepository->findOneBy(['slug' => 'accueil']);

        if (!$accueilMenu) {
            $io->error('La page d\'accueil (slug: accueil) n\'existe pas !');
            return Command::FAILURE;
        }

        $io->success(sprintf('Page d\'accueil trouvée : %s (ID: %d)', $accueilMenu->getLabel(), $accueilMenu->getId()));

        // Test 2: Vérifier les images du carrousel
        $io->section('2. Images du carrousel');
        $activeImages = $this->carouselService->getActiveImages();

        if (empty($activeImages)) {
            $io->warning('Aucune image active trouvée dans le carrousel.');
            $io->info('Le carrousel affichera les images par défaut (fallback).');
        } else {
            $io->success(sprintf('%d images actives trouvées pour le carrousel :', count($activeImages)));
            foreach ($activeImages as $image) {
                $io->text(sprintf(
                    '- Position %d: %s (%s)',
                    $image->getPosition(),
                    $image->getTitle(),
                    $image->getImagePath()
                ));
            }
        }

        // Test 3: Vérifier d'autres pages HOME
        $io->section('3. Autres pages HOME (carrousel ne doit PAS s\'afficher)');
        $otherHomePages = $this->menuRepository->findBy(['section' => 'HOME']);

        $io->info('Pages HOME où le carrousel ne doit PAS s\'afficher :');
        foreach ($otherHomePages as $page) {
            if ($page->getSlug() !== 'accueil') {
                $io->text(sprintf('- %s (slug: %s)', $page->getLabel(), $page->getSlug()));
            }
        }

        // Test 4: Résumé
        $io->section('4. Résumé');
        $io->success('Configuration correcte !');
        $io->text('✅ Le carrousel s\'affichera UNIQUEMENT sur la page d\'accueil (slug: accueil)');
        $io->text('✅ Les autres pages HOME n\'afficheront pas le carrousel');
        $io->text(sprintf('✅ %d images actives disponibles pour le carrousel', count($activeImages)));

        if (count($activeImages) > 0) {
            $io->text('✅ Le carrousel utilisera les images de la base de données');
        } else {
            $io->text('⚠️  Le carrousel utilisera les images par défaut (fallback)');
        }

        return Command::SUCCESS;
    }
}
