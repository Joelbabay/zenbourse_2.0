<?php

namespace App\Repository;

use App\Entity\Visit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Visit>
 */
class VisitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visit::class);
    }

    /**
     * Compte les visites uniques par session pour une période donnée
     */
    public function countUniqueSessions(\DateTimeInterface $startDate = null, \DateTimeInterface $endDate = null): int
    {
        $qb = $this->createQueryBuilder('v')
            ->select('COUNT(DISTINCT v.sessionId)')
            ->where('v.isBot = false')
            ->andWhere('v.isAdmin = false');

        if ($startDate) {
            $qb->andWhere('v.visitedAt >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $qb->andWhere('v.visitedAt <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Compte les visites uniques par session aujourd'hui
     */
    public function countUniqueSessionsToday(): int
    {
        $today = new \DateTime('today');
        $tomorrow = (clone $today)->modify('+1 day');

        return $this->countUniqueSessions($today, $tomorrow);
    }

    /**
     * Compte les visites uniques par session cette semaine
     */
    public function countUniqueSessionsThisWeek(): int
    {
        $startOfWeek = new \DateTime('monday this week');
        $endOfWeek = (clone $startOfWeek)->modify('+7 days');

        return $this->countUniqueSessions($startOfWeek, $endOfWeek);
    }

    /**
     * Compte les visites uniques par session ce mois
     */
    public function countUniqueSessionsThisMonth(): int
    {
        $startOfMonth = new \DateTime('first day of this month');
        $endOfMonth = (clone $startOfMonth)->modify('+1 month');

        return $this->countUniqueSessions($startOfMonth, $endOfMonth);
    }

    /**
     * Récupère les statistiques de visites pour le dashboard
     */
    public function getVisitStats(): array
    {
        $today = new \DateTime('today');
        $tomorrow = (clone $today)->modify('+1 day');
        $startOfWeek = new \DateTime('monday this week');
        $endOfWeek = (clone $startOfWeek)->modify('+7 days');
        $startOfMonth = new \DateTime('first day of this month');
        $endOfMonth = (clone $startOfMonth)->modify('+1 month');

        return [
            'today' => $this->countUniqueSessions($today, $tomorrow),
            'this_week' => $this->countUniqueSessions($startOfWeek, $endOfWeek),
            'this_month' => $this->countUniqueSessions($startOfMonth, $endOfMonth),
            'total' => $this->countUniqueSessions(),
        ];
    }
}
