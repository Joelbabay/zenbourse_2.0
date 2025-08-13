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
    name: 'app:test-investisseur-active-menus',
    description: 'Test des classes active pour les menus INVESTISSEUR'
)]
class TestInvestisseurActiveMenusCommand extends Command
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

        $io->title('Test des classes active pour les menus INVESTISSEUR');
        $io->section('Vérification de la logique des menus actifs');

        // Récupérer tous les menus INVESTISSEUR
        $investisseurMenus = $this->menuRepository->findBy(['section' => 'INVESTISSEUR'], ['menuorder' => 'ASC']);

        if (empty($investisseurMenus)) {
            $io->error('Aucun menu INVESTISSEUR trouvé !');
            return Command::FAILURE;
        }

        $io->text(sprintf('📋 %d menus INVESTISSEUR trouvés', count($investisseurMenus)));

        // Tester chaque menu avec différents slugs
        foreach ($investisseurMenus as $menu) {
            $io->section(sprintf('Menu: "%s" (slug: %s)', $menu->getLabel(), $menu->getSlug()));

            // Simuler une requête avec le slug du menu
            $request = new Request();
            $request->attributes->set('_route', 'app_investisseur_page');
            $request->attributes->set('slug', $menu->getSlug());

            $this->requestStack->push($request);

            // Tester si le menu est actif
            $isActive = $this->appExtension->isActiveMenu('app_investisseur_page', $menu);

            $io->text(sprintf(
                '  • Route: %s | Slug: %s | Actif: %s',
                $menu->getRoute(),
                $menu->getSlug(),
                $isActive ? '✅ OUI' : '❌ NON'
            ));

            // Tester les sous-menus si ce menu en a
            if ($menu->getChildren()->count() > 0) {
                $io->text(sprintf('  • Sous-menus (%d):', $menu->getChildren()->count()));

                foreach ($menu->getChildren() as $child) {
                    $childIsActive = $this->appExtension->isActiveChild('app_investisseur_page', $child);
                    $io->text(sprintf(
                        '    - %s (slug: %s) | Actif: %s',
                        $child->getLabel(),
                        $child->getSlug(),
                        $childIsActive ? '✅ OUI' : '❌ NON'
                    ));
                }
            }

            $this->requestStack->pop();
        }

        // Test avec un slug inexistant
        $io->section('Test avec slug inexistant');
        $request = new Request();
        $request->attributes->set('_route', 'app_investisseur_page');
        $request->attributes->set('slug', 'slug-inexistant');

        $this->requestStack->push($request);

        $menuPrincipal = $investisseurMenus[0]; // Premier menu pour le test
        $isActive = $this->appExtension->isActiveMenu('app_investisseur_page', $menuPrincipal);

        $io->text(sprintf(
            'Slug inexistant: %s | Menu actif: %s',
            'slug-inexistant',
            $isActive ? '✅ OUI (ERREUR!)' : '❌ NON (CORRECT)'
        ));

        $this->requestStack->pop();

        $io->success('Test terminé ! Vérifiez que les menus parents sont bien marqués comme actifs.');

        return Command::SUCCESS;
    }
}
