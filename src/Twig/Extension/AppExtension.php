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
        ];
    }

    public function getMenu(string $section): array
    {
        return $this->menuService->getMenuBySection($section);
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

        // The logic is only for children of INVESTISSEUR section
        if ($parent && $parent->getSection() === 'INVESTISSEUR') {
            // A child is active if we are on its page, or one of its ticker detail pages.
            if ($currentRoute === 'app_investisseur_child_page') {
                $childSlugFromRoute = $request->attributes->get('childSlug');
                return $childSlugFromRoute === $childSlug;
            }

            if ($currentRoute === 'app_investisseur_stock_detail') {
                $childSlugFromRoute = $request->attributes->get('childSlug');
                return $childSlugFromRoute === $childSlug;
            }

            return false;
        }

        // Comparaison directe avec la route du menu enfant (seulement pour les routes statiques)
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
            } elseif ($route === 'app_investisseur_child_page') {
                // Route hiérarchique pour les sous-menus investisseur
                $parent = $menu->getParent();
                if ($parent) {
                    return $this->urlGenerator->generate($route, [
                        'parentSlug' => $parent->getSlug(),
                        'childSlug' => $slug
                    ]);
                } else {
                    // Fallback si pas de parent
                    return $this->urlGenerator->generate('app_investisseur_page', ['slug' => $slug]);
                }
            } else {
                return $this->urlGenerator->generate($route);
            }
        } catch (\Exception $e) {
            // En cas d'erreur, retourner une URL par défaut
            return '#';
        }
    }
}
