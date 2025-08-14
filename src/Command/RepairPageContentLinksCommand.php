<?php

namespace App\Command;

use App\Entity\Menu;
use App\Entity\PageContent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:repair-page-content-links',
    description: 'Répare les liens cassés entre PageContent et Menu.',
)]
class RepairPageContentLinksCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        // Configuration si nécessaire
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Début de la réparation des liens PageContent <-> Menu');

        $pageContents = $this->entityManager->getRepository(PageContent::class)->findAll();
        $rawConn = $this->entityManager->getConnection();
        $repairedCount = 0;

        foreach ($pageContents as $pageContent) {
            // Si le lien existe déjà, on ne fait rien
            if ($pageContent->getMenu() !== null) {
                continue;
            }

            // Lire l'ID du menu directement depuis la BDD
            $stmt = $rawConn->prepare('SELECT menu_id FROM page_content WHERE id = :id');
            $result = $stmt->executeQuery(['id' => $pageContent->getId()]);
            $menuId = $result->fetchOne();

            if ($menuId) {
                $menu = $this->entityManager->getRepository(Menu::class)->find($menuId);
                if ($menu) {
                    $pageContent->setMenu($menu);
                    $menu->setPageContent($pageContent); // Forcer la relation bidirectionnelle

                    $this->entityManager->persist($pageContent);
                    $this->entityManager->persist($menu);

                    $io->writeln(sprintf('  - Réparation pour PageContent "%s" (ID: %d) -> Menu "%s" (ID: %d)', $pageContent->getTitle(), $pageContent->getId(), $menu->getLabel(), $menu->getId()));
                    $repairedCount++;
                } else {
                    $io->warning(sprintf('Menu avec ID %d non trouvé pour PageContent ID %d.', $menuId, $pageContent->getId()));
                }
            }
        }

        if ($repairedCount > 0) {
            $this->entityManager->flush();
            $io->success(sprintf('%d lien(s) ont été réparés avec succès !', $repairedCount));
        } else {
            $io->info('Aucun lien à réparer. Tout semble correct.');
        }

        return Command::SUCCESS;
    }
}
