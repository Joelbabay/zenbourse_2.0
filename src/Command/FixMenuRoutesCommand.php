<?php

namespace App\Command;

use App\Entity\Menu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fix-menu-routes',
    description: 'Corrige automatiquement les routes incorrectes dans la table menu',
)]
class FixMenuRoutesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Correction des routes de menu');

        // Mapping des routes incorrectes vers les routes correctes
        $routeMapping = [
            // Routes home_* vers app_home_page (routes génériques)
            'home_perdant' => 'app_home_page',
            'home_methodes' => 'app_home_page',
            'home_citation' => 'app_home_page',
            'home_bien_debuter' => 'app_home_page',
            'home_performance' => 'app_home_page',
            'home_test' => 'app_home_page',
            
            // Routes spécifiques
            'contact' => 'app_home_contact',
            'investisseur_methode' => 'investisseur_la-methode',
            'investisseur_bibliotheque' => 'investisseur_bibliotheque',
            'investisseur_outils' => 'investisseur_outils',
            'investisseur_gestion' => 'investisseur_gestion',
            'investisseur_introduction' => 'investisseur_introduction',
            
            // Routes intraday
            'intraday_methode' => 'intraday_methode',
            'intraday_bibliotheque' => 'intraday_bibliotheque',
            'intraday_outils' => 'intraday_outils',
            'intraday_gestion' => 'intraday_gestion',
            
            // Routes de sous-sections investisseur avec underscores vers tirets
            'investisseur_bibliotheque_bulles_type_1' => 'investisseur_bibliotheque_bulles-type-1',
            'investisseur_bibliotheque_bulles_type_2' => 'investisseur_bibliotheque_bulles-type-2',
            'investisseur_bibliotheque_ramasssage_1' => 'investisseur_bibliotheque_ramassage',
            'investisseur_bibliotheque_ramasssage_pic' => 'investisseur_bibliotheque_ramassage-pic',
            'investisseur_bibliotheque_pic_ramassage' => 'investisseur_bibliotheque_pic-ramassage',
            'investisseur_bibliotheque_pics_volumes' => 'investisseur_bibliotheque_pics-de-volumes',
            'investisseur_bibliotheque_volumes_faibles' => 'investisseur_bibliotheque_volumes-faibles',
            'investisseur_bibliotheque_introduction' => 'investisseur_bibliotheque_introductions',
            
            // Routes de sous-sections méthode
            'investisseur_la-methode_vague-d-elliot' => 'investisseur_la-methode_vague-d-elliot',
            'investisseur_la-methode_cycles-boursiers' => 'investisseur_la-methode_cycles-boursiers',
            'investisseur_la-methode_la-bulle' => 'investisseur_la-methode_la-bulle',
            'investisseur_la-methode_indicateurs' => 'investisseur_la-methode_indicateurs',
            'investisseur_methode_chandeliers_japonais' => 'investisseur_methode_chandeliers_japonais',
        ];

        $menus = $this->entityManager->getRepository(Menu::class)->findAll();
        $updatedCount = 0;

        foreach ($menus as $menu) {
            $currentRoute = $menu->getRoute();
            
            if (isset($routeMapping[$currentRoute])) {
                $newRoute = $routeMapping[$currentRoute];
                $oldRoute = $currentRoute;
                $menu->setRoute($newRoute);
                $updatedCount++;
                
                $io->text(sprintf('Menu "%s" : %s → %s', $menu->getLabel(), $oldRoute, $newRoute));
            }
        }

        if ($updatedCount > 0) {
            $this->entityManager->flush();
            $io->success(sprintf('%d menus ont été mis à jour avec les bonnes routes.', $updatedCount));
        } else {
            $io->info('Aucun menu nécessitait de correction de route.');
        }

        // Afficher un résumé des routes utilisées
        $io->section('Résumé des routes utilisées :');
        $routeSummary = [];
        foreach ($menus as $menu) {
            $route = $menu->getRoute();
            if (!isset($routeSummary[$route])) {
                $routeSummary[$route] = [];
            }
            $routeSummary[$route][] = $menu->getLabel();
        }
        
        foreach ($routeSummary as $route => $labels) {
            $io->text(sprintf('%s : %s', $route, implode(', ', $labels)));
        }

        return Command::SUCCESS;
    }
} 