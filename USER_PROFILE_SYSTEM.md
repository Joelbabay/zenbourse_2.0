# Système de Gestion des Informations Utilisateur

## Vue d'ensemble

Le système de gestion des informations utilisateur permet aux utilisateurs connectés de modifier leurs informations personnelles et de changer leur mot de passe directement depuis leur espace personnel.

## Fonctionnalités

### 1. Modification des Informations Personnelles

- **Prénom** (obligatoire, 2-50 caractères)
- **Nom** (obligatoire, 2-50 caractères)
- **Email** (obligatoire, validation email, unicité)
- **Téléphone** (optionnel, max 20 caractères)
- **Ville** (optionnel, max 100 caractères)
- **Code postal** (optionnel, max 10 caractères)
- **Pays** (optionnel, max 100 caractères)

### 2. Changement de Mot de Passe

- Vérification du mot de passe actuel
- Nouveau mot de passe avec validation
- Hachage sécurisé du nouveau mot de passe

### 3. Interface Utilisateur

- **Modal pour les informations personnelles** : Interface moderne avec formulaire en modal
- **Modal pour le mot de passe** : Séparation claire des fonctionnalités
- **Affichage des informations** : Vue d'ensemble des données actuelles
- **Gestion des erreurs** : Affichage automatique des modales en cas d'erreur

## Architecture Technique

### Entités

- **User** : Entité principale avec tous les champs nécessaires
  - `firstname`, `lastname` : Informations de base
  - `email` : Identifiant unique avec validation
  - `phone`, `city`, `postalCode`, `country` : Informations de contact

### Formulaires

- **UserProfileType** : Formulaire pour les informations personnelles

  - Validation côté serveur
  - Messages d'erreur personnalisés
  - Contraintes de longueur et format

- **ChangeLocalPasswordType** : Formulaire pour le changement de mot de passe
  - Vérification du mot de passe actuel
  - Validation du nouveau mot de passe

### Contrôleur

- **UserController** : Gestion centralisée
  - Route : `/mon-compte/profile`
  - Gestion des deux formulaires
  - Validation de l'unicité de l'email
  - Messages flash pour le feedback utilisateur

### Templates

- **account.html.twig** : Interface utilisateur complète
  - Affichage des informations actuelles
  - Modales pour les formulaires
  - Gestion automatique des erreurs
  - Design responsive avec Bootstrap

## Validation et Sécurité

### Validation des Données

```php
// Exemple de contraintes dans UserProfileType
new Assert\NotBlank(['message' => 'Le prénom est obligatoire'])
new Assert\Length(['min' => 2, 'max' => 50])
new Assert\Email(['message' => 'L\'email n\'est pas valide'])
```

### Vérification de l'Unicité Email

```php
// Dans UserController
if ($newEmail !== $user->getEmail()) {
    $existingUser = $userRepository->findOneBy(['email' => $newEmail]);
    if ($existingUser && $existingUser->getId() !== $user->getId()) {
        // Erreur : email déjà utilisé
    }
}
```

### Sécurité du Mot de Passe

- Hachage avec `UserPasswordHasherInterface`
- Vérification du mot de passe actuel
- Validation du nouveau mot de passe

## Utilisation

### Pour l'Utilisateur

1. Se connecter à son compte
2. Aller sur `/mon-compte/profile`
3. Cliquer sur "Modifier mes informations" pour ouvrir la modal
4. Remplir le formulaire et valider
5. Cliquer sur "Changer mon mot de passe" pour la modal de mot de passe

### Pour le Développeur

```bash
# Tester le système
php bin/console app:test-user-profile

# Vérifier les routes
php bin/console debug:router | grep user
```

## Gestion des Erreurs

### Types d'Erreurs Gérées

1. **Validation des champs** : Messages personnalisés pour chaque contrainte
2. **Email déjà utilisé** : Vérification de l'unicité
3. **Mot de passe incorrect** : Vérification lors du changement
4. **Erreurs de formulaire** : Affichage automatique des modales

### Feedback Utilisateur

- **Messages flash** : Succès et erreurs
- **Affichage automatique** : Modales ouvertes en cas d'erreur
- **Validation en temps réel** : Erreurs affichées dans les formulaires

## Extensibilité

### Ajout de Nouveaux Champs

1. Ajouter le champ dans l'entité `User`
2. Créer une migration si nécessaire
3. Ajouter le champ dans `UserProfileType`
4. Mettre à jour le template `account.html.twig`

### Personnalisation de l'Interface

- Les modales utilisent Bootstrap pour la cohérence
- Les icônes FontAwesome améliorent l'UX
- Le design est responsive et accessible

## Tests

### Commande de Test

```bash
php bin/console app:test-user-profile
```

Cette commande teste :

- Récupération des informations utilisateur
- Validation du formulaire
- Mise à jour des données
- Restauration des données originales

## Maintenance

### Points d'Attention

1. **Sauvegarde** : Toujours sauvegarder avant les modifications
2. **Validation** : Tester les contraintes de validation
3. **Sécurité** : Vérifier les permissions d'accès
4. **Performance** : Optimiser les requêtes de base de données

### Évolutions Possibles

- Ajout de champs personnalisés
- Intégration avec des services externes
- Historique des modifications
- Export des données utilisateur
