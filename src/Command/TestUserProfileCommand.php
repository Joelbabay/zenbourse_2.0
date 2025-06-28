<?php

namespace App\Command;

use App\Entity\User;
use App\Form\UserProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:test-user-profile',
    description: 'Test du système de gestion des informations utilisateur'
)]
class TestUserProfileCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private FormFactoryInterface $formFactory,
        private ValidatorInterface $validator
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test du système de gestion des informations utilisateur');

        // Récupérer un utilisateur de test
        $user = $this->userRepository->findOneBy([]);

        if (!$user) {
            $io->error('Aucun utilisateur trouvé dans la base de données');
            return Command::FAILURE;
        }

        $io->section('Informations actuelles de l\'utilisateur');
        $io->table(
            ['Champ', 'Valeur'],
            [
                ['ID', $user->getId()],
                ['Email', $user->getEmail()],
                ['Prénom', $user->getFirstname() ?? 'Non renseigné'],
                ['Nom', $user->getLastname() ?? 'Non renseigné'],
                ['Téléphone', $user->getPhone() ?? 'Non renseigné'],
                ['Ville', $user->getCity() ?? 'Non renseigné'],
                ['Code postal', $user->getPostalCode() ?? 'Non renseigné'],
                ['Pays', $user->getCountry() ?? 'Non renseigné'],
            ]
        );

        // Test du formulaire
        $io->section('Test du formulaire UserProfileType');

        $form = $this->formFactory->create(UserProfileType::class, $user);

        $io->text('Champs du formulaire :');
        foreach ($form->all() as $name => $field) {
            $io->text("- {$name}: " . get_class($field->getConfig()->getType()->getInnerType()));
        }

        // Test de validation
        $io->section('Test de validation');

        $violations = $this->validator->validate($user);

        if (count($violations) > 0) {
            $io->warning('Violations de validation trouvées :');
            foreach ($violations as $violation) {
                $io->text("- {$violation->getPropertyPath()}: {$violation->getMessage()}");
            }
        } else {
            $io->success('Aucune violation de validation');
        }

        // Test de mise à jour
        $io->section('Test de mise à jour des informations');

        $oldEmail = $user->getEmail();
        $user->setFirstname('Test Prénom');
        $user->setLastname('Test Nom');
        $user->setPhone('0123456789');
        $user->setCity('Paris');
        $user->setPostalCode('75001');
        $user->setCountry('France');

        $this->entityManager->flush();

        $io->success('Informations mises à jour avec succès');

        // Restaurer les données originales
        $user->setEmail($oldEmail);
        $this->entityManager->flush();

        $io->info('Données originales restaurées');

        return Command::SUCCESS;
    }
}
