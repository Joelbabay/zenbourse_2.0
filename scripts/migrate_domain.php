<?php

/**
 * Script de migration de domaine zenbourse50.com vers zenbourse.fr
 * Usage: php scripts/migrate_domain.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

class DomainMigrator
{
    private string $oldDomain = 'zenbourse50.com';
    private string $newDomain = 'zenbourse.fr';
    private array $filesToUpdate = [];

    public function __construct()
    {
        // Charger les variables d'environnement
        if (file_exists(__DIR__ . '/../.env.local')) {
            (new Dotenv())->loadEnv(__DIR__ . '/../.env.local');
        }
    }

    public function migrate(): void
    {
        echo "🚀 Début de la migration de {$this->oldDomain} vers {$this->newDomain}\n\n";

        $this->updateDatabaseConfiguration();
        $this->updateFilePaths();
        $this->clearCache();
        $this->updateDatabase();

        echo "✅ Migration terminée avec succès!\n";
        echo "📝 N'oublie pas de:\n";
        echo "   - Vérifier la configuration DNS\n";
        echo "   - Tester toutes les fonctionnalités\n";
        echo "   - Mettre à jour les certificats SSL\n";
    }

    private function updateDatabaseConfiguration(): void
    {
        echo "📊 Mise à jour de la configuration de base de données...\n";
        
        // Vérifier si .env.local existe
        $envFile = __DIR__ . '/../.env.local';
        if (!file_exists($envFile)) {
            echo "⚠️  Fichier .env.local non trouvé. Crée-le manuellement.\n";
            return;
        }

        $content = file_get_contents($envFile);
        
        // Mettre à jour l'URL de l'application
        $content = preg_replace(
            '/APP_URL="[^"]*"/',
            'APP_URL="https://' . $this->newDomain . '"',
            $content
        );

        file_put_contents($envFile, $content);
        echo "✅ Configuration de base de données mise à jour\n";
    }

    private function updateFilePaths(): void
    {
        echo "📁 Mise à jour des chemins de fichiers...\n";
        
        // Chercher les références à l'ancien domaine dans les templates
        $this->searchAndReplaceInDirectory(__DIR__ . '/../templates', $this->oldDomain, $this->newDomain);
        $this->searchAndReplaceInDirectory(__DIR__ . '/../src', $this->oldDomain, $this->newDomain);
        
        echo "✅ Chemins de fichiers mis à jour\n";
    }

    private function searchAndReplaceInDirectory(string $directory, string $old, string $new): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['php', 'twig', 'yaml', 'yml'])) {
                $content = file_get_contents($file->getPathname());
                if (strpos($content, $old) !== false) {
                    $newContent = str_replace($old, $new, $content);
                    file_put_contents($file->getPathname(), $newContent);
                    echo "   📝 Mis à jour: " . $file->getPathname() . "\n";
                }
            }
        }
    }

    private function clearCache(): void
    {
        echo "🧹 Nettoyage du cache...\n";
        
        $cacheDir = __DIR__ . '/../var/cache';
        if (is_dir($cacheDir)) {
            $this->removeDirectory($cacheDir);
            echo "✅ Cache nettoyé\n";
        }
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    private function updateDatabase(): void
    {
        echo "🗄️  Mise à jour de la base de données...\n";
        
        // Exécuter les migrations si nécessaire
        $output = shell_exec('cd ' . __DIR__ . '/.. && php bin/console doctrine:migrations:migrate --no-interaction 2>&1');
        echo $output;
        
        echo "✅ Base de données mise à jour\n";
    }
}

// Exécution du script
if (php_sapi_name() === 'cli') {
    $migrator = new DomainMigrator();
    $migrator->migrate();
} else {
    echo "Ce script doit être exécuté en ligne de commande.\n";
} 