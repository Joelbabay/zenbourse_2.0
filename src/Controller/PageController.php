<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use App\Repository\PageContentRepository;
use App\Service\CarouselService;
use App\Service\StockExampleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;

class PageController extends AbstractController
{
    #[Route('/{slug}', name: 'app_home_page', requirements: ['slug' => '[a-z0-9-]+'])]
    public function show(MenuRepository $menuRepo, PageContentRepository $contentRepo, CarouselService $carouselService, string $slug): Response
    {
        $menu = $menuRepo->findOneBy(['slug' => $slug, 'section' => 'HOME', 'isActive' => true]);
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
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user || (!$user->isInvestisseur() && !$user->hasValidTemporaryInvestorAccess())) {
            $this->addFlash('danger', 'Vous n\'avez pas accès à la méthode Investisseur.');
            return $this->redirectToRoute('app_home_page', ['slug' => 'accueil']);
        }

        $menu = $menuRepo->findOneBy(['slug' => $slug, 'section' => 'INVESTISSEUR', 'isActive' => true]);
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

    #[Route('/investisseur/{parentSlug}/{childSlug}', name: 'app_investisseur_child_page', requirements: [
        'parentSlug' => '[a-z0-9-]+',
        'childSlug' => '[a-z0-9-]+'
    ])]
    public function show_investisseur_child(
        MenuRepository $menuRepo,
        PageContentRepository $contentRepo,
        StockExampleService $stockExampleService, // Ajouter le service ici
        string $parentSlug,
        string $childSlug
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user || (!$user->isInvestisseur() && !$user->hasValidTemporaryInvestorAccess())) {
            $this->addFlash('danger', 'Vous n\'avez pas accès à la méthode Investisseur.');
            return $this->redirectToRoute('app_home_page', ['slug' => 'accueil']);
        }

        // Récupérer le menu parent
        $parentMenu = $menuRepo->findOneBy([
            'slug' => $parentSlug,
            'section' => 'INVESTISSEUR',
            'parent' => null, // Pas de parent = menu principal
            'isActive' => true
        ]);

        if (!$parentMenu) {
            throw $this->createNotFoundException('Menu parent non trouvé');
        }

        // Récupérer le sous-menu
        $childMenu = $menuRepo->findOneBy([
            'slug' => $childSlug,
            'section' => 'INVESTISSEUR',
            'parent' => $parentMenu,
            'isActive' => true
        ]);

        if (!$childMenu) {
            throw $this->createNotFoundException('Sous-menu non trouvé');
        }

        $pageContent = $contentRepo->findOneBy(['menu' => $childMenu]);

        // Récupérer les tickers pour la sidebar
        $stocksForSidebar = $stockExampleService->getExamplesByCategory($childSlug);

        return $this->render('investisseur/page.html.twig', [
            'menu' => $childMenu,
            'parentMenu' => $parentMenu,
            'pageContent' => $pageContent,
            'parentSlug' => $parentSlug,
            'childSlug' => $childSlug,
            'stocksForSidebar' => $stocksForSidebar, // Passer les tickers
            'categoryTitle' => $stockExampleService->getCategoryTitle($childSlug) // Passer le titre
        ]);
    }

    #[Route('/investisseur/{parentSlug}/{childSlug}/{tickerSlug}', name: 'app_investisseur_stock_detail', requirements: [
        'parentSlug' => '[a-z0-9-]+',
        'childSlug' => '[a-z0-9-]+',
        'tickerSlug' => '[a-z0-9-]+'
    ])]
    public function show_stock_detail(
        MenuRepository $menuRepo,
        StockExampleService $stockExampleService,
        string $parentSlug,
        string $childSlug,
        string $tickerSlug
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user || (!$user->isInvestisseur() && !$user->hasValidTemporaryInvestorAccess())) {
            $this->addFlash('danger', 'Vous n\'avez pas accès à cette section.');
            return $this->redirectToRoute('app_home_page', ['slug' => 'accueil']);
        }

        // 1. Valider et récupérer le menu parent (ex: "bibliotheque")
        $parentMenu = $menuRepo->findOneBy(['slug' => $parentSlug, 'section' => 'INVESTISSEUR', 'parent' => null, 'isActive' => true]);
        if (!$parentMenu) {
            throw $this->createNotFoundException('Menu principal de la bibliothèque non trouvé.');
        }

        // 2. Valider et récupérer le menu enfant (ex: "bulles-type-1")
        $childMenu = $menuRepo->findOneBy(['slug' => $childSlug, 'section' => 'INVESTISSEUR', 'parent' => $parentMenu, 'isActive' => true]);
        if (!$childMenu) {
            throw $this->createNotFoundException('Catégorie de la bibliothèque non trouvée.');
        }

        // 3. Récupérer les détails du ticker et son contenu associé
        $stockExample = $stockExampleService->getExampleBySlug($tickerSlug);
        if (!$stockExample || $stockExample->getCategory() !== $childSlug) {
            throw $this->createNotFoundException('Exemple boursier non trouvé dans cette catégorie.');
        }
        $pageContent = $stockExample->getPageContent();

        // 4. Récupérer tous les tickers de la même catégorie pour la sidebar
        $stocksForSidebar = $stockExampleService->getExamplesByCategory($childSlug);

        return $this->render('investisseur/stock_detail.html.twig', [
            'parentMenu' => $parentMenu,
            'childMenu' => $childMenu,
            'stockExample' => $stockExample,
            'pageContent' => $pageContent, // Passer le contenu à Twig
            'stocksForSidebar' => $stocksForSidebar,
            'categoryTitle' => $stockExampleService->getCategoryTitle($childSlug),
            'currentTickerSlug' => $tickerSlug
        ]);
    }

    #[Route('/intraday/{slug}', name: 'app_intraday_page', requirements: ['slug' => '[a-z0-9-]+'])]
    #[IsGranted('ROLE_INTRADAY')]
    public function show_intraday(MenuRepository $menuRepo, PageContentRepository $contentRepo, string $slug): Response
    {
        $menu = $menuRepo->findOneBy(['slug' => $slug, 'section' => 'INTRADAY', 'isActive' => true, 'parent' => null]);
        if (!$menu) {
            throw $this->createNotFoundException('Page intraday non trouvée');
        }

        $pageContent = $contentRepo->findOneBy(['menu' => $menu]);

        // Si le menu a des enfants, on les passe au template
        $children = $menu->getChildren()->filter(fn($child) => $child->isIsActive());

        return $this->render('intraday/page.html.twig', [
            'menu' => $menu,
            'pageContent' => $pageContent,
            'children' => $children
        ]);
    }

    #[Route('/intraday/{parentSlug}/{childSlug}', name: 'app_intraday_child_page', requirements: [
        'parentSlug' => '[a-z0-9-]+',
        'childSlug' => '[a-z0-9-]+'
    ])]
    #[IsGranted('ROLE_INTRADAY')]
    public function show_intraday_child(
        MenuRepository $menuRepo,
        PageContentRepository $contentRepo,
        string $parentSlug,
        string $childSlug
    ): Response {
        $parentMenu = $menuRepo->findOneBy(['slug' => $parentSlug, 'section' => 'INTRADAY', 'parent' => null, 'isActive' => true]);
        if (!$parentMenu) {
            throw $this->createNotFoundException('Menu parent intraday non trouvé');
        }

        $childMenu = $menuRepo->findOneBy(['slug' => $childSlug, 'section' => 'INTRADAY', 'parent' => $parentMenu, 'isActive' => true]);
        if (!$childMenu) {
            throw $this->createNotFoundException('Sous-menu intraday non trouvé');
        }

        $pageContent = $contentRepo->findOneBy(['menu' => $childMenu]);

        return $this->render('intraday/page.html.twig', [
            'menu' => $childMenu,
            'parentMenu' => $parentMenu,
            'pageContent' => $pageContent
        ]);
    }
}
