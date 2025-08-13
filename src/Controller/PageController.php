<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use App\Repository\PageContentRepository;
use App\Service\CarouselService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PageController extends AbstractController
{
    #[Route('/{slug}', name: 'app_home_page', requirements: ['slug' => '[a-z0-9-]+'])]
    public function show(MenuRepository $menuRepo, PageContentRepository $contentRepo, CarouselService $carouselService, string $slug): Response
    {
        $menu = $menuRepo->findOneBy(['slug' => $slug, 'section' => 'HOME']);
        if (!$menu) {
            throw $this->createNotFoundException('Page non trouvée');
        }

        $pageContent = $contentRepo->findOneBy(['menu' => $menu]);

        return $this->render('home/page.html.twig', [
            'menu' => $menu,
            'pageContent' => $pageContent,
            'carousel_service' => $carouselService
        ]);
    }

    #[Route('/investisseur/{slug}', name: 'app_investisseur_page', requirements: ['slug' => '[a-z0-9-]+'])]
    public function show_investisseur(MenuRepository $menuRepo, PageContentRepository $contentRepo, string $slug): Response
    {
        $user = $this->getUser();
        if (!$user || (!$user->isInvestisseur() && !$user->hasValidTemporaryInvestorAccess())) {
            $this->addFlash('danger', 'Vous n\'avez pas accès à la méthode Investisseur.');
            return $this->redirectToRoute('home');
        }

        $menu = $menuRepo->findOneBy(['slug' => $slug, 'section' => 'INVESTISSEUR']);
        if (!$menu) {
            throw $this->createNotFoundException('Page investisseur non trouvée');
        }

        $pageContent = $contentRepo->findOneBy(['menu' => $menu]);

        return $this->render('investisseur/page.html.twig', [
            'menu' => $menu,
            'pageContent' => $pageContent,
            'slug' => $slug
        ]);
    }

    #[Route('/intraday/{slug}', name: 'app_intraday_page', requirements: ['slug' => '[a-z0-9-]+'])]
    #[IsGranted('ROLE_INTRADAY')]
    public function show_intraday(MenuRepository $menuRepo, PageContentRepository $contentRepo, string $slug): Response
    {
        $menu = $menuRepo->findOneBy(['slug' => $slug, 'section' => 'INTRADAY']);
        if (!$menu) {
            throw $this->createNotFoundException('Page intraday non trouvée');
        }

        $pageContent = $contentRepo->findOneBy(['menu' => $menu]);

        return $this->render('intraday/page.html.twig', [
            'menu' => $menu,
            'pageContent' => $pageContent,
        ]);
    }
}
