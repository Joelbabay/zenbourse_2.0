<?php

namespace App\Command;

use App\Entity\Menu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\RouterInterface;

#[AsCommand(
    name: 'app:validate-menu-routes',
    description: 'Valide que toutes les routes des menus sont correctes',
)]
class ValidateMenuRoutesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RouterInterface $router
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Validation des routes de menu');

        $menus = $this->entityManager->getRepository(Menu::class)->findAll();
        $validRoutes = 0;
        $invalidRoutes = 0;
        $errors = [];

        foreach ($menus as $menu) {
            $route = $menu->getRoute();
            $slug = $menu->getSlug();

            try {
                // Routes qui nécessitent le paramètre slug
                $routesWithSlug = ['app_home_page', 'home'];

                // Routes qui nécessitent le paramètre category
                $routesWithCategory = ['investisseur_bibliotheque_category'];

                if (in_array($route, $routesWithSlug)) {
                    $this->router->generate($route, ['slug' => $slug]);
                } elseif (in_array($route, $routesWithCategory)) {
                    $this->router->generate($route, ['category' => $slug]);
                } else {
                    $this->router->generate($route);
                }

                $validRoutes++;
                $io->text(sprintf('✓ Menu "%s" : route "%s" valide', $menu->getLabel(), $route));
            } catch (\Exception $e) {
                $invalidRoutes++;
                $error = sprintf('✗ Menu "%s" : route "%s" invalide - %s', $menu->getLabel(), $route, $e->getMessage());
                $errors[] = $error;
                $io->text($error);
            }
        }

        $io->section('Résumé');
        $io->text(sprintf('Routes valides : %d', $validRoutes));
        $io->text(sprintf('Routes invalides : %d', $invalidRoutes));

        if ($invalidRoutes === 0) {
            $io->success('Toutes les routes des menus sont valides !');
        } else {
            $io->error(sprintf('%d routes invalides trouvées.', $invalidRoutes));
        }

        return $invalidRoutes === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
