<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use App\Repository\PageContentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AbstractController
{
    #[Route('/{route}', name: 'app_page')]
    public function show(MenuRepository $menuRepo, PageContentRepository $contentRepo, string $route): Response
    {
        $menu = $menuRepo->findOneBy(['route' => $route]);
        if (!$menu) {
            throw $this->createNotFoundException('Page non trouvée');
        }

        $pageContent = $contentRepo->findOneBy(['menu' => $menu]);

        return $this->render('home/page.html.twig', [
            'menu' => $menu,
            'pageContent' => $pageContent
        ]);
    }
}
