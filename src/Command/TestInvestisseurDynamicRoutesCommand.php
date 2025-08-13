<?php

namespace App\Command;

use App\Repository\MenuRepository;
use App\Repository\PageContentRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(
    name: 'app:test-investisseur-dynamic-routes',
    description: 'Teste les routes dynamiques INVESTISSEUR et la génération des URLs'
)]
class TestInvestisseurDynamicRoutesCommand extends Command
{
    public function __construct(
        private MenuRepository $menuRepository,
        private PageContentRepository $pageContentRepository,
        private UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test des routes dynamiques INVESTISSEUR');

        // 1. Vérifier tous les menus INVESTISSEUR
        $investisseurMenus = $this->menuRepository->findBy(['section' => 'INVESTISSEUR'], ['menuorder' => 'ASC']);

        $io->section('📋 Menus INVESTISSEUR configurés');

        $table = [];
        foreach ($investisseurMenus as $menu) {
            $hasContent = $this->pageContentRepository->findOneBy(['menu' => $menu]) ? '✅' : '❌';

            try {
                $url = $this->urlGenerator->generate('app_investisseur_page', ['slug' => $menu->getSlug()]);
                $urlStatus = '✅';
            } catch (\Exception $e) {
                $url = 'ERREUR: ' . $e->getMessage();
                $urlStatus = '❌';
            }

            $table[] = [
                $menu->getLabel(),
                $menu->getSlug(),
                $menu->getRoute(),
                $hasContent,
                $urlStatus,
                $url
            ];
        }

        $io->table(
            ['Menu', 'Slug', 'Route', 'Contenu', 'URL OK', 'URL générée'],
            $table
        );

        // 2. Statistiques
        $totalMenus = count($investisseurMenus);
        $menusWithContent = count(array_filter($table, fn($row) => $row[3] === '✅'));
        $menusWithValidUrl = count(array_filter($table, fn($row) => $row[4] === '✅'));

        $io->section('📊 Statistiques');
        $io->definitionList(
            ['Total menus INVESTISSEUR' => $totalMenus],
            ['Menus avec contenu' => "$menusWithContent/$totalMenus"],
            ['URLs valides' => "$menusWithValidUrl/$totalMenus"],
            ['Route utilisée' => 'app_investisseur_page']
        );

        // 3. Recommandations
        $io->section('💡 Recommandations');

        if ($menusWithContent < $totalMenus) {
            $io->note([
                'Des menus n\'ont pas encore de contenu.',
                'Vous pouvez créer du contenu depuis l\'admin EasyAdmin.',
                'Allez dans "Pages : création, modification, suppression" et créez du contenu pour chaque menu.'
            ]);
        }

        if ($menusWithValidUrl === $totalMenus) {
            $io->success('🎉 Tous les menus INVESTISSEUR ont des URLs valides !');
        } else {
            $io->error('⚠️ Certains menus ont des problèmes d\'URL. Vérifiez les routes.');
        }

        // 4. Instructions pour tester
        $io->section('🧪 Comment tester');
        $io->listing([
            'Connectez-vous avec un compte qui a accès INVESTISSEUR',
            'Naviguez vers http://localhost:8000/investisseur/[slug]',
            'Exemples d\'URLs à tester:',
            '  • http://localhost:8000/investisseur/la-methode',
            '  • http://localhost:8000/investisseur/bibliotheque',
            '  • http://localhost:8000/investisseur/outils',
            'Vérifiez que les menus actifs s\'affichent correctement',
            'Testez la création de contenu depuis l\'admin'
        ]);

        $io->info('Le système de routes dynamiques INVESTISSEUR est maintenant opérationnel !');

        return Command::SUCCESS;
    }
}
