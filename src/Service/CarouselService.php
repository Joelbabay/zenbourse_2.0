<?php

namespace App\Service;

use App\Entity\CarouselImage;
use App\Repository\CarouselImageRepository;
use Doctrine\ORM\EntityManagerInterface;

class CarouselService
{
    public function __construct(
        private CarouselImageRepository $carouselImageRepository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Récupère toutes les images actives du carrousel triées par position
     */
    public function getActiveImages(): array
    {
        return $this->carouselImageRepository->findActiveImages();
    }

    /**
     * Récupère la prochaine position disponible
     */
    public function getNextPosition(): int
    {
        return $this->carouselImageRepository->getNextPosition();
    }

    /**
     * Gère la position d'une image du carrousel
     * Si la position est déjà occupée, décale les autres images
     */
    public function handleImagePosition(CarouselImage $image, int $newPosition): void
    {
        $currentPosition = $image->getPosition();

        // Si la position n'a pas changé, ne rien faire
        if ($currentPosition === $newPosition) {
            return;
        }

        // Récupérer toutes les images sauf celle qu'on modifie
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('ci')
            ->from(CarouselImage::class, 'ci')
            ->where('ci.id != :id')
            ->setParameter('id', $image->getId());

        $otherImages = $qb->getQuery()->getResult();

        if ($currentPosition === null) {
            // Nouvelle image : insérer à la position demandée
            foreach ($otherImages as $otherImage) {
                if ($otherImage->getPosition() >= $newPosition) {
                    $otherImage->setPosition($otherImage->getPosition() + 1);
                }
            }
        } else {
            // Image existante : déplacer
            if ($newPosition > $currentPosition) {
                // Déplacer vers le bas
                foreach ($otherImages as $otherImage) {
                    if ($otherImage->getPosition() > $currentPosition && $otherImage->getPosition() <= $newPosition) {
                        $otherImage->setPosition($otherImage->getPosition() - 1);
                    }
                }
            } else {
                // Déplacer vers le haut
                foreach ($otherImages as $otherImage) {
                    if ($otherImage->getPosition() >= $newPosition && $otherImage->getPosition() < $currentPosition) {
                        $otherImage->setPosition($otherImage->getPosition() + 1);
                    }
                }
            }
        }

        $image->setPosition($newPosition);
        $this->entityManager->flush();
    }
}
