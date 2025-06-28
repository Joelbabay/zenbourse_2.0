# Architecture des Menus - Zenbourse

## Vue d'ensemble

L'architecture des menus de Zenbourse est basée sur une structure de base de données avec des relations parent/enfant, utilisant Twig pour l'affichage et des services pour la logique métier.

## Structure de la base de données

### Entité Menu

```php
Menu {
    id: int
    label: string          // Nom affiché du menu
    route: string          // Route Symfony
    parent: Menu|null      // Menu parent (null pour les menus racine)
    children: Collection   // Menus enfants
    section: string        // Section (HOME, INVESTISSEUR, INTRADAY)
    menuorder: int         // Ordre d'affichage
}
```

## Services

### MenuService

Responsable de la logique métier des menus :

- `getMenuBySection(string $section): array` - Récupère les menus parents d'une section
- `getMenuWithChildren(string $section): array` - Récupère les menus avec leurs enfants
- `isMenuActive(string $currentRoute, Menu $menu): bool` - Vérifie si un menu est actif
- `isChildActive(string $currentRoute, Menu $child): bool` - Vérifie si un enfant est actif
- `getActiveParentMenu(string $currentRoute, string $section): ?Menu` - Trouve le parent actif

## Extension Twig

### AppExtension

Fournit les fonctions Twig suivantes :

- `get_menu(section)` - Récupère les menus d'une section
- `get_menu_with_children(section)` - Récupère les menus avec enfants
- `is_active_menu(currentRoute, menu)` - Vérifie si un menu est actif
- `is_active_child(currentRoute, child)` - Vérifie si un enfant est actif
- `get_active_parent_menu(currentRoute, section)` - Trouve le parent actif

## Templates

### Template générique

`templates/partials/menu_generic.html.twig` - Template réutilisable pour tous les menus avec sous-menus.

**Variables attendues :**

- `section` : Section du menu (INVESTISSEUR, INTRADAY)
- `menu_class` : Classe CSS pour le conteneur
- `nav_class` : Classe CSS pour la navigation

### Templates spécifiques

- `menu_home_simple.html.twig` - Menu simple sans sous-menus
- `menu_investisseur.html.twig` - Utilise le template générique
- `menu_intraday.html.twig` - Utilise le template générique

## Utilisation

### Dans un template

```twig
{# Menu avec sous-menus #}
{% include 'partials/menu_generic.html.twig' with {
    section: 'INVESTISSEUR',
    menu_class: 'menu-investisseur',
    nav_class: 'main-nav main-nav-g'
} %}

{# Menu simple #}
{% include 'partials/menu_home_simple.html.twig' %}
```

### Dans un contrôleur

```php
// Récupérer les menus d'une section
$menus = $this->menuService->getMenuBySection('INVESTISSEUR');

// Vérifier si un menu est actif
$isActive = $this->menuService->isMenuActive($currentRoute, $menu);
```

## Avantages de cette architecture

1. **Centralisation** : Toute la logique des menus est dans le service
2. **Réutilisabilité** : Templates génériques pour éviter la duplication
3. **Maintenabilité** : Modifications centralisées dans la base de données
4. **Performance** : Requêtes optimisées avec Doctrine
5. **Flexibilité** : Facile d'ajouter de nouvelles sections

## Gestion des états actifs

Le système détecte automatiquement les menus actifs en :

1. Comparant la route actuelle avec la route du menu parent
2. Comparant la route actuelle avec les routes des enfants
3. Affichant le sous-menu uniquement quand un parent est actif

## Administration

Les menus sont gérés via EasyAdmin dans `MenuCrudController` :

- Création/modification des menus
- Gestion des relations parent/enfant
- Ordre d'affichage
- Sections

## Migration des données

Pour migrer des menus hardcodés vers la base de données :

1. Créer les entrées dans la table `menu`
2. Définir les relations parent/enfant
3. Assigner les sections et ordres
4. Supprimer le code hardcodé

## Exemple de structure en base

```sql
-- Menu parent
INSERT INTO menu (label, route, section, menuorder, parent_id)
VALUES ('Bibliothèque', 'investisseur_bibliotheque', 'INVESTISSEUR', 1, NULL);

-- Menus enfants
INSERT INTO menu (label, route, section, menuorder, parent_id)
VALUES ('Bulles Type 1', 'investisseur_bibliotheque_bulles_type_1', 'INVESTISSEUR', 1, 1);
```
