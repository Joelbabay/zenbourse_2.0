<?php

namespace App\Command;

use App\Repository\CarouselImageRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-carousel-images',
    description: 'Teste les images du carrousel et vérifie leur accessibilité',
)]
class TestCarouselImagesCommand extends Command
{
    public function __construct(
        private CarouselImageRepository $carouselImageRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test des images du carrousel');

        // Récupérer toutes les images
        $allImages = $this->carouselImageRepository->findAll();

        if (empty($allImages)) {
            $io->warning('Aucune image trouvée dans la base de données.');
            return Command::SUCCESS;
        }

        $io->section('Vérification des images du carrousel');

        $validImages = 0;
        $invalidImages = 0;

        foreach ($allImages as $image) {
            $imagePath = $image->getImagePath();
            $fullPath = 'public' . $imagePath;

            $io->text(sprintf(
                'Image ID %d: %s (Position %d)',
                $image->getId(),
                $image->getTitle(),
                $image->getPosition()
            ));

            $io->text(sprintf('  Chemin: %s', $imagePath));

            // Vérifier si le fichier existe
            if (file_exists($fullPath)) {
                $fileSize = filesize($fullPath);
                $fileSizeKB = round($fileSize / 1024, 2);

                $io->text(sprintf('  ✅ Fichier trouvé (%s KB)', $fileSizeKB));
                $io->text(sprintf('  Statut: %s', $image->isActive() ? '✅ Actif' : '❌ Inactif'));
                $validImages++;
            } else {
                $io->text('  ❌ Fichier non trouvé !');
                $invalidImages++;
            }

            if ($image->getAltText()) {
                $io->text(sprintf('  Alt: %s', $image->getAltText()));
            } else {
                $io->text('  Alt: (aucun)');
            }

            $io->newLine();
        }

        // Résumé
        $io->section('Résumé');
        $io->success(sprintf('%d images valides trouvées', $validImages));

        if ($invalidImages > 0) {
            $io->warning(sprintf('%d images avec des chemins invalides', $invalidImages));
        }

        // Suggestions d'amélioration
        if ($invalidImages > 0) {
            $io->section('Suggestions');
            $io->text('Pour corriger les images invalides :');
            $io->text('1. Vérifiez que les fichiers existent dans le dossier public/');
            $io->text('2. Corrigez les chemins dans l\'interface d\'administration');
            $io->text('3. Utilisez des chemins relatifs depuis public/ (ex: /images/...)');
        }

        $io->success('Test des images du carrousel terminé !');
        return Command::SUCCESS;
    }
}
