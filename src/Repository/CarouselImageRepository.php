<?php

namespace App\Repository;

use App\Entity\CarouselImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CarouselImage>
 *
 * @method CarouselImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method CarouselImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method CarouselImage[]    findAll()
 * @method CarouselImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarouselImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CarouselImage::class);
    }

    /**
     * Récupère toutes les images actives du carrousel triées par position
     */
    public function findActiveImages(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('c.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère la prochaine position disponible
     */
    public function getNextPosition(): int
    {
        $result = $this->createQueryBuilder('c')
            ->select('MAX(c.position) as maxPosition')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? $result + 1 : 1;
    }

    //    /**
    //     * @return CarouselImage[] Returns an array of CarouselImage objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?CarouselImage
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
