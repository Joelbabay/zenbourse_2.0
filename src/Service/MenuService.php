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
        return $this->menuRepository->findBy(['section' => $section, 'parent' => null], ['menuorder' => 'ASC']);
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
    public function isMenuActive(string $currentRoute, $menu): bool
    {
        // Pour les menus HOME, comparer avec le slug de la route actuelle
        if ($menu->getSection() === 'HOME') {
            // Extraire le slug de la route actuelle si c'est app_home_page
            if ($currentRoute === 'app_home_page') {
                // Récupérer le slug depuis la requête (sera géré dans l'extension Twig)
                return true; // Temporaire, sera géré plus bas
            }

            // Comparer avec la route du menu
            if ($currentRoute === $menu->getRoute()) {
                return true;
            }
        } else {
            // Pour les autres sections, comparer avec la route
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

    /**
     * Vérifie si un menu enfant est actif
     */
    public function isChildActive(string $currentRoute, $child): bool
    {
        // Comparaison directe avec la route du menu enfant
        if ($currentRoute === $child->getRoute()) {
            return true;
        }

        // Gestion spéciale pour les routes dynamiques de la bibliothèque
        if ($currentRoute === 'investisseur_bibliotheque_category' || $currentRoute === 'investisseur_bibliotheque_detail') {
            // Récupérer la catégorie depuis la requête (sera géré dans l'extension Twig)
            // Pour l'instant, on considère que si on est sur une route de catégorie, 
            // tous les enfants de la bibliothèque sont potentiellement actifs
            // La vraie logique sera dans l'extension Twig avec le paramètre de la requête
            return true;
        }

        return false;
    }

    /**
     * Récupère le menu parent actif pour afficher le sous-menu
     */
    public function getActiveParentMenu(string $currentRoute, string $section): ?object
    {
        // Gère toutes les routes de la section INVESTISSEUR
        if ($section === 'INVESTISSEUR' && in_array($currentRoute, ['app_investisseur_page', 'app_investisseur_child_page', 'app_investisseur_stock_detail'])) {
            $request = $this->requestStack->getCurrentRequest();
            if ($request) {
                $parentSlug = $request->attributes->get('parentSlug');
                $slug = $request->attributes->get('slug');

                // Pour les pages enfant ou détail de ticker, le parentSlug est l'identifiant fiable.
                if ($parentSlug) {
                    $parentMenu = $this->menuRepository->findOneBy(['slug' => $parentSlug, 'parent' => null, 'section' => 'INVESTISSEUR']);
                    if ($parentMenu) {
                        return $this->menuRepository->find($parentMenu->getId()); // find() pour charger les enfants
                    }
                }

                // Pour les pages parent (ou les enfants consultés directement via slug)
                if ($slug) {
                    // Est-ce le slug d'un menu parent ?
                    $parentMenu = $this->menuRepository->findOneBy(['slug' => $slug, 'parent' => null, 'section' => 'INVESTISSEUR']);
                    if ($parentMenu) {
                        return $this->menuRepository->find($parentMenu->getId());
                    }

                    // Sinon, est-ce le slug d'un menu enfant ? Si oui, on retourne son parent.
                    $childMenu = $this->menuRepository->findOneBy(['slug' => $slug, 'section' => 'INVESTISSEUR']);
                    if ($childMenu && $childMenu->getParent()) {
                        return $this->menuRepository->find($childMenu->getParent()->getId());
                    }
                }
            }
        }

        // Gestion générique pour les autres sections
        if ($currentRoute !== 'app_investisseur_page' && $currentRoute !== 'app_investisseur_child_page') {
            // D'abord, chercher si la route actuelle correspond à un menu parent
            $parentMenu = $this->menuRepository->findOneBy([
                'section' => $section,
                'parent' => null,
                'route' => $currentRoute
            ]);

            if ($parentMenu) {
                return $parentMenu;
            }

            // Si aucun parent n'est actif, chercher parmi les enfants pour trouver leur parent
            $childMenu = $this->menuRepository->findOneBy([
                'section' => $section,
                'route' => $currentRoute
            ]);

            if ($childMenu && $childMenu->getParent()) {
                return $childMenu->getParent();
            }
        }

        return null;
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
}
