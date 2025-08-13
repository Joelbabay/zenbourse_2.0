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
        // Gestion spéciale pour les routes dynamiques INVESTISSEUR
        if ($section === 'INVESTISSEUR' && $currentRoute === 'app_investisseur_page') {
            $request = $this->requestStack->getCurrentRequest();
            if ($request) {
                $currentSlug = $request->get('slug');

                // Debug: afficher le slug actuel
                // error_log("Current slug: " . $currentSlug);

                // D'abord chercher si le slug correspond à un menu parent
                $parentMenu = $this->menuRepository->findOneBy([
                    'section' => 'INVESTISSEUR',
                    'parent' => null,
                    'slug' => $currentSlug
                ]);

                if ($parentMenu) {
                    // Debug: afficher le parent trouvé
                    // error_log("Found parent menu: " . $parentMenu->getLabel());

                    // Charger le parent avec ses enfants
                    $parentWithChildren = $this->menuRepository->find($parentMenu->getId());
                    return $parentWithChildren;
                }

                // Sinon chercher si le slug correspond à un sous-menu
                $childMenu = $this->menuRepository->findOneBy([
                    'section' => 'INVESTISSEUR',
                    'slug' => $currentSlug
                ]);

                if ($childMenu && $childMenu->getParent()) {
                    // Debug: afficher l'enfant et son parent
                    // error_log("Found child menu: " . $childMenu->getLabel() . " with parent: " . $childMenu->getParent()->getLabel());

                    // Charger le parent avec ses enfants
                    $parentWithChildren = $this->menuRepository->find($childMenu->getParent()->getId());
                    return $parentWithChildren;
                }
            }
        }

        // Gestion générique pour les autres sections
        if ($currentRoute !== 'app_investisseur_page') {
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
