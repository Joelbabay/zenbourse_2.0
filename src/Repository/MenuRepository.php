<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @extends ServiceEntityRepository<Menu>
 *
 * @method Menu|null find($id, $lockMode = null, $lockVersion = null)
 * @method Menu|null findOneBy(array $criteria, array $orderBy = null)
 * @method Menu[]    findAll()
 * @method Menu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuRepository extends ServiceEntityRepository
{
    private SluggerInterface $slugger;
    public function __construct(ManagerRegistry $registry, SluggerInterface $slugger)
    {
        parent::__construct($registry, Menu::class);
        $this->slugger = $slugger;
    }

    /**
     * Trouve le voisin (précédent ou suivant) d'un menu pour le réordonnancement.
     * La recherche se fait au sein de la même section et du même niveau hiérarchique (même parent).
     */
    public function findNeighbor(Menu $menu, string $direction): ?Menu
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.section = :section')
            ->setParameter('section', $menu->getSection());

        // Gère la contrainte du parent :
        // Si le menu a un parent, le voisin doit avoir le même parent.
        // Si le menu n'a pas de parent, le voisin ne doit pas en avoir non plus.
        if ($menu->getParent()) {
            $qb->andWhere('m.parent = :parent')
                ->setParameter('parent', $menu->getParent());
        } else {
            $qb->andWhere('m.parent IS NULL');
        }

        // Adapte la condition et le tri en fonction de la direction
        if ($direction === 'up') {
            $qb->andWhere('m.menuorder < :menuorder')
                ->setParameter('menuorder', $menu->getMenuorder())
                ->orderBy('m.menuorder', 'DESC');
        } else { // 'down'
            $qb->andWhere('m.menuorder > :menuorder')
                ->setParameter('menuorder', $menu->getMenuorder())
                ->orderBy('m.menuorder', 'ASC');
        }

        return $qb->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupère les menus d'une section avec leurs enfants
     */
    public function findBySectionWithChildren(string $section): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.children', 'c')
            ->addSelect('c')
            ->where('m.section = :section')
            ->andWhere('m.parent IS NULL')
            ->andWhere('m.isActive = :isActive')
            ->setParameter('section', $section)
            ->setParameter('isActive', true)
            ->orderBy('m.menuorder', 'ASC')
            ->addOrderBy('c.menuorder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère le premier menu actif d'une section
     */
    public function findFirstActiveBySection(string $section): ?Menu
    {
        return $this->createQueryBuilder('m')
            ->where('m.isActive = :isActive')
            ->andWhere('m.section = :section')
            ->andWhere('m.parent IS NULL')
            ->setParameter('isActive', true)
            ->setParameter('section', $section)
            ->orderBy('m.menuorder', 'ASC') // Le tri est crucial
            ->setMaxResults(1) // Ne retourne qu'un seul résultat
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Génère un slug unique pour un menu.
     */
    public function generateUniqueSlug(string $label, ?int $excludeId = null): string
    {
        $baseSlug = $this->slugger->slug($label)->lower();
        $slug = $baseSlug;
        $counter = 1;

        // Boucle pour s'assurer que le slug est unique
        while ($this->slugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Vérifie si un slug existe déjà en base de données.
     */
    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.slug = :slug')
            ->setParameter('slug', $slug);

        if ($excludeId !== null) {
            $qb->andWhere('m.id != :id')
                ->setParameter('id', $excludeId);
        }

        return $qb->getQuery()->getOneOrNullResult() !== null;
    }
}
