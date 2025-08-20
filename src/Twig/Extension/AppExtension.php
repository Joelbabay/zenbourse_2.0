<?php

namespace App\Twig\Extension;

use App\Service\MenuService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Entity\Menu;

class AppExtension extends AbstractExtension
{
    private $menuService;
    private $requestStack;
    private $urlGenerator;

    public function __construct(
        MenuService $menuService,
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->menuService = $menuService;
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_menu', [$this, 'getMenu']),
            new TwigFunction('get_menu_with_children', [$this, 'getMenuWithChildren']),
            new TwigFunction('is_active_menu', [$this, 'isActiveMenu']),
            new TwigFunction('is_active_child', [$this, 'isActiveChild']),
            new TwigFunction('get_active_parent_menu', [$this, 'getActiveParentMenu']),
            new TwigFunction('menu_url', [$this, 'generateMenuUrl']),
            new TwigFunction('generate_breadcrumbs', [$this, 'generateBreadcrumbs']),
            new TwigFunction('get_first_menu_url', [$this, 'getFirstMenuUrl']),
        ];
    }

    public function getMenu(string $section): array
    {
        return $this->menuService->getMenuBySection($section);
    }

    public function getFirstMenuUrl(string $section): string
    {
        $menu = $this->menuService->getMenuRepository()->findFirstActiveBySection($section);

        if ($menu) {
            return $this->generateMenuUrl($menu);
        }

        return '#'; // URL de secours si aucun menu n'est trouvé
    }

    public function getMenuWithChildren(string $section): array
    {
        return $this->menuService->getMenuWithChildren($section);
    }

    public function isActiveMenu(string $currentRoute, Menu $menu): bool
    {
        // Pour les menus HOME, comparer avec le slug
        if ($menu->getSection() === 'HOME') {
            if ($currentRoute === 'app_home_page') {
                $request = $this->requestStack->getCurrentRequest();
                if ($request) {
                    $currentSlug = $request->get('slug');
                    return $currentSlug === $menu->getSlug();
                }
            }
            if ($currentRoute === $menu->getRoute()) {
                return true;
            }
        } elseif ($menu->getSection() === 'INTRADAY') {
            // Pour les menus INTRADAY, comparer avec le slug
            if ($currentRoute === 'app_intraday_page') {
                $request = $this->requestStack->getCurrentRequest();
                if ($request) {
                    $currentSlug = $request->get('slug');
                    return $currentSlug === $menu->getSlug();
                }
            }
            if ($currentRoute === $menu->getRoute()) {
                return true;
            }
        } elseif ($menu->getSection() === 'INVESTISSEUR') {
            $request = $this->requestStack->getCurrentRequest();
            if (!$request) {
                return false;
            }

            // A parent menu is active if we are on its page, or one of its child pages, or a ticker detail page.
            if ($currentRoute === 'app_investisseur_page') {
                $slugFromRoute = $request->attributes->get('slug');
                return $slugFromRoute === $menu->getSlug();
            }

            if ($currentRoute === 'app_investisseur_child_page' || $currentRoute === 'app_investisseur_stock_detail') {
                $parentSlugFromRoute = $request->attributes->get('parentSlug');
                return $parentSlugFromRoute === $menu->getSlug();
            }

            return false;
        } else {
            // Gestion générique pour les autres sections
            if (str_ends_with($currentRoute, '_detail')) {
                $parentRoute = str_replace('_detail', '', $currentRoute);
                if ($menu->getRoute() === $parentRoute) {
                    return true;
                }
            }

            // Pour les autres sections, comparer avec la route du menu parent
            if ($currentRoute === $menu->getRoute()) {
                return true;
            }
        }

        // Vérifier si la route actuelle correspond à celle des enfants (sauf pour INVESTISSEUR déjà géré)
        if ($menu->getSection() !== 'INVESTISSEUR') {
            foreach ($menu->getChildren() as $child) {
                if ($currentRoute === $child->getRoute()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isActiveChild(string $currentRoute, Menu $child): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return false;
        }

        $childSlug = $child->getSlug();
        $parent = $child->getParent();

        if ($parent) {
            // Logique pour les sections avec sous-menus dynamiques
            if ($parent->getSection() === 'INVESTISSEUR') {
                if ($currentRoute === 'app_investisseur_child_page' || $currentRoute === 'app_investisseur_stock_detail') {
                    return $request->attributes->get('childSlug') === $childSlug;
                }
            } elseif ($parent->getSection() === 'INTRADAY') {
                if ($currentRoute === 'app_intraday_child_page') {
                    return $request->attributes->get('childSlug') === $childSlug;
                }
            }
        }

        // Comparaison directe avec la route du menu enfant (pour les routes statiques)
        if ($currentRoute === $child->getRoute()) {
            return true;
        }

        // Gestion générique pour les routes de détail qui commencent par une route parent
        if (str_ends_with($currentRoute, '_detail')) {
            $parentRoute = str_replace('_detail', '', $currentRoute);
            if ($child->getRoute() === $parentRoute) {
                return true;
            }
        }

        return false;
    }

    public function getActiveParentMenu(string $currentRoute, string $section): ?Menu
    {
        return $this->menuService->getActiveParentMenu($currentRoute, $section);
    }

    /**
     * Génère l'URL pour un menu en gérant automatiquement les paramètres
     */
    public function generateMenuUrl(Menu $menu): string
    {
        $route = $menu->getRoute();
        $slug = $menu->getSlug();

        // Routes dynamiques qui utilisent des slugs
        $dynamicRoutes = [
            'app_home_page',
            'app_investisseur_page',
            'app_intraday_page'
        ];

        try {
            if (in_array($route, $dynamicRoutes)) {
                return $this->urlGenerator->generate($route, ['slug' => $slug]);
            } elseif ($route === 'app_investisseur_child_page' || $route === 'app_intraday_child_page') {
                // Route hiérarchique pour les sous-menus
                $parent = $menu->getParent();
                if ($parent) {
                    return $this->urlGenerator->generate($route, [
                        'parentSlug' => $parent->getSlug(),
                        'childSlug' => $slug
                    ]);
                } else {
                    // Fallback si pas de parent (devrait être géré par la logique du dessus)
                    $fallbackRoute = ($menu->getSection() === 'INTRADAY') ? 'app_intraday_page' : 'app_investisseur_page';
                    return $this->urlGenerator->generate($fallbackRoute, ['slug' => $slug]);
                }
            } else {
                return $this->urlGenerator->generate($route);
            }
        } catch (\Exception $e) {
            // En cas d'erreur, retourner une URL par défaut
            return '#';
        }
    }

    public function generateBreadcrumbs(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return [];
        }

        $crumbs = [];

        // 1. Toujours ajouter l'accueil
        $crumbs[] = ['label' => 'Accueil', 'url' => $this->urlGenerator->generate('app_home_page', ['slug' => 'accueil'])];

        $route = $request->attributes->get('_route');
        $parentSlug = $request->attributes->get('parentSlug');
        $childSlug = $request->attributes->get('childSlug');
        $tickerSlug = $request->attributes->get('tickerSlug');

        // 2. Gérer les pages de la section Investisseur (les plus complexes)
        if (str_starts_with($route, 'app_investisseur')) {
            $crumbs[] = ['label' => 'Investisseur', 'url' => $this->urlGenerator->generate('app_investisseur_page', ['slug' => 'presentation'])];

            if ($parentSlug) {
                $parentMenu = $this->menuService->getMenuBySlug($parentSlug);
                if ($parentMenu) {
                    $crumbs[] = ['label' => $parentMenu->getLabel(), 'url' => $this->generateMenuUrl($parentMenu)];
                }
            }

            if ($childSlug) {
                $childMenu = $this->menuService->getMenuBySlug($childSlug);
                if ($childMenu) {
                    $crumbs[] = ['label' => $childMenu->getLabel(), 'url' => $this->generateMenuUrl($childMenu)];
                }
            }

            if ($tickerSlug) {
                // Pour le ticker, on ne met pas de lien car c'est la page active
                $crumbs[] = ['label' => 'Détail de la valeur'];
            }
        }
        // 3. Ajouter ici la logique pour les autres sections (HOME, INTRADAY) si nécessaire

        return $crumbs;
    }
}
