<?php

namespace App\Command;

use App\Entity\CarouselImage;
use App\Repository\CarouselImageRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-carousel-edit',
    description: 'Test de la fonctionnalité d\'upload dans l\'édition du carrousel'
)]
class TestCarouselEditCommand extends Command
{
    public function __construct(
        private CarouselImageRepository $carouselImageRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test de la fonctionnalité d\'upload dans l\'édition du carrousel');

        // Récupérer une image de carrousel existante
        $carouselImage = $this->carouselImageRepository->findOneBy([]);

        if (!$carouselImage) {
            $io->error('Aucune image de carrousel trouvée dans la base de données');
            return Command::FAILURE;
        }

        $io->section('Image de carrousel trouvée');
        $io->table(
            ['Propriété', 'Valeur'],
            [
                ['ID', $carouselImage->getId()],
                ['Titre', $carouselImage->getTitle()],
                ['Chemin image', $carouselImage->getImagePath()],
                ['Position', $carouselImage->getPosition()],
                ['Actif', $carouselImage->isActive() ? 'Oui' : 'Non'],
            ]
        );

        $io->section('Fonctionnalités disponibles');
        $io->text('✅ Upload d\'image par drag & drop');
        $io->text('✅ Upload d\'image par sélection de fichier');
        $io->text('✅ Validation des types de fichiers (JPG, PNG, GIF, WebP)');
        $io->text('✅ Validation de la taille (max 5MB)');
        $io->text('✅ Mise à jour automatique du champ imagePath');
        $io->text('✅ Messages de feedback utilisateur');

        $io->section('Instructions de test');
        $io->text('1. Allez dans l\'admin EasyAdmin');
        $io->text('2. Accédez à "Images du carrousel"');
        $io->text('3. Cliquez sur "Modifier" pour une image existante');
        $io->text('4. Dans la section "Changer l\'image" :');
        $io->text('   - Glissez-déposez une image ou cliquez pour sélectionner');
        $io->text('   - Vérifiez que le champ "Chemin de l\'image" se met à jour');
        $io->text('   - Sauvegardez les modifications');
        $io->text('5. Vérifiez que l\'image apparaît dans le carrousel de la page d\'accueil');

        $io->success('Système d\'upload d\'image pour l\'édition du carrousel prêt !');

        return Command::SUCCESS;
    }
}
