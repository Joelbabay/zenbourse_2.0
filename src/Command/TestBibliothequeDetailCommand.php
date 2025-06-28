<?php

namespace App\Command;

use App\Service\MenuService;
use App\Twig\Extension\AppExtension;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCommand(
    name: 'app:test-bibliotheque-detail',
    description: 'Test de la route de détails de la bibliothèque',
)]
class TestBibliothequeDetailCommand extends Command
{
    public function __construct(
        private MenuService $menuService,
        private AppExtension $appExtension
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("=== Test route bibliotheque detail ===\n");

        // Test avec la route de détails
        $route = 'investisseur_bibliotheque_detail';

        // Créer une requête simulée avec les paramètres
        $request = new Request();
        $request->attributes->set('_route', $route);
        $request->attributes->set('category', 'bulles-type-2');
        $request->attributes->set('slug', 'example-slug');

        // Créer un RequestStack temporaire
        $requestStack = new RequestStack();
        $requestStack->push($request);

        // Utiliser la réflexion pour injecter le RequestStack dans l'extension
        $reflection = new \ReflectionClass($this->appExtension);
        $property = $reflection->getProperty('requestStack');
        $property->setAccessible(true);
        $property->setValue($this->appExtension, $requestStack);

        // Tester getActiveParentMenu
        $activeParent = $this->menuService->getActiveParentMenu($route, 'INVESTISSEUR');
        $output->writeln("Route: {$route}");
        $output->writeln("Parent actif: " . ($activeParent ? $activeParent->getLabel() : 'AUCUN'));

        if ($activeParent) {
            $output->writeln("Nombre d'enfants: " . $activeParent->getChildren()->count());
            $output->writeln("Enfants:");
            foreach ($activeParent->getChildren() as $child) {
                $isActive = $this->appExtension->isActiveChild($route, $child);
                $output->writeln("  - {$child->getLabel()} (slug: {$child->getSlug()}, actif: " . ($isActive ? 'OUI' : 'NON') . ")");
            }
        }

        return Command::SUCCESS;
    }
}
