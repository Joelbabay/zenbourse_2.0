<?php
// src/Twig/SiteConfigExtension.php

namespace App\Twig;

use App\Repository\SiteConfigRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SiteConfigExtension extends AbstractExtension
{
    public function __construct(
        private SiteConfigRepository $configRepository
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('config', [$this, 'getConfig']),
        ];
    }

    public function getConfig(string $key): ?string
    {
        return $this->configRepository->getValue($key);
    }
}
