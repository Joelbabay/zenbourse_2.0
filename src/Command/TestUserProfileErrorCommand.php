<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:test-user-profile-error',
    description: 'Test du système de gestion des erreurs dans les formulaires utilisateur'
)]
class TestUserProfileErrorCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private ValidatorInterface $validator
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test du système de gestion des erreurs dans les formulaires utilisateur');

        // Récupérer un utilisateur de test
        $user = $this->userRepository->findOneBy([]);

        if (!$user) {
            $io->error('Aucun utilisateur trouvé dans la base de données');
            return Command::FAILURE;
        }

        $io->section('Test de validation des entités');

        // Test de validation directe de l'entité avec des données invalides
        $testUser = clone $user;
        $testUser->setFirstname(''); // Prénom vide
        $testUser->setEmail('invalid-email'); // Email invalide

        $violations = $this->validator->validate($testUser);

        if (count($violations) > 0) {
            $io->warning('✅ Violations de validation détectées :');
            foreach ($violations as $violation) {
                $io->text("- {$violation->getPropertyPath()}: {$violation->getMessage()}");
            }
        } else {
            $io->error('❌ Aucune violation détectée alors qu\'il devrait y en avoir');
        }

        $io->section('Test de validation avec des données valides');

        $testUser2 = clone $user;
        $testUser2->setFirstname('Test');
        $testUser2->setLastname('User');
        $testUser2->setEmail('test@example.com');

        $violations2 = $this->validator->validate($testUser2);

        if (count($violations2) === 0) {
            $io->success('✅ Aucune violation avec des données valides');
        } else {
            $io->error('❌ Violations détectées avec des données valides :');
            foreach ($violations2 as $violation) {
                $io->text("- {$violation->getPropertyPath()}: {$violation->getMessage()}");
            }
        }

        $io->section('Test des contraintes de validation');

        $constraints = [
            'Prénom vide' => ['firstname' => ''],
            'Prénom trop court' => ['firstname' => 'A'],
            'Nom vide' => ['lastname' => ''],
            'Nom trop court' => ['lastname' => 'B'],
            'Email invalide' => ['email' => 'invalid-email'],
            'Email vide' => ['email' => ''],
        ];

        foreach ($constraints as $description => $data) {
            $testUser3 = clone $user;
            foreach ($data as $property => $value) {
                $setter = 'set' . ucfirst($property);
                if (method_exists($testUser3, $setter)) {
                    $testUser3->$setter($value);
                }
            }

            $violations3 = $this->validator->validate($testUser3);
            $hasErrors = count($violations3) > 0;

            if ($hasErrors) {
                $io->text("✅ {$description}: Erreurs détectées");
            } else {
                $io->text("❌ {$description}: Aucune erreur détectée");
            }
        }

        return Command::SUCCESS;
    }
}
