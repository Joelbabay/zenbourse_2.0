<?php

// src/Service/MenuService.php
namespace App\Service;

use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;

class MenuService
{
    private $menuRepository;
    private $entityManager;

    public function __construct(MenuRepository $menuRepository, EntityManagerInterface $entityManager)
    {
        $this->menuRepository = $menuRepository;
        $this->entityManager = $entityManager;
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
        // D'abord, chercher si la route actuelle correspond à un menu parent
        $parentMenu = $this->menuRepository->findOneBy([
            'section' => $section,
            'parent' => null,
            'route' => $currentRoute
        ]);

        if ($parentMenu) {
            // Charger le parent avec ses enfants via DQL
            $dql = "SELECT m, c FROM App\Entity\Menu m LEFT JOIN m.children c WHERE m.id = :id ORDER BY c.menuorder ASC";
            $query = $this->entityManager->createQuery($dql);
            $query->setParameter('id', $parentMenu->getId());
            $result = $query->getResult();

            if (!empty($result)) {
                return $result[0];
            }
        }

        // Si aucun parent n'est actif, chercher parmi les enfants pour trouver leur parent
        $childMenu = $this->menuRepository->findOneBy([
            'section' => $section,
            'route' => $currentRoute
        ]);

        if ($childMenu && $childMenu->getParent()) {
            // Charger le parent avec ses enfants via DQL
            $parentId = $childMenu->getParent()->getId();
            $dql = "SELECT m, c FROM App\Entity\Menu m LEFT JOIN m.children c WHERE m.id = :id ORDER BY c.menuorder ASC";
            $query = $this->entityManager->createQuery($dql);
            $query->setParameter('id', $parentId);
            $result = $query->getResult();

            if (!empty($result)) {
                return $result[0];
            }
        }

        // Gestion spéciale pour les routes dynamiques de la bibliothèque
        if ($section === 'INVESTISSEUR' && ($currentRoute === 'investisseur_bibliotheque_category' || $currentRoute === 'investisseur_bibliotheque_detail')) {
            // Chercher le parent "Bibliothèque"
            $bibliothequeParent = $this->menuRepository->findOneBy([
                'section' => 'INVESTISSEUR',
                'parent' => null,
                'label' => 'Bibliothèque'
            ]);

            if ($bibliothequeParent) {
                // Charger le parent avec ses enfants via DQL
                $dql = "SELECT m, c FROM App\Entity\Menu m LEFT JOIN m.children c WHERE m.id = :id ORDER BY c.menuorder ASC";
                $query = $this->entityManager->createQuery($dql);
                $query->setParameter('id', $bibliothequeParent->getId());
                $result = $query->getResult();

                if (!empty($result)) {
                    return $result[0];
                }
            }
        }

        // Gestion spéciale pour les routes de détail des chandeliers japonais (PRIORITÉ)
        if ($section === 'INVESTISSEUR' && $currentRoute === 'investisseur_methode_chandeliers_japonais_detail') {
            // Chercher le parent "La Méthode" au lieu de "Chandeliers japonais"
            $methodeParent = $this->menuRepository->findOneBy([
                'section' => 'INVESTISSEUR',
                'parent' => null,
                'label' => 'La Méthode'
            ]);

            if ($methodeParent) {
                // Charger le parent avec ses enfants via DQL
                $dql = "SELECT m, c FROM App\Entity\Menu m LEFT JOIN m.children c WHERE m.id = :id ORDER BY c.menuorder ASC";
                $query = $this->entityManager->createQuery($dql);
                $query->setParameter('id', $methodeParent->getId());
                $result = $query->getResult();

                if (!empty($result)) {
                    return $result[0];
                }
            }
        }

        // Gestion générique pour les routes de détail qui commencent par une route parent
        if ($section === 'INVESTISSEUR' && str_ends_with($currentRoute, '_detail')) {
            // Extraire la route parent en supprimant '_detail'
            $parentRoute = str_replace('_detail', '', $currentRoute);

            // Chercher le menu enfant qui a cette route
            $childMenu = $this->menuRepository->findOneBy([
                'section' => 'INVESTISSEUR',
                'route' => $parentRoute
            ]);

            if ($childMenu && $childMenu->getParent()) {
                // Charger le parent avec ses enfants via DQL
                $parentId = $childMenu->getParent()->getId();
                $dql = "SELECT m, c FROM App\Entity\Menu m LEFT JOIN m.children c WHERE m.id = :id ORDER BY c.menuorder ASC";
                $query = $this->entityManager->createQuery($dql);
                $query->setParameter('id', $parentId);
                $result = $query->getResult();

                if (!empty($result)) {
                    return $result[0];
                }
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
