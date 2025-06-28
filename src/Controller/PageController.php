<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use App\Repository\PageContentRepository;
use App\Service\CarouselService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AbstractController
{
    #[Route('/{slug}', name: 'app_home_page', requirements: ['slug' => '[a-z0-9-]+'])]
    public function show(MenuRepository $menuRepo, PageContentRepository $contentRepo, CarouselService $carouselService, string $slug): Response
    {
        $menu = $menuRepo->findOneBy(['slug' => $slug]);
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
}
