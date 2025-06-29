<?php

namespace App\Repository;

use App\Entity\StockExample;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StockExample>
 *
 * @method StockExample|null find($id, $lockMode = null, $lockVersion = null)
 * @method StockExample|null findOneBy(array $criteria, array $orderBy = null)
 * @method StockExample[]    findAll()
 * @method StockExample[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockExampleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockExample::class);
    }

    public function findByCategory(string $category): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.category = :category')
            ->andWhere('s.isActive = :active')
            ->setParameter('category', $category)
            ->setParameter('active', true)
            ->orderBy('s.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?StockExample
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.slug = :slug')
            ->andWhere('s.isActive = :active')
            ->setParameter('slug', $slug)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getCategories(): array
    {
        return $this->createQueryBuilder('s')
            ->select('DISTINCT s.category')
            ->andWhere('s.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('s.category', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * Récupère toutes les catégories distinctes disponibles
     */
    public function findDistinctCategories(): array
    {
        return $this->createQueryBuilder('s')
            ->select('DISTINCT s.category')
            ->orderBy('s.category', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }
}
