<?php
// src/EventSubscriber/LoginCounterSubscriber.php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginCounterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        // Incrémenter le compteur global
        $user->incrementLoginCount();

        // Incrémenter le compteur Investisseur si l'accès est actif
        if ($user->isInvestisseur()) {
            $user->incrementInvestorLoginCount();
        }

        // Incrémenter le compteur Intraday si l'accès est actif
        if ($user->isIntraday()) {
            $user->incrementIntradayLoginCount();
        }

        // Mettre à jour la dernière connexion
        $user->setLastConnexion(new \DateTime());

        // Sauvegarder
        $this->entityManager->flush();
    }
}
