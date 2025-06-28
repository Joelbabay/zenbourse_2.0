<?php

namespace App\Repository;

use App\Entity\CandlestickPattern;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CandlestickPattern>
 *
 * @method CandlestickPattern|null find($id, $lockMode = null, $lockVersion = null)
 * @method CandlestickPattern|null findOneBy(array $criteria, array $orderBy = null)
 * @method CandlestickPattern[]    findAll()
 * @method CandlestickPattern[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CandlestickPatternRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CandlestickPattern::class);
    }

    public function findBySlug(string $slug): ?CandlestickPattern
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.slug = :slug')
            ->andWhere('c.isActive = :active')
            ->setParameter('slug', $slug)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
