<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Entity\Download;
use App\Entity\IntradayRequest;
use App\Entity\InvestisseurRequest;
use App\Entity\Menu;
use App\Entity\PageContent;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
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
            ->setTitle('Zenbourse 50');
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            ->setPaginatorPageSize(15);
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile('/css/admin.css')
            ->addHtmlContentToHead('<script src="https://cdn.tiny.cloud/1/1ii47bnpw6kbzwupf0piyjyp0ixu6ih9u4hwjkuk77de2l70/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>')
            ->addHtmlContentToBody('
            <script> tinymce.init({
				selector: \'#my_tinymce\'
			    }); 
            </script>');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::linkToCrud('Boîte de réception', 'fa fa-inbox', Contact::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user-friends', User::class)->setController(UserCrudController::class);
        yield MenuItem::linkToCrud('Téléchargement liste valeurs 2020', 'fa fa-download', Download::class)
            ->setController(UserDownloadCrudController::class);
        yield MenuItem::linkToCrud('Méthode Investisseur', 'fa fa-star', InvestisseurRequest::class)
            ->setController(InterestedUsersCrudController::class);
        yield MenuItem::linkToCrud('Méthode Intraday', 'fa fa-star', IntradayRequest::class)
            ->setController(IntradayRequestCrudController::class);
        yield MenuItem::linkToCrud('Menus', 'fa fa-list', Menu::class);
        yield MenuItem::linkToCrud('Contenus des Pages', 'fa fa-edit', PageContent::class);
    }
}
