<?php

namespace App\Command;

use App\Service\MenuService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-menu',
    description: 'Test du système de menu',
)]
class TestMenuCommand extends Command
{
    public function __construct(
        private MenuService $menuService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test du système de menu');

        // Test 1: Récupérer le menu parent actif pour la route de détail
        $io->section('Test 1: Menu parent actif pour investisseur_methode_chandeliers_japonais_detail');
        
        $activeParent = $this->menuService->getActiveParentMenu('investisseur_methode_chandeliers_japonais_detail', 'INVESTISSEUR');
        
        if ($activeParent) {
            $io->success('Menu parent trouvé: ' . $activeParent->getLabel());
            $io->text('Route: ' . $activeParent->getRoute());
            $io->text('Enfants: ' . count($activeParent->getChildren()));
            
            foreach ($activeParent->getChildren() as $child) {
                $io->text('- ' . $child->getLabel() . ' (' . $child->getRoute() . ')');
            }
        } else {
            $io->error('Aucun menu parent trouvé');
        }

        // Test 2: Récupérer tous les menus de la section INVESTISSEUR
        $io->section('Test 2: Tous les menus INVESTISSEUR');
        
        $menus = $this->menuService->getMenuBySection('INVESTISSEUR');
        
        foreach ($menus as $menu) {
            $io->text($menu->getLabel() . ' (' . $menu->getRoute() . ') - Enfants: ' . count($menu->getChildren()));
        }

        // Test 3: Chercher spécifiquement le menu "La Méthode"
        $io->section('Test 3: Menu "La Méthode"');
        
        $methodeMenu = null;
        foreach ($menus as $menu) {
            if ($menu->getLabel() === 'La Méthode') {
                $methodeMenu = $menu;
                break;
            }
        }
        
        if ($methodeMenu) {
            $io->success('Menu "La Méthode" trouvé');
            $io->text('Route: ' . $methodeMenu->getRoute());
            $io->text('Enfants: ' . count($methodeMenu->getChildren()));
            
            foreach ($methodeMenu->getChildren() as $child) {
                $io->text('- ' . $child->getLabel() . ' (' . $child->getRoute() . ')');
            }
        } else {
            $io->error('Menu "La Méthode" non trouvé');
        }

        // Test 4: Simuler la logique Twig pour voir ce qui se passe
        $io->section('Test 4: Simulation de la logique Twig');
        
        $currentRoute = 'investisseur_methode_chandeliers_japonais_detail';
        $section = 'INVESTISSEUR';
        
        // Simuler get_active_parent_menu
        $activeParent = $this->menuService->getActiveParentMenu($currentRoute, $section);
        
        if ($activeParent) {
            $io->text('Menu parent actif: ' . $activeParent->getLabel());
            
            // Simuler la boucle des enfants
            foreach ($activeParent->getChildren() as $child) {
                // Simuler is_active_child
                $isActive = false;
                
                // Logique de is_active_child
                if ($currentRoute === $child->getRoute()) {
                    $isActive = true;
                } elseif (
                    $child->getRoute() === 'investisseur_methode_chandeliers_japonais'
                    && $currentRoute === 'investisseur_methode_chandeliers_japonais_detail'
                ) {
                    $isActive = true;
                }
                
                $status = $isActive ? 'ACTIF' : 'inactif';
                $io->text('- ' . $child->getLabel() . ' (' . $child->getRoute() . ') : ' . $status);
            }
        }

        return Command::SUCCESS;
    }
} 