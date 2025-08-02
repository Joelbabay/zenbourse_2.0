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
        } else {
            // Gestion spéciale pour le parent Bibliothèque sur les routes dynamiques
            if (
                $menu->getLabel() === 'Bibliothèque'
                && in_array($currentRoute, ['investisseur_bibliotheque_category', 'investisseur_bibliotheque_detail'])
            ) {
                $request = $this->requestStack->getCurrentRequest();
                if ($request) {
                    $category = $request->get('category');
                    foreach ($menu->getChildren() as $child) {
                        if ($child->getSlug() === $category) {
                            return true;
                        }
                    }
                }
            }

            // Gestion spéciale pour le parent Chandeliers japonais sur les routes de détail
            if (
                $menu->getRoute() === 'investisseur_methode_chandeliers_japonais'
                && $currentRoute === 'investisseur_methode_chandeliers_japonais_detail'
            ) {
                return true;
            }

            // Gestion générique pour les routes de détail qui commencent par une route parent
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

        // Vérifier si la route actuelle correspond à celle des enfants
        foreach ($menu->getChildren() as $child) {
            if ($currentRoute === $child->getRoute()) {
                return true;
            }
        }

        return false;
    }

    public function isActiveChild(string $currentRoute, Menu $child): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        $childSlug = $child->getSlug();
        $parent = $child->getParent();

        // Gestion spéciale pour les sous-menus de la bibliothèque (PRIORITÉ)
        if ($parent && $parent->getLabel() === 'Bibliothèque') {
            if (in_array($currentRoute, ['investisseur_bibliotheque_category', 'investisseur_bibliotheque_detail']) && $request) {
                // Essayer d'obtenir le paramètre category de plusieurs façons
                $category = $request->attributes->get('category') ?? $request->get('category');
                // Fallback : extraire de l'URL si besoin
                if (!$category && preg_match('#/investisseur/bibliotheque/([^/]+)#', $request->getPathInfo(), $m)) {
                    $category = $m[1];
                }

                return $category === $childSlug;
            }
        }

        // Gestion spéciale pour les sous-menus Chandeliers japonais (PRIORITÉ)
        if ($parent && $parent->getLabel() === 'Chandeliers japonais') {
            if ($currentRoute === 'investisseur_methode_chandeliers_japonais_detail' && $request) {
                $slug = $request->attributes->get('slug') ?? $request->get('slug');
                if (!$slug && preg_match('#/chandeliers-japonais/([^/]+)#', $request->getPathInfo(), $m)) {
                    $slug = $m[1];
                }
                return $slug === $childSlug;
            }
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
        $parent = $menu->getParent();

        // Cas spécial : sous-menu de la Bibliothèque
        if ($parent && $parent->getLabel() === 'Bibliothèque') {
            return $this->urlGenerator->generate('investisseur_bibliotheque_category', ['category' => $slug]);
        }
        // Cas spécial : sous-menu des Chandeliers japonais
        if ($parent && $parent->getLabel() === 'Chandeliers japonais') {
            return $this->urlGenerator->generate('investisseur_methode_chandeliers_japonais_detail', ['slug' => $slug]);
        }

        // Routes qui nécessitent le paramètre slug
        $routesWithSlug = [
            'app_home_page',
            'home'
        ];

        // Routes INTRADAY qui utilisent la route dynamique
        $intradayRoutes = [
            'app_intraday_page'
        ];

        // Routes de bibliothèque qui utilisent la route dynamique (fallback)
        $bibliothequeRoutes = [
            'investisseur_bibliotheque_bulles-type-1',
            'investisseur_bibliotheque_bulles-type-2',
            'investisseur_bibliotheque_ramassage',
            'investisseur_bibliotheque_ramassage-pic',
            'investisseur_bibliotheque_pic-ramassage',
            'investisseur_bibliotheque_pics-de-volumes',
            'investisseur_bibliotheque_volumes-faibles',
            'investisseur_bibliotheque_introductions',
        ];

        try {
            if (in_array($route, $routesWithSlug)) {
                return $this->urlGenerator->generate($route, ['slug' => $slug]);
            } elseif (in_array($route, $intradayRoutes)) {
                return $this->urlGenerator->generate($route, ['slug' => $slug]);
            } elseif (in_array($route, $bibliothequeRoutes)) {
                // Utiliser la route dynamique pour toutes les catégories de bibliothèque
                return $this->urlGenerator->generate('investisseur_bibliotheque_category', ['category' => $slug]);
            } else {
                return $this->urlGenerator->generate($route);
            }
        } catch (\Exception $e) {
            // En cas d'erreur, retourner une URL par défaut
            return '#';
        }
    }
}
