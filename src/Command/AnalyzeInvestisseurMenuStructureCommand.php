<?php

namespace App\Command;

use App\Entity\Menu;
use App\Repository\MenuRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:analyze-investisseur-menu-structure',
    description: 'Analyser la structure complète des menus INVESTISSEUR dans la base de données'
)]
class AnalyzeInvestisseurMenuStructureCommand extends Command
{
    public function __construct(
        private MenuRepository $menuRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Analyse de la structure des menus INVESTISSEUR');
        $io->section('Structure de la base de données');

        // Récupérer tous les menus INVESTISSEUR
        $allMenus = $this->menuRepository->findBy(['section' => 'INVESTISSEUR'], ['menuorder' => 'ASC']);

        if (empty($allMenus)) {
            $io->error('Aucun menu INVESTISSEUR trouvé !');
            return Command::FAILURE;
        }

        $io->text(sprintf('📋 Total: %d menus INVESTISSEUR trouvés', count($allMenus)));

        // Séparer les menus parents et enfants
        $parentMenus = [];
        $childMenus = [];

        foreach ($allMenus as $menu) {
            if ($menu->getParent() === null) {
                $parentMenus[] = $menu;
            } else {
                $childMenus[] = $menu;
            }
        }

        $io->section('📊 Répartition des menus');
        $io->text(sprintf('  • Menus parents: %d', count($parentMenus)));
        $io->text(sprintf('  • Sous-menus: %d', count($childMenus)));

        // Afficher la structure hiérarchique
        $io->section('🌳 Structure hiérarchique des menus');

        foreach ($parentMenus as $parent) {
            $io->text(sprintf(
                '📁 %s (ID: %d, Slug: %s, Route: %s)',
                $parent->getLabel(),
                $parent->getId(),
                $parent->getSlug(),
                $parent->getRoute()
            ));

            $children = $parent->getChildren();
            if ($children->count() > 0) {
                foreach ($children as $child) {
                    $io->text(sprintf(
                        '  └── 📄 %s (ID: %d, Slug: %s, Route: %s)',
                        $child->getLabel(),
                        $child->getId(),
                        $child->getSlug(),
                        $child->getRoute()
                    ));
                }
            } else {
                $io->text('  └── (Aucun sous-menu)');
            }
        }

        // Afficher les sous-menus orphelins
        $orphanMenus = [];
        foreach ($childMenus as $child) {
            if ($child->getParent() === null) {
                $orphanMenus[] = $child;
            }
        }

        if (!empty($orphanMenus)) {
            $io->section('⚠️  Sous-menus orphelins (sans parent)');
            foreach ($orphanMenus as $orphan) {
                $io->text(sprintf(
                    '  • %s (ID: %d, Slug: %s)',
                    $orphan->getLabel(),
                    $orphan->getId(),
                    $orphan->getSlug()
                ));
            }
        }

        // Afficher les détails de chaque menu
        $io->section('🔍 Détails de chaque menu');

        foreach ($allMenus as $menu) {
            $io->text(sprintf('Menu: "%s"', $menu->getLabel()));
            $io->text(sprintf('  • ID: %d', $menu->getId()));
            $io->text(sprintf('  • Slug: %s', $menu->getSlug()));
            $io->text(sprintf('  • Route: %s', $menu->getRoute()));
            $io->text(sprintf('  • Section: %s', $menu->getSection()));
            $io->text(sprintf('  • Position: %s', $menu->getMenuorder() ?? 'Non définie'));

            if ($menu->getParent()) {
                $io->text(sprintf(
                    '  • Parent: %s (ID: %d)',
                    $menu->getParent()->getLabel(),
                    $menu->getParent()->getId()
                ));
            } else {
                $io->text('  • Parent: Aucun (menu principal)');
            }

            $childrenCount = $menu->getChildren()->count();
            $io->text(sprintf('  • Enfants: %d', $childrenCount));

            if ($childrenCount > 0) {
                foreach ($menu->getChildren() as $child) {
                    $io->text(sprintf('    - %s (ID: %d)', $child->getLabel(), $child->getId()));
                }
            }

            $io->text(''); // Ligne vide pour séparer
        }

        // Vérifier la cohérence des routes
        $io->section('🔗 Vérification des routes');
        $routesWithSlug = [];
        $routesWithoutSlug = [];

        foreach ($allMenus as $menu) {
            if (str_contains($menu->getRoute(), '{slug}') || $menu->getRoute() === 'app_investisseur_page') {
                $routesWithSlug[] = $menu;
            } else {
                $routesWithoutSlug[] = $menu;
            }
        }

        $io->text(sprintf('  • Routes avec slug: %d', count($routesWithSlug)));
        $io->text(sprintf('  • Routes sans slug: %d', count($routesWithoutSlug)));

        if (!empty($routesWithoutSlug)) {
            $io->text('  • Routes à vérifier:');
            foreach ($routesWithoutSlug as $menu) {
                $io->text(sprintf('    - %s: %s', $menu->getLabel(), $menu->getRoute()));
            }
        }

        $io->success('Analyse terminée ! Vérifiez la structure de vos menus.');

        return Command::SUCCESS;
    }
}
