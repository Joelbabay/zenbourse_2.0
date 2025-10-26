<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class UserLoginListener
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[AsEventListener(event: LoginSuccessEvent::class)]
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $user->setLastConnexion(new \DateTime());
        $this->checkAndDisableExpiredTemporaryAccess($user);

        $this->entityManager->flush();
    }

    private function checkAndDisableExpiredTemporaryAccess(User $user): void
    {
        if (!$user->getHasTemporaryInvestorAccess() || !$user->getTemporaryInvestorAccessStart()) {
            return;
        }

        $now = new \DateTime();
        $startDate = $user->getTemporaryInvestorAccessStart();

        $start = $startDate instanceof \DateTime
            ? clone $startDate
            : \DateTime::createFromInterface($startDate);

        $endDate = (clone $start)->add(new \DateInterval('P10D'));

        if ($now > $endDate) {
            $user->setHasTemporaryInvestorAccess(false);
        }
    }
}
