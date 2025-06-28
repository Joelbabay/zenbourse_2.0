<?php

namespace App\Command;

use App\Entity\Menu;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-slugs',
    description: 'Génère des slugs pour les menus existants',
)]
class GenerateSlugsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MenuRepository $menuRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Génération des slugs pour les menus');

        $menus = $this->entityManager->getRepository(Menu::class)->findAll();
        $updatedCount = 0;
        $usedSlugs = [];

        // Pré-remplir avec les slugs déjà existants en base (hors menu courant)
        foreach ($menus as $menu) {
            $slug = $menu->getSlug();
            if (!empty($slug)) {
                $usedSlugs[$slug] = true;
            }
        }

        foreach ($menus as $menu) {
            $currentSlug = $menu->getSlug();
            
            // Si le slug est vide ou commence par "menu-", générer un nouveau slug
            if (empty($currentSlug) || str_starts_with($currentSlug, 'menu-')) {
                $baseSlug = $this->generateSlug($menu->getLabel());
                $newSlug = $baseSlug;
                $counter = 1;
                // Chercher un slug unique (en mémoire et en base)
                while (isset($usedSlugs[$newSlug]) || $this->menuRepository->slugExists($newSlug, $menu->getId())) {
                    $newSlug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                $menu->setSlug($newSlug);
                $usedSlugs[$newSlug] = true;
                $updatedCount++;
                $io->text(sprintf('Menu "%s" : %s → %s', $menu->getLabel(), $currentSlug, $newSlug));
            }
        }

        if ($updatedCount > 0) {
            $this->entityManager->flush();
            $io->success(sprintf('%d menus ont été mis à jour avec de nouveaux slugs.', $updatedCount));
        } else {
            $io->info('Aucun menu nécessitait de mise à jour de slug.');
        }

        return Command::SUCCESS;
    }

    private function generateSlug(string $text): string
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
}
