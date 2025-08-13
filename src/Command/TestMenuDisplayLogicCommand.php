<?php

namespace App\Command;

use App\Entity\Menu;
use App\Repository\MenuRepository;
use App\Service\MenuService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCommand(
    name: 'app:test-menu-display-logic',
    description: 'Tester la logique d\'affichage des sous-menus pour comprendre le problème d\'affichage permanent'
)]
class TestMenuDisplayLogicCommand extends Command
{
    public function __construct(
        private MenuRepository $menuRepository,
        private MenuService $menuService,
        private RequestStack $requestStack
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test de la logique d\'affichage des sous-menus');
        $io->section('Analyse du problème d\'affichage permanent');

        // Récupérer tous les menus INVESTISSEUR
        $allMenus = $this->menuRepository->findBy(['section' => 'INVESTISSEUR'], ['menuorder' => 'ASC']);

        if (empty($allMenus)) {
            $io->error('Aucun menu INVESTISSEUR trouvé !');
            return Command::FAILURE;
        }

        // Tester chaque page pour voir quel menu parent est considéré comme actif
        foreach ($allMenus as $currentMenu) {
            $io->section(sprintf(
                '🌐 Test sur: "%s" (/investisseur/%s)',
                $currentMenu->getLabel(),
                $currentMenu->getSlug()
            ));

            // Simuler la requête
            $request = new Request();
            $request->attributes->set('_route', 'app_investisseur_page');
            $request->attributes->set('slug', $currentMenu->getSlug());

            $this->requestStack->push($request);

            // Vérifier quel menu parent est considéré comme actif
            $activeParent = $this->menuService->getActiveParentMenu('app_investisseur_page', 'INVESTISSEUR');

            if ($activeParent) {
                $io->text(sprintf(
                    '📁 Menu parent actif détecté: "%s" (ID: %d)',
                    $activeParent->getLabel(),
                    $activeParent->getId()
                ));

                $children = $activeParent->getChildren();
                if ($children->count() > 0) {
                    $io->text(sprintf('  • Nombre d\'enfants: %d', $children->count()));
                    foreach ($children as $child) {
                        $io->text(sprintf(
                            '    - %s (ID: %d, Slug: %s)',
                            $child->getLabel(),
                            $child->getId(),
                            $child->getSlug()
                        ));
                    }
                }
            } else {
                $io->text('❌ Aucun menu parent actif détecté');
            }

            // Vérifier si le menu actuel est un parent ou un enfant
            if ($currentMenu->getParent() === null) {
                $io->text(sprintf('  • "%s" est un menu parent', $currentMenu->getLabel()));
            } else {
                $io->text(sprintf(
                    '  • "%s" est un sous-menu de "%s"',
                    $currentMenu->getLabel(),
                    $currentMenu->getParent()->getLabel()
                ));
            }

            $this->requestStack->pop();
            $io->text(''); // Ligne vide
        }

        // Test spécial : vérifier la logique sur des pages spécifiques
        $io->section('🔍 Test sur des pages spécifiques');

        $testPages = [
            'la-methode' => 'Page La Méthode (menu parent)',
            'vague-d-elliot' => 'Page Vague d\'elliot (sous-menu de La Méthode)',
            'bibliotheque' => 'Page Bibliothèque (menu parent)',
            'ramassage' => 'Page Ramassage (sous-menu de Bibliothèque)',
            'outils' => 'Page Outils (menu parent sans enfants)'
        ];

        foreach ($testPages as $slug => $description) {
            $io->text(sprintf('📄 Test: %s', $description));

            $request = new Request();
            $request->attributes->set('_route', 'app_investisseur_page');
            $request->attributes->set('slug', $slug);

            $this->requestStack->push($request);

            $activeParent = $this->menuService->getActiveParentMenu('app_investisseur_page', 'INVESTISSEUR');

            if ($activeParent) {
                $io->text(sprintf('  ✅ Menu parent actif: "%s"', $activeParent->getLabel()));

                // Vérifier si ce menu parent a des enfants
                $children = $activeParent->getChildren();
                if ($children->count() > 0) {
                    $io->text(sprintf('  📋 Sous-menus qui s\'afficheront: %d', $children->count()));
                    foreach ($children as $child) {
                        $io->text(sprintf('    - %s', $child->getLabel()));
                    }
                } else {
                    $io->text('  ⚠️  Ce menu parent n\'a pas d\'enfants');
                }
            } else {
                $io->text('  ❌ Aucun menu parent actif');
            }

            $this->requestStack->pop();
            $io->text(''); // Ligne vide
        }

        $io->success('Test terminé !');
        $io->note('Le problème peut venir de la logique dans getActiveParentMenu ou dans le template.');

        return Command::SUCCESS;
    }
}
