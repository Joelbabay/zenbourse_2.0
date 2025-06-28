<?php

namespace App\Service;

use App\Entity\CandlestickPattern;
use App\Repository\CandlestickPatternRepository;
use Doctrine\ORM\EntityManagerInterface;

class CandlestickPatternService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CandlestickPatternRepository $candlestickPatternRepository
    ) {}

    public function getAllPatterns(): array
    {
        return $this->candlestickPatternRepository->findAllActive();
    }

    public function getPatternBySlug(string $slug): ?CandlestickPattern
    {
        return $this->candlestickPatternRepository->findBySlug($slug);
    }

    public function formatPatternForTemplate(CandlestickPattern $pattern): array
    {
        return [
            'structure' => $pattern->getStructure(),
            'title' => $pattern->getTitle(),
            'image_h' => $pattern->getImageH(),
            'image_b' => $pattern->getImageB(),
            'image_name_h' => $pattern->getImageNameH(),
            'image_name_b' => $pattern->getImageNameB(),
            'description' => $pattern->getDescription(),
            'content_h' => $pattern->getContentH(),
            'content_b' => $pattern->getContentB(),
        ];
    }

    public function formatPatternsForTemplate(array $patterns): array
    {
        $formatted = [];
        foreach ($patterns as $pattern) {
            $formatted[$pattern->getSlug()] = $this->formatPatternForTemplate($pattern);
        }
        return $formatted;
    }
}
