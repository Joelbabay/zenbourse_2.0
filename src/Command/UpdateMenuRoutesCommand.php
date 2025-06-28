<?php

namespace App\Command;

use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-menu-routes',
    description: 'Met à jour automatiquement les routes des menus selon la nouvelle logique',
)]
class UpdateMenuRoutesCommand extends Command
{
    private $menuRepository;
    private $entityManager;

    public function __construct(
        MenuRepository $menuRepository,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->menuRepository = $menuRepository;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Mise à jour intelligente des routes des menus');

        $menus = $this->menuRepository->findAll();
        if (empty($menus)) {
            $io->warning('Aucun menu trouvé dans la base de données.');
            return Command::SUCCESS;
        }

        $updatedCount = 0;
        $skippedCount = 0;
        $orphans = [];

        foreach ($menus as $menu) {
            $section = strtoupper($menu->getSection());
            $slug = $menu->getSlug();
            $label = $menu->getLabel();
            $oldRoute = $menu->getRoute();
            $newRoute = $oldRoute;

            // HOME : route dynamique avec slug
            if ($section === 'HOME') {
                $newRoute = 'app_home_page';
            }
            // INVESTISSEUR BIBLIOTHEQUE : route dynamique avec category
            elseif ($section === 'INVESTISSEUR' && str_starts_with($oldRoute, 'investisseur_bibliotheque_')) {
                $newRoute = 'investisseur_bibliotheque_category';
            }
            // INTRADAY : corriger les routes avec -1
            elseif ($section === 'INTRADAY' && preg_match('/^intraday_(.+)-1$/', $oldRoute, $matches)) {
                $newRoute = 'intraday_' . $matches[1];
            }
            // INTRADAY : corriger la route la-methode
            elseif ($oldRoute === 'intraday_la-methode') {
                $newRoute = 'intraday_methode';
            }
            // Méthodes investisseur (mapping manuel)
            elseif ($oldRoute === 'investisseur_la-methode_vague-d-elliot') {
                $newRoute = 'investisseur_methode_vagues_elliot';
            } elseif ($oldRoute === 'investisseur_la-methode_cycles-boursiers') {
                $newRoute = 'investisseur_methode_cycles_boursiers';
            } elseif ($oldRoute === 'investisseur_la-methode_la-bulle') {
                $newRoute = 'investisseur_methode_boites_bulles';
            } elseif ($oldRoute === 'investisseur_la-methode_indicateurs') {
                $newRoute = 'investisseur_methode_indicateurs';
            }
            // Orphelins : routes qui ne correspondent à rien
            elseif (!preg_match('/^(app_home_page|investisseur_|intraday_|admin_|file_|home_|app_|reset_)/', $oldRoute)) {
                $orphans[] = [$label, $oldRoute];
                continue;
            }

            if ($oldRoute !== $newRoute) {
                $menu->setRoute($newRoute);
                $updatedCount++;
                $io->text(sprintf('✓ Route corrigée pour "%s" : "%s" → "%s"', $label, $oldRoute, $newRoute));
            } else {
                $skippedCount++;
            }
        }
        $this->entityManager->flush();

        $io->success([
            sprintf('%d routes corrigées', $updatedCount),
            sprintf('%d menus déjà corrects', $skippedCount),
            sprintf('%d menus orphelins', count($orphans)),
        ]);
        if ($orphans) {
            $io->warning('Menus orphelins (à corriger ou supprimer) :');
            foreach ($orphans as [$label, $route]) {
                $io->text(sprintf('- %s : %s', $label, $route));
            }
        }
        return Command::SUCCESS;
    }

    private function generateRoute($menu): string
    {
        $baseRoute = strtolower($menu->getSection());
        $slug = $menu->getSlug();

        // Si c'est un menu enfant, on ajoute le slug du parent
        if ($menu->getParent()) {
            $parentSlug = $menu->getParent()->getSlug();
            return $baseRoute . '_' . $parentSlug . '_' . $slug;
        }

        return $baseRoute . '_' . $slug;
    }
}
