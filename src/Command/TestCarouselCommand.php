<?php

namespace App\Command;

use App\Repository\CarouselImageRepository;
use App\Service\CarouselService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-carousel',
    description: 'Teste le système de carrousel',
)]
class TestCarouselCommand extends Command
{
    public function __construct(
        private CarouselService $carouselService,
        private CarouselImageRepository $carouselImageRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test du système de carrousel');

        // Test 1: Récupérer les images actives
        $io->section('1. Images actives du carrousel');
        $activeImages = $this->carouselService->getActiveImages();

        if (empty($activeImages)) {
            $io->warning('Aucune image active trouvée dans le carrousel.');
        } else {
            $io->success(sprintf('%d images actives trouvées :', count($activeImages)));
            foreach ($activeImages as $image) {
                $io->text(sprintf(
                    '- Position %d: %s (%s) - %s',
                    $image->getPosition(),
                    $image->getTitle(),
                    $image->getImagePath(),
                    $image->isActive() ? 'Actif' : 'Inactif'
                ));
            }
        }

        // Test 2: Prochaine position disponible
        $io->section('2. Prochaine position disponible');
        $nextPosition = $this->carouselService->getNextPosition();
        $io->info(sprintf('Prochaine position disponible : %d', $nextPosition));

        // Test 3: Toutes les images (actives et inactives)
        $io->section('3. Toutes les images du carrousel');
        $allImages = $this->carouselImageRepository->findAll();

        if (empty($allImages)) {
            $io->warning('Aucune image trouvée dans la base de données.');
        } else {
            $io->success(sprintf('%d images au total :', count($allImages)));
            foreach ($allImages as $image) {
                $status = $image->isActive() ? '✅ Actif' : '❌ Inactif';
                $io->text(sprintf(
                    '- ID %d | Position %d | %s | %s | %s',
                    $image->getId(),
                    $image->getPosition(),
                    $image->getTitle(),
                    $image->getImagePath(),
                    $status
                ));
            }
        }

        $io->success('Test du système de carrousel terminé avec succès !');
        return Command::SUCCESS;
    }
}
