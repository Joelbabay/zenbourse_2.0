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

            // Vérifier et désactiver l'accès temporaire si expiré
            $this->checkAndDisableExpiredTemporaryAccess($user);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    private function checkAndDisableExpiredTemporaryAccess(\App\Entity\User $user): void
    {
        if ($user->getHasTemporaryInvestorAccess() && $user->getTemporaryInvestorAccessStart()) {
            $now = new \DateTime();
            $startDate = $user->getTemporaryInvestorAccessStart();

            // S'assurer qu'on a un objet DateTime
            $start = $startDate instanceof \DateTime ? clone $startDate : new \DateTime($startDate->format('Y-m-d H:i:s'));

            // Cloner pour ne pas modifier l'original
            $endDate = clone $start;
            $endDate->add(new \DateInterval('P10D'));

            if ($now > $endDate) {
                // L'accès a expiré, on le désactive
                $user->setHasTemporaryInvestorAccess(false);
            }
        }
    }
}
