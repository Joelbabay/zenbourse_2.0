<?php

namespace App\Twig\Extension;

use App\Service\MenuService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Entity\Menu;

class AppExtension extends AbstractExtension
{
    private $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_menu', [$this, 'getMenu']),
            new TwigFunction('isActiveMenu', [$this, 'isActiveMenu']),
        ];
    }

    public function getMenu(string $section): array
    {
        return $this->menuService->getMenuBySection($section);
    }

    public function isActiveMenu(string $currentRoute, Menu $menu): bool
    {
        // Vérifie si la route actuelle correspond à la route du menu parent
        if ($currentRoute === $menu->getRoute()) {
            return true;
        }

        // Vérifie si la route actuelle correspond à celle des enfants
        foreach ($menu->getChildren() as $child) {
            if ($currentRoute === $child->getRoute()) {
                return true;
            }
        }

        return false;
    }
}
