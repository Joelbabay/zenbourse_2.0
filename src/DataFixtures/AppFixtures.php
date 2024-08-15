<?php

namespace App\DataFixtures;

use App\Entity\Menu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $homeMenu1 = new Menu();
        $homeMenu1->setLabel('Accueil')
            ->setRoute('home')
            ->setSection('HOME')
            ->setMenuorder(1);
        $manager->persist($homeMenu1);

        $homeMenu2 = new Menu();
        $homeMenu2->setLabel('Méthodes')
            ->setRoute('home_methodes')
            ->setSection('HOME')
            ->setMenuorder(2);
        $manager->persist($homeMenu2);

        $homeMenu3 = new Menu();
        $homeMenu3->setLabel('Le Perdant')
            ->setRoute('home_perdant')
            ->setSection('HOME')
            ->setMenuorder(3);
        $manager->persist($homeMenu3);

        $homeMenu4 = new Menu();
        $homeMenu4->setLabel('Citation')
            ->setRoute('home_citation')
            ->setSection('HOME')
            ->setMenuorder(4);
        $manager->persist($homeMenu4);

        $homeMenu5 = new Menu();
        $homeMenu5->setLabel('Bien Débuter')
            ->setRoute('home_bien_debuter')
            ->setSection('HOME')
            ->setMenuorder(5);
        $manager->persist($homeMenu5);

        $homeMenu6 = new Menu();
        $homeMenu6->setLabel('Performance')
            ->setRoute('home_performance')
            ->setSection('HOME')
            ->setMenuorder(6);
        $manager->persist($homeMenu6);

        $homeMenu7 = new Menu();
        $homeMenu7->setLabel('Contact')
            ->setRoute('home_contact')
            ->setSection('HOME')
            ->setMenuorder(7);
        $manager->persist($homeMenu7);

        // Menus pour la section INVESTISSEUR
        $investisseurMenu1 = new Menu();
        $investisseurMenu1->setLabel('Présentation')
            ->setRoute('investisseur_presentation')
            ->setSection('INVESTISSEUR')
            ->setMenuorder(1);
        $manager->persist($investisseurMenu1);

        $investisseurMenu2 = new Menu();
        $investisseurMenu2->setLabel('La Méthode')
            ->setRoute('investisseur_methode')
            ->setSection('INVESTISSEUR')
            ->setMenuorder(2);
        $manager->persist($investisseurMenu2);

        // Sous-menus pour "La Méthode"
        $picMenu = new Menu();
        $picMenu->setLabel('Pic')
            ->setRoute('investisseur_methode_pic')
            ->setSection('INVESTISSEUR')
            ->setParent($investisseurMenu2)
            ->setMenuorder(1);
        $manager->persist($picMenu);

        $ramassageMenu = new Menu();
        $ramassageMenu->setLabel('Ramassage')
            ->setRoute('investisseur_methode_ramassage')
            ->setSection('INVESTISSEUR')
            ->setParent($investisseurMenu2)
            ->setMenuorder(2);
        $manager->persist($ramassageMenu);

        $introMenu = new Menu();
        $introMenu->setLabel('Intro')
            ->setRoute('investisseur_methode_intro')
            ->setSection('INVESTISSEUR')
            ->setParent($investisseurMenu2)
            ->setMenuorder(3);
        $manager->persist($introMenu);

        $investisseurMenu3 = new Menu();
        $investisseurMenu3->setLabel('Bibliothèque')
            ->setRoute('investisseur_bibliotheque')
            ->setSection('INVESTISSEUR')
            ->setMenuorder(3);
        $manager->persist($investisseurMenu3);

        $investisseurMenu4 = new Menu();
        $investisseurMenu4->setLabel('Outils')
            ->setRoute('investisseur_outils')
            ->setSection('INVESTISSEUR')
            ->setMenuorder(4);
        $manager->persist($investisseurMenu4);

        $investisseurMenu5 = new Menu();
        $investisseurMenu5->setLabel('Gestion')
            ->setRoute('investisseur_gestion')
            ->setSection('INVESTISSEUR')
            ->setMenuorder(5);
        $manager->persist($investisseurMenu5);

        $investisseurMenu6 = new Menu();
        $investisseurMenu6->setLabel('Introduction')
            ->setRoute('investisseur_introduction')
            ->setSection('INVESTISSEUR')
            ->setMenuorder(6);
        $manager->persist($investisseurMenu6);

        // Menus pour la section INTRADAY
        $intradayMenu1 = new Menu();
        $intradayMenu1->setLabel('Présentation')
            ->setRoute('intraday_presentation')
            ->setSection('INTRADAY')
            ->setMenuorder(1);
        $manager->persist($intradayMenu1);

        $intradayMenu2 = new Menu();
        $intradayMenu2->setLabel('La Méthode')
            ->setRoute('intraday_methode')
            ->setSection('INTRADAY')
            ->setMenuorder(2);
        $manager->persist($intradayMenu2);

        $intradayMenu3 = new Menu();
        $intradayMenu3->setLabel('Bibliothèque')
            ->setRoute('intraday_bibliotheque')
            ->setSection('INTRADAY')
            ->setMenuorder(3);
        $manager->persist($intradayMenu3);

        $intradayMenu4 = new Menu();
        $intradayMenu4->setLabel('Outils')
            ->setRoute('intraday_outils')
            ->setSection('INTRADAY')
            ->setMenuorder(4);
        $manager->persist($intradayMenu4);

        $intradayMenu5 = new Menu();
        $intradayMenu5->setLabel('Gestion')
            ->setRoute('intraday_gestion')
            ->setSection('INTRADAY')
            ->setMenuorder(5);
        $manager->persist($intradayMenu5);

        $manager->flush();
    }
}
