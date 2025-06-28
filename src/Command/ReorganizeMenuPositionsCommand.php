<?php

namespace App\Command;

use App\Entity\Menu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:reorganize-menu-positions',
    description: 'Réorganise les positions des menus par section'
)]
class ReorganizeMenuPositionsCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Réorganisation des positions des menus');

        $sections = ['HOME', 'INVESTISSEUR', 'INTRADAY'];
        $totalUpdated = 0;

        foreach ($sections as $section) {
            $io->section("Traitement de la section: $section");

            // Récupérer tous les menus de la section, triés par position actuelle
            $menus = $this->entityManager->getRepository(Menu::class)
                ->createQueryBuilder('m')
                ->where('m.section = :section')
                ->setParameter('section', $section)
                ->orderBy('m.menuorder', 'ASC')
                ->addOrderBy('m.id', 'ASC')
                ->getQuery()
                ->getResult();

            $position = 1;
            $updated = 0;

            foreach ($menus as $menu) {
                $oldPosition = $menu->getMenuorder();
                $menu->setMenuorder($position);

                if ($oldPosition !== $position) {
                    $io->text("  - {$menu->getLabel()}: {$oldPosition} → {$position}");
                    $updated++;
                }

                $position++;
            }

            $totalUpdated += $updated;
            $io->text("Section $section: $updated menus mis à jour");
        }

        // Persister les changements
        $this->entityManager->flush();

        $io->success("Réorganisation terminée ! $totalUpdated menus mis à jour au total.");

        return Command::SUCCESS;
    }
}
