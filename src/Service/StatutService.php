<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class StatutService
{
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        $this->entityManager = $entityManager;
    }

    public function statutService(string $email, $data): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($user) {
            if ($user->getCreatedAt() == null) {
                $user->setCreatedAt(new \DateTime());
            }
            if (in_array($user->getStatut(), ['CLIENT', 'INVITE'])) {
                return;
            }
        } else {
            $user = new User();
            $user->setCivility($data->getCivility());
            $user->setEmail($data->getEmail());
            $user->setFirstname($data->getFirstname());
            $user->setLastname($data->getLastname());
            $user->setPassword($this->passwordHasher->hashPassword($user, 'zenbourse'));
            $user->setStatut('PROSPECT');
        }

        $user->setIsDownloadRequestSubmitted(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
