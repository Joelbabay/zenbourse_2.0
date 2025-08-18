<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Menu>
 */
class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    /**
     * Trouve un menu par son slug
     */
    public function findBySlug(string $slug): ?Menu
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * Trouve un menu par sa route
     */
    public function findByRoute(string $route): ?Menu
    {
        return $this->findOneBy(['route' => $route]);
    }

    /**
     * Récupère tous les menus d'une section avec leurs enfants
     */
    public function findBySectionWithChildren(string $section): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.children', 'c')
            ->where('m.section = :section')
            ->andWhere('m.parent IS NULL')
            ->andWhere('m.isActive = :isActive')
            ->setParameter('section', $section)
            ->setParameter('isActive', true)
            ->orderBy('m.menuorder', 'ASC')
            ->addOrderBy('c.menuorder', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve le premier menu actif pour une section donnée, trié par ordre.
     */
    public function findFirstActiveBySection(string $section): ?Menu
    {
        return $this->createQueryBuilder('m')
            ->where('m.section = :section')
            ->andWhere('m.isActive = :isActive')
            ->andWhere('m.parent IS NULL') // On ne cible que les menus principaux
            ->setParameter('section', $section)
            ->setParameter('isActive', true)
            ->orderBy('m.menuorder', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Vérifie si un slug existe déjà
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.slug = :slug')
            ->setParameter('slug', $slug);

        if ($excludeId) {
            $qb->andWhere('m.id != :id')
                ->setParameter('id', $excludeId);
        }

        return $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * Génère un slug unique basé sur un label
     */
    public function generateUniqueSlug(string $label, ?int $excludeId = null): string
    {
        $baseSlug = $this->slugify($label);
        $slug = $baseSlug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Convertit un texte en slug
     */
    private function slugify(string $text): string
    {
        // Convertit en minuscules
        $text = strtolower($text);

        // Remplace les caractères accentués
        $text = str_replace(
            ['à', 'á', 'â', 'ã', 'ä', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ'],
            ['a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y'],
            $text
        );

        // Remplace les caractères spéciaux par des tirets
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);

        // Supprime les tirets multiples
        $text = preg_replace('/-+/', '-', $text);

        // Supprime les tirets en début et fin
        return trim($text, '-');
    }

    /**
     * Trouve le menu voisin (précédent ou suivant) pour un menu donné.
     * C'est la logique clé pour les actions "Monter" et "Descendre".
     */
    public function findNeighbor(Menu $menu, string $direction): ?Menu
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.section = :section')
            ->setParameter('section', $menu->getSection());

        // Gère la contrainte du parent : on ne déplace que parmi les frères
        if ($menu->getParent()) {
            $qb->andWhere('m.parent = :parent')
                ->setParameter('parent', $menu->getParent());
        } else {
            $qb->andWhere('m.parent IS NULL');
        }

        // Logique pour trouver le voisin
        if ($direction === 'up') {
            $qb->andWhere('m.menuorder < :currentOrder')
                ->setParameter('currentOrder', $menu->getMenuorder())
                ->orderBy('m.menuorder', 'DESC');
        } elseif ($direction === 'down') {
            $qb->andWhere('m.menuorder > :currentOrder')
                ->setParameter('currentOrder', $menu->getMenuorder())
                ->orderBy('m.menuorder', 'ASC');
        } else {
            return null; // Direction non valide
        }

        return $qb->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Menu[] Returns an array of Menu objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Menu
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
