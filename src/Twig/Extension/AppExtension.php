<?php

namespace App\Twig\Extension;

use App\Service\MenuService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

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
        ];
    }

    public function getMenu(string $section): array
    {
        return $this->menuService->getMenuBySection($section);
    }
}
