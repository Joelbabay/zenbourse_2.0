<?php

namespace App\Command;

use App\Entity\Menu;
use App\Repository\MenuRepository;
use App\Twig\Extension\AppExtension;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCommand(
    name: 'app:test-investisseur-navigation-real',
    description: 'Test de navigation réelle pour vérifier les classes active des menus INVESTISSEUR'
)]
class TestInvestisseurNavigationRealCommand extends Command
{
    public function __construct(
        private MenuRepository $menuRepository,
        private AppExtension $appExtension,
        private RequestStack $requestStack
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test de navigation réelle - Classes active des menus INVESTISSEUR');
        $io->section('Simulation de la navigation utilisateur');

        // Récupérer tous les menus INVESTISSEUR
        $allMenus = $this->menuRepository->findBy(['section' => 'INVESTISSEUR'], ['menuorder' => 'ASC']);

        if (empty($allMenus)) {
            $io->error('Aucun menu INVESTISSEUR trouvé !');
            return Command::FAILURE;
        }

        // Simuler la navigation sur chaque page
        foreach ($allMenus as $currentMenu) {
            $io->section(sprintf(
                '🌐 Navigation vers: "%s" (/investisseur/%s)',
                $currentMenu->getLabel(),
                $currentMenu->getSlug()
            ));

            // Simuler la requête
            $request = new Request();
            $request->attributes->set('_route', 'app_investisseur_page');
            $request->attributes->set('slug', $currentMenu->getSlug());

            $this->requestStack->push($request);

            // Vérifier quels menus sont actifs
            $io->text('📋 Menus actifs détectés:');

            foreach ($allMenus as $menu) {
                $isActive = $this->appExtension->isActiveMenu('app_investisseur_page', $menu);

                if ($isActive) {
                    $status = '✅ ACTIF';
                    $reason = '';

                    if ($menu->getId() === $currentMenu->getId()) {
                        $reason = ' (page actuelle)';
                    } elseif ($menu->getParent() && $menu->getParent()->getId() === $currentMenu->getId()) {
                        $reason = ' (sous-menu de la page actuelle)';
                    } elseif ($currentMenu->getParent() && $currentMenu->getParent()->getId() === $menu->getId()) {
                        $reason = ' (parent de la page actuelle)';
                    } else {
                        $reason = ' (autre raison)';
                    }

                    $io->text(sprintf('  • %s%s', $menu->getLabel(), $reason));
                }
            }

            // Vérifier les sous-menus actifs
            $activeChildren = [];
            foreach ($allMenus as $menu) {
                if ($menu->getParent()) {
                    $isChildActive = $this->appExtension->isActiveChild('app_investisseur_page', $menu);
                    if ($isChildActive) {
                        $activeChildren[] = $menu;
                    }
                }
            }

            if (!empty($activeChildren)) {
                $io->text('📄 Sous-menus actifs:');
                foreach ($activeChildren as $child) {
                    $io->text(sprintf(
                        '  • %s (enfant de %s)',
                        $child->getLabel(),
                        $child->getParent()->getLabel()
                    ));
                }
            }

            $this->requestStack->pop();
            $io->text(''); // Ligne vide pour séparer
        }

        // Test spécial : vérifier la cohérence des menus parents
        $io->section('🔍 Test de cohérence des menus parents');

        $parentMenus = array_filter($allMenus, fn($m) => $m->getParent() === null);

        foreach ($parentMenus as $parent) {
            $io->text(sprintf('Menu parent: "%s"', $parent->getLabel()));

            $children = $parent->getChildren();
            if ($children->count() > 0) {
                $io->text(sprintf('  • Nombre d\'enfants: %d', $children->count()));

                // Tester si le parent devient actif quand on visite un enfant
                foreach ($children as $child) {
                    $request = new Request();
                    $request->attributes->set('_route', 'app_investisseur_page');
                    $request->attributes->set('slug', $child->getSlug());

                    $this->requestStack->push($request);

                    $parentIsActive = $this->appExtension->isActiveMenu('app_investisseur_page', $parent);
                    $childIsActive = $this->appExtension->isActiveChild('app_investisseur_page', $child);

                    $io->text(sprintf(
                        '    - Visite "%s": Parent actif: %s | Enfant actif: %s',
                        $child->getLabel(),
                        $parentIsActive ? '✅ OUI' : '❌ NON',
                        $childIsActive ? '✅ OUI' : '❌ NON'
                    ));

                    $this->requestStack->pop();
                }
            } else {
                $io->text('  • Aucun enfant');
            }
            $io->text(''); // Ligne vide
        }

        $io->success('Test de navigation terminé !');
        $io->note('Vérifiez que les menus parents sont actifs quand on visite leurs sous-menus.');

        return Command::SUCCESS;
    }
}
