<?php

namespace App\Command;

use App\Repository\MenuRepository;
use App\Service\MenuService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-contact-menu',
    description: 'Test de la logique de détection active du menu Contact'
)]
class TestContactMenuCommand extends Command
{
    public function __construct(
        private MenuRepository $menuRepository,
        private MenuService $menuService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test de la logique de détection active du menu Contact');

        // Récupérer le menu Contact
        $contactMenu = $this->menuRepository->findOneBy(['label' => 'Contact', 'section' => 'HOME']);

        if (!$contactMenu) {
            $io->error('Menu Contact non trouvé');
            return Command::FAILURE;
        }

        $io->section('Informations du menu Contact');
        $io->table(
            ['Propriété', 'Valeur'],
            [
                ['ID', $contactMenu->getId()],
                ['Label', $contactMenu->getLabel()],
                ['Route', $contactMenu->getRoute()],
                ['Slug', $contactMenu->getSlug()],
                ['Section', $contactMenu->getSection()],
            ]
        );

        // Test de la logique de détection active
        $io->section('Test de la logique de détection active');

        $testRoutes = [
            'app_home_contact' => 'Route du contrôleur Contact',
            'app_home_page' => 'Route générique avec slug',
            'home_contact' => 'Ancienne route',
            'other_route' => 'Route inexistante'
        ];

        foreach ($testRoutes as $route => $description) {
            $isActive = $this->menuService->isMenuActive($route, $contactMenu);
            $status = $isActive ? '✅ ACTIF' : '❌ INACTIF';
            $io->text("{$description} ({$route}): {$status}");
        }

        // Test avec la route actuelle du contrôleur
        $io->section('Test avec la route du contrôleur ContactController');
        $currentRoute = 'app_home_contact';
        $isActive = $this->menuService->isMenuActive($currentRoute, $contactMenu);

        if ($isActive) {
            $io->success('✅ Le menu Contact sera correctement détecté comme actif sur la page contact');
        } else {
            $io->error('❌ Le menu Contact ne sera pas détecté comme actif sur la page contact');
        }

        return Command::SUCCESS;
    }
}
