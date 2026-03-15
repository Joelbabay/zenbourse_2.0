<?php
// src/Repository/SiteConfigRepository.php

namespace App\Repository;

use App\Entity\SiteConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SiteConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiteConfig::class);
    }

    public function getValue(string $key): ?string
    {
        $config = $this->findOneBy(['configKey' => $key]);
        return $config?->getValue();
    }
}
