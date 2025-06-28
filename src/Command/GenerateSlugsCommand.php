<?php

namespace App\Command;

use App\Repository\MenuRepository;
use App\Service\MenuService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-slugs',
    description: 'Génère automatiquement les slugs pour tous les menus existants',
)]
class GenerateSlugsCommand extends Command
{
    private $menuRepository;
    private $menuService;
    private $entityManager;

    public function __construct(
        MenuRepository $menuRepository,
        MenuService $menuService,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->menuRepository = $menuRepository;
        $this->menuService = $menuService;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Génération des slugs pour les menus');

        // Récupère tous les menus
        $menus = $this->menuRepository->findAll();

        if (empty($menus)) {
            $io->warning('Aucun menu trouvé dans la base de données.');
            return Command::SUCCESS;
        }

        $io->text(sprintf('Traitement de %d menus...', count($menus)));

        $updatedCount = 0;
        $skippedCount = 0;
        $usedSlugs = [];

        foreach ($menus as $menu) {
            $label = $menu->getLabel();
            $currentSlug = $menu->getSlug();

            // Régénère le slug s'il est vide ou s'il commence par "temp-"
            if (empty($currentSlug) || str_starts_with($currentSlug, 'temp-')) {
                // Génère un slug unique en tenant compte des slugs déjà générés dans cette exécution
                $baseSlug = $this->menuRepository->generateUniqueSlug($label, $menu->getId());
                $slug = $baseSlug;
                $counter = 1;
                while (in_array($slug, $usedSlugs)) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                $menu->setSlug($slug);
                $usedSlugs[] = $slug;
                $io->text(sprintf('✓ Généré slug "%s" pour "%s" (remplace "%s")', $slug, $label, $currentSlug));
                $updatedCount++;
            } else {
                $usedSlugs[] = $currentSlug;
                $io->text(sprintf('- Slug déjà présent "%s" pour "%s"', $currentSlug, $label));
                $skippedCount++;
            }
        }

        // Persiste les changements
        $this->entityManager->flush();

        $io->success([
            sprintf('%d slugs générés', $updatedCount),
            sprintf('%d menus déjà avec slug', $skippedCount),
            'Tous les slugs ont été sauvegardés en base de données.'
        ]);

        return Command::SUCCESS;
    }
}
