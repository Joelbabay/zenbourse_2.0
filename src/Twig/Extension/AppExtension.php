<?php

namespace App\Twig\Extension;

use App\Service\MenuService;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Entity\Menu;

class AppExtension extends AbstractExtension
{
    private $menuService;
    private $requestStack;

    public function __construct(MenuService $menuService, RequestStack $requestStack)
    {
        $this->menuService = $menuService;
        $this->requestStack = $requestStack;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_menu', [$this, 'getMenu']),
            new TwigFunction('get_menu_with_children', [$this, 'getMenuWithChildren']),
            new TwigFunction('is_active_menu', [$this, 'isActiveMenu']),
            new TwigFunction('is_active_child', [$this, 'isActiveChild']),
            new TwigFunction('get_active_parent_menu', [$this, 'getActiveParentMenu']),
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
        // Comparaison directe avec la route du menu enfant
        if ($currentRoute === $child->getRoute()) {
            return true;
        }

        // Gestion spéciale pour les routes dynamiques de la bibliothèque
        if ($currentRoute === 'investisseur_bibliotheque_category' || $currentRoute === 'investisseur_bibliotheque_detail') {
            $request = $this->requestStack->getCurrentRequest();
            if ($request) {
                $category = $request->get('category');
                // Extraire la catégorie du slug du menu enfant
                $childSlug = $child->getSlug();
                return $category === $childSlug;
            }
        }

        return false;
    }

    public function getActiveParentMenu(string $currentRoute, string $section): ?Menu
    {
        return $this->menuService->getActiveParentMenu($currentRoute, $section);
    }
}
