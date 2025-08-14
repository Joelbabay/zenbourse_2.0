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
    name: 'app:update-investisseur-routes',
    description: 'Met à jour les routes des menus investisseur selon leur hiérarchie'
)]
class UpdateInvestisseurRoutesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Mise à jour des routes des menus investisseur');

        $repository = $this->entityManager->getRepository(Menu::class);

        // Récupérer tous les menus investisseur
        $menus = $repository->findBy(['section' => 'INVESTISSEUR']);

        $updatedCount = 0;
        $errors = [];

        foreach ($menus as $menu) {
            try {
                $oldRoute = $menu->getRoute();

                // Déterminer la nouvelle route selon la hiérarchie
                if ($menu->getParent()) {
                    // Sous-menu
                    $newRoute = 'app_investisseur_child_page';
                } else {
                    // Menu parent
                    $newRoute = 'app_investisseur_page';
                }

                // Mettre à jour la route si nécessaire
                if ($oldRoute !== $newRoute) {
                    $menu->setRoute($newRoute);
                    $updatedCount++;

                    $io->text(sprintf(
                        'Menu "%s" : %s → %s',
                        $menu->getLabel(),
                        $oldRoute,
                        $newRoute
                    ));
                }
            } catch (\Exception $e) {
                $errors[] = sprintf('Erreur pour le menu "%s": %s', $menu->getLabel(), $e->getMessage());
            }
        }

        if ($updatedCount > 0) {
            $this->entityManager->flush();
            $io->success(sprintf('%d routes mises à jour avec succès', $updatedCount));
        } else {
            $io->info('Aucune route à mettre à jour');
        }

        if (!empty($errors)) {
            $io->warning('Erreurs rencontrées :');
            foreach ($errors as $error) {
                $io->text('- ' . $error);
            }
        }

        return Command::SUCCESS;
    }
}
