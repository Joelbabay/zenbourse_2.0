<?php

// src/Service/MenuService.php
namespace App\Service;

use App\Repository\MenuRepository;

class MenuService
{
    private $menuRepository;

    public function __construct(MenuRepository $menuRepository)
    {
        $this->menuRepository = $menuRepository;
    }

    public function getMenuBySection(string $section): array
    {
        return $this->menuRepository->findBy(['section' => $section, 'parent' => null]);
    }
}
