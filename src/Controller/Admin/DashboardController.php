<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Entity\Menu;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(ContactCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Zenbourse');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Boîte de réception', 'fa fa-inbox', Contact::class);
        yield MenuItem::subMenu('Compte', 'fas fa-user')->setSubItems(
            [
                MenuItem::linkToCrud('Utilisateurs', 'fas fa-user-friends', User::class)
                    ->setController(UserCrudController::class),
                MenuItem::linkToCrud('Ajouter', 'fas fa-plus', User::class)->setAction(Crud::PAGE_NEW)
                    ->setController(UserCrudController::class),
            ]
        );
        yield MenuItem::linkToCrud('Menus', 'fa fa-list', Menu::class);
        yield MenuItem::linkToCrud('Liste des 300 valeurs', 'fa fa-download', User::class)
            ->setController(UserDownloadCrudController::class);
    }
}
