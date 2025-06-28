<?php

namespace App\Command;

use App\Entity\Menu;
use App\Entity\CandlestickPattern;
use App\Repository\MenuRepository;
use App\Repository\CandlestickPatternRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-chandeliers-submenus',
    description: 'Génère les sous-menus pour chaque pattern de chandeliers japonais',
)]
class GenerateChandeliersSubMenusCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private MenuRepository $menuRepository,
        private CandlestickPatternRepository $patternRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Génération des sous-menus Chandeliers Japonais');

        // 1. Trouver le menu parent "chandeliers japonais"
        $parentMenu = $this->menuRepository->findOneBy([
            'section' => 'INVESTISSEUR',
            'route' => 'investisseur_methode_chandeliers_japonais',
        ]);
        if (!$parentMenu) {
            $io->error('Menu parent "chandeliers japonais" non trouvé.');
            return Command::FAILURE;
        }

        // 2. Récupérer tous les patterns actifs
        $patterns = $this->patternRepository->findAllActive();
        $order = 1;
        $created = 0;
        $updated = 0;

        foreach ($patterns as $pattern) {
            // Vérifier si un menu existe déjà pour ce pattern
            $existing = $this->menuRepository->findOneBy([
                'parent' => $parentMenu,
                'section' => 'INVESTISSEUR',
                'route' => 'investisseur_methode_chandeliers_japonais_detail',
                'slug' => $pattern->getSlug(),
            ]);
            if ($existing) {
                // Mettre à jour l'ordre et le label si besoin
                $existing->setLabel($pattern->getTitle());
                $existing->setMenuorder($order);
                $this->em->persist($existing);
                $updated++;
            } else {
                $menu = new Menu();
                $menu->setLabel($pattern->getTitle());
                $menu->setSlug($pattern->getSlug());
                $menu->setRoute('investisseur_methode_chandeliers_japonais_detail');
                $menu->setSection('INVESTISSEUR');
                $menu->setParent($parentMenu);
                $menu->setMenuorder($order);
                $this->em->persist($menu);
                $created++;
            }
            $order++;
        }
        $this->em->flush();
        $io->success("Sous-menus générés : $created créés, $updated mis à jour.");
        return Command::SUCCESS;
    }
} 