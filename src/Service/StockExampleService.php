<?php

namespace App\Service;

use App\Entity\StockExample;
use App\Repository\StockExampleRepository;
use Doctrine\ORM\EntityManagerInterface;

class StockExampleService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StockExampleRepository $stockExampleRepository
    ) {}

    public function getExamplesByCategory(string $category): array
    {
        return $this->stockExampleRepository->findByCategory($category);
    }

    public function getExampleBySlug(string $slug): ?StockExample
    {
        return $this->stockExampleRepository->findBySlug($slug);
    }

    public function getCategories(): array
    {
        return $this->stockExampleRepository->getCategories();
    }

    public function getCategoryTitle(string $category): string
    {
        return match ($category) {
            'bulles-type-1' => 'Bulles type 1',
            'bulles-type-2' => 'Bulles type 2',
            'ramassage' => 'Ramassage',
            'ramassage-pic' => 'Ramassage + Pic',
            'pic-ramassage' => 'Pic + Ramassage',
            'pic-volumes' => 'Pics de volumes',
            'volumes-faibles' => 'Volumes faibles',
            'introductions-recentes' => 'Introductions',
            default => ucfirst(str_replace('-', ' ', $category))
        };
    }

    public function formatExampleForTemplate(StockExample $example): array
    {
        return [
            'title' => $example->getTitle(),
            'ticker' => $example->getTicker(),
            'flag' => $example->getFlag(),
            'image_jour' => $example->getImageJour(),
            'image_semaine' => $example->getImageSemaine(),
            'description' => $example->getDescription(),
            'slug' => $example->getSlug(),
        ];
    }

    public function formatExamplesForTemplate(array $examples): array
    {
        $formatted = [];
        foreach ($examples as $example) {
            $formatted[$example->getSlug()] = $this->formatExampleForTemplate($example);
        }
        return $formatted;
    }
}
