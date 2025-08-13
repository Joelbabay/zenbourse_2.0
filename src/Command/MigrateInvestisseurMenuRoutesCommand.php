<?php

namespace App\Command;

use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate-investisseur-menu-routes',
    description: 'Migre les routes spécifiques INVESTISSEUR vers la route dynamique'
)]
class MigrateInvestisseurMenuRoutesCommand extends Command
{
    public function __construct(
        private MenuRepository $menuRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Migration des routes INVESTISSEUR vers le système dynamique');

        // Récupérer tous les menus de la section INVESTISSEUR
        $investisseurMenus = $this->menuRepository->findBy(['section' => 'INVESTISSEUR']);

        if (empty($investisseurMenus)) {
            $io->warning('Aucun menu INVESTISSEUR trouvé.');
            return Command::SUCCESS;
        }

        $io->progressStart(count($investisseurMenus));

        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($investisseurMenus as $menu) {
            $oldRoute = $menu->getRoute();

            // Si la route est déjà 'app_investisseur_page', on la laisse
            if ($oldRoute === 'app_investisseur_page') {
                $skippedCount++;
                $io->progressAdvance();
                continue;
            }

            // Mettre à jour la route
            $menu->setRoute('app_investisseur_page');
            $updatedCount++;

            $io->writeln(sprintf(
                'Menu: "%s" | Ancienne route: %s → Nouvelle route: app_investisseur_page',
                $menu->getLabel(),
                $oldRoute ?: 'null'
            ));

            $io->progressAdvance();
        }

        // Sauvegarder les changements
        $this->entityManager->flush();

        $io->progressFinish();

        $io->success([
            sprintf('Migration terminée !'),
            sprintf('• %d menus mis à jour', $updatedCount),
            sprintf('• %d menus déjà corrects', $skippedCount),
            sprintf('• Total: %d menus traités', count($investisseurMenus))
        ]);

        $io->note([
            'Les menus INVESTISSEUR utilisent maintenant la route dynamique "app_investisseur_page".',
            'Vous pouvez maintenant créer du contenu dynamique pour ces pages depuis l\'admin.'
        ]);

        return Command::SUCCESS;
    }
}
