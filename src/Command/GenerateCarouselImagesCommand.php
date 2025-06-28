<?php

namespace App\Command;

use App\Entity\CarouselImage;
use App\Repository\CarouselImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-carousel-images',
    description: 'Génère des images de test pour le carrousel',
)]
class GenerateCarouselImagesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CarouselImageRepository $carouselImageRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Vérifier s'il y a déjà des images
        $existingImages = $this->carouselImageRepository->findAll();
        if (!empty($existingImages)) {
            $io->warning('Des images de carrousel existent déjà. Voulez-vous les supprimer et en créer de nouvelles ? (y/N)');
            $answer = $io->askHidden('', 'N');
            if (strtolower($answer) !== 'y') {
                $io->info('Opération annulée.');
                return Command::SUCCESS;
            }

            // Supprimer les images existantes
            foreach ($existingImages as $image) {
                $this->entityManager->remove($image);
            }
            $this->entityManager->flush();
            $io->info('Images existantes supprimées.');
        }

        // Images de test
        $testImages = [
            [
                'title' => 'Bannière 1 - Promotion spéciale',
                'imagePath' => 'images/home/banniere/pub1.jpg',
                'altText' => 'Promotion spéciale Zenbourse 50',
                'position' => 1,
                'isActive' => true
            ],
            [
                'title' => 'Bannière 2 - Méthodes d\'investissement',
                'imagePath' => 'images/home/banniere/pub2.jpg',
                'altText' => 'Découvrez nos méthodes d\'investissement',
                'position' => 2,
                'isActive' => true
            ],
            [
                'title' => 'Bannière 3 - Formation trading',
                'imagePath' => 'images/home/banniere/pub3.jpg',
                'altText' => 'Formation trading et analyse technique',
                'position' => 3,
                'isActive' => true
            ],
            [
                'title' => 'Bannière 4 - Bibliothèque d\'exemples',
                'imagePath' => 'images/home/banniere/pub4.jpg',
                'altText' => 'Bibliothèque d\'exemples et cas pratiques',
                'position' => 4,
                'isActive' => true
            ]
        ];

        $createdCount = 0;
        foreach ($testImages as $imageData) {
            $carouselImage = new CarouselImage();
            $carouselImage->setTitle($imageData['title']);
            $carouselImage->setImagePath($imageData['imagePath']);
            $carouselImage->setAltText($imageData['altText']);
            $carouselImage->setPosition($imageData['position']);
            $carouselImage->setIsActive($imageData['isActive']);

            $this->entityManager->persist($carouselImage);
            $createdCount++;
        }

        $this->entityManager->flush();

        $io->success(sprintf('%d images de carrousel ont été créées avec succès !', $createdCount));
        $io->info('Vous pouvez maintenant gérer ces images depuis l\'interface d\'administration.');

        return Command::SUCCESS;
    }
}
