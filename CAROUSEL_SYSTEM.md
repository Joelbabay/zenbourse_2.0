# Système de Gestion du Carrousel

## Vue d'ensemble

Le système de gestion du carrousel permet d'administrer dynamiquement les images affichées dans le carrousel de la page d'accueil de Zenbourse 50. **Le carrousel ne s'affiche que sur la page d'accueil (slug: 'accueil')** pour une meilleure UX.

Cette fonctionnalité améliore l'UX en permettant de :

- **Upload d'images** avec drag & drop et sélection de fichiers
- **Bibliothèque d'images** pour réutiliser les images existantes
- Ajouter/supprimer des images du carrousel
- Réorganiser l'ordre d'affichage
- Activer/désactiver des images
- Gérer les textes alternatifs pour l'accessibilité
- **Affichage exclusif sur la page d'accueil**

## Architecture

### Entités

- **CarouselImage** : Entité principale pour stocker les informations des images
  - `title` : Titre descriptif (pour l'administration)
  - `imagePath` : Chemin vers l'image
  - `altText` : Texte alternatif pour l'accessibilité
  - `position` : Ordre d'affichage dans le carrousel
  - `isActive` : Statut actif/inactif
  - `createdAt` / `updatedAt` : Timestamps

### Services

- **CarouselService** : Service principal pour la logique métier
  - `getActiveImages()` : Récupère les images actives triées par position
  - `getNextPosition()` : Calcule la prochaine position disponible
  - `handleImagePosition()` : Gère les conflits de position avec décalage automatique

### Contrôleurs

- **CarouselImageCrudController** : Interface d'administration EasyAdmin

  - Gestion complète CRUD des images
  - Gestion automatique des positions
  - Templates personnalisés pour une meilleure UX

- **CarouselImageUploadController** : Gestion de l'upload d'images
  - Upload avec validation et renommage automatique
  - Bibliothèque d'images existantes
  - Suppression d'images de la bibliothèque

## Utilisation

### Interface d'Administration

1. Accédez à `/admin`
2. Cliquez sur "Images du carrousel" dans le menu
3. Utilisez les boutons "Ajouter une image" pour créer de nouvelles images
4. Modifiez les propriétés existantes avec "Modifier"

### Système d'Upload d'Images

#### Onglet "Upload d'image"

- **Drag & Drop** : Glissez-déposez vos images directement
- **Sélection de fichier** : Cliquez pour choisir une image
- **Validation automatique** : Types acceptés (JPG, PNG, GIF, WebP)
- **Limite de taille** : Maximum 5MB par image
- **Renommage automatique** : Évite les doublons avec un identifiant unique

#### Onglet "Bibliothèque d'images"

- **Images existantes** : Sélectionnez parmi les images déjà uploadées
- **Prévisualisation** : Voir les images avant sélection
- **Informations** : Taille et date de modification
- **Sélection simple** : Cliquez sur une image pour la sélectionner

### Champs du Formulaire

- **Titre** : Nom descriptif pour identifier l'image (ex: "Promotion spéciale")
- **Chemin de l'image** : Rempli automatiquement lors de l'upload ou de la sélection
- **Texte alternatif** : Description pour l'accessibilité (optionnel)
- **Position** : Ordre d'affichage (1 = première image)
- **Actif** : Cochez pour afficher l'image dans le carrousel

### Gestion des Positions

Le système gère automatiquement les conflits de position :

- Si vous entrez une position déjà occupée, les autres images sont décalées
- Les positions sont automatiquement assignées si laissées vides
- L'ordre est maintenu lors des modifications

### Commandes Disponibles

```bash
# Générer des images de test
php bin/console app:generate-carousel-images

# Tester le système
php bin/console app:test-carousel

# Tester l'affichage du carrousel
php bin/console app:test-carousel-display

# Tester le système d'upload
php bin/console app:test-carousel-upload

# Tester les images du carrousel
php bin/console app:test-carousel-images
```

## Intégration Frontend

Le carrousel est automatiquement intégré dans le template `templates/home/page.html.twig` et ne s'affiche que sur la page d'accueil :

```twig
{% if menu.slug == 'accueil' %}
    <div id="carouselExampleSlidesOnly" class="carousel slide mb-4" data-bs-ride="carousel" style="min-height:300px">
        <div class="carousel-inner">
            {% set carouselImages = carousel_service.getActiveImages() %}
            {% if carouselImages|length > 0 %}
                {% for image in carouselImages %}
                    <div class="carousel-item {% if loop.first %}active{% endif %}">
                        <img src="{{ asset(image.imagePath) }}"
                             class="d-block w-100"
                             alt="{{ image.altText|default(image.title) }}">
                    </div>
                {% endfor %}
            {% else %}
                {# Fallback aux images par défaut si aucune image n'est configurée #}
            {% endif %}
        </div>
    </div>
{% endif %}
```

### Pages d'Affichage

- **✅ Page d'accueil** (slug: 'accueil') : Le carrousel s'affiche
- **❌ Autres pages HOME** (méthodes, contact, etc.) : Le carrousel ne s'affiche pas
- **❌ Pages INVESTISSEUR et INTRADAY** : Le carrousel ne s'affiche pas

## Fonctionnalités UX

### Templates Personnalisés

- **carousel_image_list.html.twig** : Liste avec prévisualisation des images
- **carousel_image_upload_form.html.twig** : Formulaire d'ajout avec upload et bibliothèque
- **carousel_image_edit.html.twig** : Formulaire d'édition avec prévisualisation

### Améliorations UX

- **Interface avec onglets** : Upload et bibliothèque séparés
- **Drag & Drop** : Upload intuitif par glisser-déposer
- **Prévisualisation en temps réel** : Voir l'image avant validation
- **Bibliothèque d'images** : Réutilisation des images existantes
- **Validation automatique** : Types de fichiers et tailles
- **Renommage intelligent** : Évite les conflits de noms
- Aide contextuelle pour chaque champ
- Avertissements lors de conflits de position
- Suggestions de position automatique
- Interface intuitive avec icônes
- **Affichage exclusif sur la page d'accueil pour éviter la confusion**

## Migration et Installation

1. La migration `Version20250622041821.php` crée la table `carousel_image`
2. Le dossier `public/images/carousel/` est créé automatiquement
3. Exécutez `php bin/console app:generate-carousel-images` pour créer des données de test
4. Accédez à l'interface d'administration pour gérer les images
5. Vérifiez que la page d'accueil a le slug 'accueil'

## Maintenance

### Upload d'Images

1. **Via l'interface** : Utilisez l'onglet "Upload d'image" dans l'administration
2. **Types acceptés** : JPG, PNG, GIF, WebP
3. **Taille maximale** : 5MB par image
4. **Stockage** : `public/images/carousel/` avec renommage automatique

### Gestion de la Bibliothèque

- **Ajout** : Upload automatique lors de l'ajout d'une image
- **Sélection** : Utilisez l'onglet "Bibliothèque d'images"
- **Suppression** : Via l'API `/admin/carousel/delete-image`

### Sauvegarde

Les images du carrousel sont stockées en base de données, mais les fichiers images doivent être sauvegardés séparément dans `public/images/carousel/`.

### Performance

Le système utilise des requêtes optimisées pour récupérer uniquement les images actives, triées par position.

### Tests

Utilisez les commandes de test pour vérifier :

- `php bin/console app:test-carousel-display` : Affichage du carrousel
- `php bin/console app:test-carousel-upload` : Système d'upload
- `php bin/console app:test-carousel-images` : Validation des images
