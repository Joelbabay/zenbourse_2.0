<?php

namespace App\DataFixtures;

use App\Entity\Menu;
use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Liste des rôles à ajouter
        $roles = [
            'ROLE_CLIENT',
            'ROLE_INVITE',
            'ROLE_PROSPECT',
            'ROLE_ADMIN'
        ];

        foreach ($roles as $roleName) {
            // Créer un nouveau rôle
            $role = new Role();
            $role->setName($roleName);

            // Persister l'entité
            $manager->persist($role);
        }

        // Sauvegarder dans la base de données
        $manager->flush();
    }
}
