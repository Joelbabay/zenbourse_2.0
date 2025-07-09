# Checklist de migration zenbourse50.com → zenbourse.fr

## 📋 Préparation

### ✅ Sauvegarde
- [ ] Sauvegarde complète de la base de données (phpMyAdmin)
- [ ] Sauvegarde des fichiers via FTP/SFTP
- [ ] Sauvegarde des certificats SSL
- [ ] Export des configurations serveur

### ✅ Configuration O2switch
- [ ] Ajout du domaine zenbourse.fr dans le panneau
- [ ] Configuration DNS (si nécessaire)
- [ ] Création de la base de données pour zenbourse.fr
- [ ] Configuration SSL pour le nouveau domaine

## 🚀 Migration

### ✅ Fichiers de configuration
- [ ] Créer le fichier `.env.local` avec les nouvelles configurations
- [ ] Mettre à jour `DATABASE_URL` pour zenbourse.fr
- [ ] Mettre à jour `APP_URL` vers https://zenbourse.fr
- [ ] Générer une nouvelle `APP_SECRET`

### ✅ Base de données
- [ ] Importer la sauvegarde dans la nouvelle base
- [ ] Exécuter les migrations Symfony si nécessaire
- [ ] Vérifier l'intégrité des données

### ✅ Code source
- [ ] Exécuter le script de migration: `php scripts/migrate_domain.php`
- [ ] Vérifier que tous les liens pointent vers zenbourse.fr
- [ ] Mettre à jour les références dans les templates
- [ ] Nettoyer le cache Symfony

## 🔧 Post-migration

### ✅ Tests fonctionnels
- [ ] Test de connexion à l'application
- [ ] Test de l'authentification utilisateur
- [ ] Test des fonctionnalités d'administration
- [ ] Test des uploads de fichiers
- [ ] Test des emails (si configurés)
- [ ] Test des performances

### ✅ Configuration serveur
- [ ] Vérifier la configuration Apache/Nginx
- [ ] Vérifier les permissions des dossiers
- [ ] Configurer les redirections 301 (ancien → nouveau domaine)
- [ ] Mettre à jour les certificats SSL

### ✅ SEO et Analytics
- [ ] Mettre à jour Google Search Console
- [ ] Mettre à jour Google Analytics
- [ ] Configurer les redirections 301 pour le SEO
- [ ] Vérifier les sitemaps

## 🚨 Points d'attention

### ⚠️ Redirections
```apache
# .htaccess pour rediriger l'ancien domaine
RewriteEngine On
RewriteCond %{HTTP_HOST} ^zenbourse50\.com$ [NC]
RewriteRule ^(.*)$ https://zenbourse.fr/$1 [R=301,L]
```

### ⚠️ Configuration .env.local
```env
# Exemple de configuration pour zenbourse.fr
DATABASE_URL="mysql://[utilisateur]:[mot_de_passe]@localhost:3306/[nom_base_zenbourse_fr]?serverVersion=8.0.32&charset=utf8mb4"
APP_URL="https://zenbourse.fr"
APP_SECRET="[nouvelle_cle_secrete]"
```

### ⚠️ Commandes Symfony utiles
```bash
# Nettoyer le cache
php bin/console cache:clear

# Vérifier la configuration
php bin/console debug:config

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Vérifier les routes
php bin/console debug:router
```

## 📞 Support O2switch

En cas de problème, contacter O2switch avec:
- Numéro de client
- Domaine concerné
- Description précise du problème
- Logs d'erreur si disponibles

## 🔄 Rollback

En cas de problème majeur:
1. Restaurer la sauvegarde de la base de données
2. Restaurer les fichiers
3. Reconfigurer l'ancien domaine
4. Tester avant de relancer la migration 