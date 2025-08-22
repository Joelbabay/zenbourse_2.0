<?php

// src/Service/MenuService.php
namespace App\Service;

use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class MenuService
{
    private $menuRepository;
    private $entityManager;
    private $requestStack;

    public function __construct(MenuRepository $menuRepository, EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->menuRepository = $menuRepository;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    public function getMenuBySection(string $section): array
    {
        return $this->menuRepository->findBy(['section' => $section, 'parent' => null, 'isActive' => true], ['menuorder' => 'ASC']);
    }

    /**
     * Récupère un menu avec tous ses enfants triés par ordre
     */
    public function getMenuWithChildren(string $section): array
    {
        return $this->menuRepository->findBySectionWithChildren($section);
    }

    /**
     * Vérifie si un menu ou ses enfants sont actifs
     */

    public function isMenuActive($menu): bool
    {
        $request = $this->requestStack->getMainRequest();
        if (!$request) {
            return false;
        }

        $currentRoute = $request->attributes->get('_route');
        $currentSlug = $request->attributes->get('slug');
        $parentSlug = $request->attributes->get('parentSlug');
        $childSlug = $request->attributes->get('childSlug');

        // 1. Comparaison directe avec la route du menu
        if ($currentRoute === $menu->getRoute()) {
            return true;
        }

        // 2. Pour les menus PARENTS : vérifier si c'est le parent actuel
        if ($menu->getParent() === null) {
            // PRIORITÉ : Le parent est actif si son slug correspond au parentSlug de l'URL
            if ($menu->getSlug() && $parentSlug && $menu->getSlug() === $parentSlug) {
                return true;
            }

            // Seulement si pas de parentSlug : vérifier le slug direct
            if (!$parentSlug && $menu->getSlug() && $currentSlug && $menu->getSlug() === $currentSlug) {
                return true;
            }

            // Important: retourner false explicitement pour éviter que d'autres parents soient actifs
            return false;
        }

        // 3. Pour les menus ENFANTS : vérifier si c'est l'enfant actuel
        if ($menu->getParent() !== null) {
            // L'enfant est actif si son slug correspond au childSlug
            if ($menu->getSlug() && $childSlug && $menu->getSlug() === $childSlug) {
                return true;
            }

            // Fallback avec currentSlug seulement si pas de childSlug
            if (!$childSlug && $menu->getSlug() && $currentSlug && $menu->getSlug() === $currentSlug) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie si un menu enfant est actif
     */
    public function isChildActive($child): bool
    {
        return $this->isMenuActive($child);
    }

    /**
     * Récupère le menu parent actif pour afficher le sous-menu
     */
    public function getActiveParentMenu(string $currentRoute, string $section): ?object
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return null;
        }

        $parentSlug = $request->attributes->get('parentSlug');
        $slug = $request->attributes->get('slug');
        $childSlug = $request->attributes->get('childSlug');

        // 1. Si parentSlug existe, c'est le plus fiable - on cherche le parent correspondant
        if ($parentSlug) {
            $parentMenu = $this->menuRepository->findOneBy([
                'slug' => $parentSlug,
                'parent' => null,
                'section' => $section,
                'isActive' => true
            ]);
            if ($parentMenu) {
                return $parentMenu;
            }
        }

        // 2. Vérifier si le slug actuel est un menu parent de cette section
        if ($slug) {
            $parentMenu = $this->menuRepository->findOneBy([
                'slug' => $slug,
                'parent' => null,
                'section' => $section,
                'isActive' => true
            ]);
            if ($parentMenu) {
                return $parentMenu;
            }
        }

        // 3. Vérifier si le slug actuel ou childSlug est un menu enfant et récupérer son parent
        $slugsToCheck = array_filter([$slug, $childSlug]);
        foreach ($slugsToCheck as $slugToCheck) {
            $childMenu = $this->menuRepository->findOneBy([
                'slug' => $slugToCheck,
                'isActive' => true
            ]);

            if (
                $childMenu && $childMenu->getParent() &&
                $childMenu->getParent()->getSection() === $section &&
                $childMenu->getParent()->isIsActive()
            ) {
                return $childMenu->getParent();
            }
        }

        // 4. Fallback : route statique
        return $this->menuRepository->findOneBy([
            'section' => $section,
            'parent' => null,
            'route' => $currentRoute,
            'isActive' => true
        ]);
    }
    /**
     * Méthode pour obtenir le menu enfant actuel (utile pour les templates)
     */
    /**
     * Méthode pour obtenir le menu enfant actuel (utile pour les templates)
     */
    public function getActiveChildMenu(string $section): ?object
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return null;
        }

        $childSlug = $request->attributes->get('childSlug');
        $slug = $request->attributes->get('slug');

        // Vérifier d'abord avec childSlug
        if ($childSlug) {
            $childMenu = $this->menuRepository->findOneBy([
                'slug' => $childSlug,
                'isActive' => true
            ]);

            if (
                $childMenu && $childMenu->getParent() &&
                $childMenu->getParent()->getSection() === $section
            ) {
                return $childMenu;
            }
        }

        // Fallback avec slug si c'est un enfant
        if ($slug) {
            $childMenu = $this->menuRepository->findOneBy([
                'slug' => $slug,
                'isActive' => true
            ]);

            if (
                $childMenu && $childMenu->getParent() &&
                $childMenu->getParent()->getSection() === $section
            ) {
                return $childMenu;
            }
        }

        return null;
    }

    /**
     * Méthode de debug pour comprendre les valeurs actuelles
     */
    public function debugCurrentRequest(): array
    {
        $request = $this->requestStack->getMainRequest();
        if (!$request) {
            return [];
        }

        return [
            'route' => $request->attributes->get('_route'),
            'slug' => $request->attributes->get('slug'),
            'parentSlug' => $request->attributes->get('parentSlug'),
            'childSlug' => $request->attributes->get('childSlug'),
            'tickerSlug' => $request->attributes->get('tickerSlug'),
            'all_attributes' => $request->attributes->all()
        ];
    }

    /**
     * Trouve un menu par son slug
     */
    public function findBySlug(string $slug): ?object
    {
        return $this->menuRepository->findBySlug($slug);
    }

    /**
     * Trouve un menu par sa route
     */
    public function findByRoute(string $route): ?object
    {
        return $this->menuRepository->findByRoute($route);
    }

    /**
     * Génère un slug unique pour un menu
     */
    public function generateSlug(string $label, ?int $excludeId = null): string
    {
        return $this->menuRepository->generateUniqueSlug($label, $excludeId);
    }

    /**
     * Vérifie si un slug existe
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        return $this->menuRepository->slugExists($slug, $excludeId);
    }

    public function getMenuBySlug(string $slug): ?object
    {
        return $this->menuRepository->findOneBy(['slug' => $slug, 'isActive' => true]);
    }

    public function getMenuRepository(): MenuRepository
    {
        return $this->menuRepository;
    }
}
