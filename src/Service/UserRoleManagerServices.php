<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class UserRoleManagerService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private int $temporaryAccessDuration = 10
    ) {}

    public function activateInvestorAccess(User $user): void
    {
        if (!$user->getInvestorAccessDate()) {
            $user->setInvestorAccessDate(new \DateTime());
        }

        $roles = $user->getRoles();
        if (!in_array('ROLE_INVESTISSEUR', $roles)) {
            $roles[] = 'ROLE_INVESTISSEUR';
            $user->setRoles(array_unique($roles));
        }

        $user->setInterestedInInvestorMethod(false);

        $this->logger->info('Investor access activated', [
            'user_id' => $user->getId(),
            'email' => $user->getEmail()
        ]);
    }

    public function deactivateInvestorAccess(User $user): void
    {
        $user->setIsIntraday(false);
        $user->setInvestorAccessDate(null);
        $user->setIntradayAccessDate(null);

        $roles = array_diff($user->getRoles(), ['ROLE_INVESTISSEUR', 'ROLE_INTRADAY']);
        $user->setRoles(array_values($roles));

        $this->logger->info('Investor access deactivated', [
            'user_id' => $user->getId(),
            'email' => $user->getEmail()
        ]);
    }

    public function activateIntradayAccess(User $user): void
    {
        if (!$user->isInvestisseur()) {
            throw new \LogicException('L\'utilisateur doit être investisseur pour avoir l\'accès intraday');
        }

        if (!$user->getIntradayAccessDate()) {
            $user->setIntradayAccessDate(new \DateTime());
        }

        $roles = $user->getRoles();
        if (!in_array('ROLE_INTRADAY', $roles)) {
            $roles[] = 'ROLE_INTRADAY';
            $user->setRoles(array_unique($roles));
        }

        $this->logger->info('Intraday access activated', [
            'user_id' => $user->getId(),
            'email' => $user->getEmail()
        ]);
    }

    public function deactivateIntradayAccess(User $user): void
    {
        $user->setIntradayAccessDate(null);

        $roles = array_diff($user->getRoles(), ['ROLE_INTRADAY']);
        $user->setRoles(array_values($roles));

        $this->logger->info('Intraday access deactivated', [
            'user_id' => $user->getId(),
            'email' => $user->getEmail()
        ]);
    }

    public function activateTemporaryAccess(User $user): void
    {
        if (!$user->getTemporaryInvestorAccessStart()) {
            $user->setTemporaryInvestorAccessStart(new \DateTime());
        }
        $user->setHasTemporaryInvestorAccess(true);

        $this->logger->info('Temporary access activated', [
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'start_date' => $user->getTemporaryInvestorAccessStart()->format('Y-m-d H:i:s'),
            'duration_days' => $this->temporaryAccessDuration
        ]);
    }

    public function deactivateTemporaryAccess(User $user): void
    {
        $user->setHasTemporaryInvestorAccess(false);
        $user->setTemporaryInvestorAccessStart(null);

        $this->logger->info('Temporary access deactivated', [
            'user_id' => $user->getId(),
            'email' => $user->getEmail()
        ]);
    }

    public function isTemporaryAccessExpired(User $user): bool
    {
        if (!$user->getHasTemporaryInvestorAccess() || !$user->getTemporaryInvestorAccessStart()) {
            return false;
        }

        $startDate = $user->getTemporaryInvestorAccessStart();
        $expirationDate = (clone $startDate)->add(
            new \DateInterval('P' . $this->temporaryAccessDuration . 'D')
        );

        return new \DateTime() > $expirationDate;
    }

    public function cleanExpiredTemporaryAccess(): int
    {
        $users = $this->entityManager->getRepository(User::class)
            ->findBy(['hasTemporaryInvestorAccess' => true]);

        $count = 0;
        foreach ($users as $user) {
            if ($this->isTemporaryAccessExpired($user)) {
                $this->deactivateTemporaryAccess($user);
                $count++;
            }
        }

        if ($count > 0) {
            $this->entityManager->flush();
            $this->logger->info('Expired temporary accesses cleaned', ['count' => $count]);
        }

        return $count;
    }
}
