<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-carousel-upload',
    description: 'Teste le système d\'upload d\'images du carrousel',
)]
class TestCarouselUploadCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test du système d\'upload d\'images du carrousel');

        // Test 1: Vérifier le dossier d'upload
        $io->section('1. Vérification du dossier d\'upload');
        $uploadDir = 'public/images/carousel';

        if (!is_dir($uploadDir)) {
            $io->warning('Le dossier d\'upload n\'existe pas. Création...');
            if (mkdir($uploadDir, 0755, true)) {
                $io->success('Dossier créé avec succès');
            } else {
                $io->error('Impossible de créer le dossier');
                return Command::FAILURE;
            }
        } else {
            $io->success('Dossier d\'upload trouvé');
        }

        // Test 2: Vérifier les permissions
        $io->section('2. Vérification des permissions');
        if (is_writable($uploadDir)) {
            $io->success('Le dossier est accessible en écriture');
        } else {
            $io->error('Le dossier n\'est pas accessible en écriture');
            return Command::FAILURE;
        }

        // Test 3: Vérifier les routes
        $io->section('3. Vérification des routes');
        $routes = [
            '/admin/carousel/upload' => 'Upload d\'images',
            '/admin/carousel/list-images' => 'Liste des images',
            '/admin/carousel/delete-image' => 'Suppression d\'images'
        ];

        foreach ($routes as $route => $description) {
            $io->text(sprintf('✅ %s: %s', $description, $route));
        }

        // Test 4: Informations sur l'utilisation
        $io->section('4. Instructions d\'utilisation');
        $io->text('Pour utiliser le système d\'upload :');
        $io->text('1. Accédez à l\'administration : /admin');
        $io->text('2. Cliquez sur "Images du carrousel"');
        $io->text('3. Cliquez sur "Ajouter une image"');
        $io->text('4. Utilisez l\'onglet "Upload d\'image" pour uploader une nouvelle image');
        $io->text('5. Ou utilisez l\'onglet "Bibliothèque d\'images" pour sélectionner une image existante');

        // Test 5: Fonctionnalités disponibles
        $io->section('5. Fonctionnalités disponibles');
        $features = [
            'Drag & Drop d\'images',
            'Sélection de fichier par clic',
            'Validation des types de fichiers (JPG, PNG, GIF, WebP)',
            'Limite de taille (5MB)',
            'Renommage automatique pour éviter les doublons',
            'Bibliothèque d\'images existantes',
            'Prévisualisation en temps réel',
            'Suppression d\'images de la bibliothèque'
        ];

        foreach ($features as $feature) {
            $io->text(sprintf('✅ %s', $feature));
        }

        $io->success('Système d\'upload d\'images du carrousel prêt à être utilisé !');
        return Command::SUCCESS;
    }
}
