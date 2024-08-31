<?php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

final class UserLoginListener
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[AsEventListener(event: 'security.interactive_login')] //  <- attribut
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof \App\Entity\User) {
            $user->setLastConnexion(new \DateTime());
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }
}
