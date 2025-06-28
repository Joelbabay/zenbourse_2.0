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
    name: 'app:update-menu-routes',
    description: 'Met à jour automatiquement les routes des menus selon la nouvelle logique',
)]
class UpdateMenuRoutesCommand extends Command
{
    private $menuRepository;
    private $entityManager;

    public function __construct(
        MenuRepository $menuRepository,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->menuRepository = $menuRepository;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Mise à jour des routes des menus');

        // Récupère tous les menus
        $menus = $this->menuRepository->findAll();

        if (empty($menus)) {
            $io->warning('Aucun menu trouvé dans la base de données.');
            return Command::SUCCESS;
        }

        $io->text(sprintf('Traitement de %d menus...', count($menus)));

        $updatedCount = 0;
        $skippedCount = 0;

        // Mapping des anciennes routes vers les nouvelles routes
        $routeMapping = [
            // Routes de la méthode
            'investisseur_la-methode_vague-d-elliot' => 'investisseur_methode_vagues_elliot',
            'investisseur_la-methode_cycles-boursiers' => 'investisseur_methode_cycles_boursiers',
            'investisseur_la-methode_la-bulle' => 'investisseur_methode_boites_bulles',
            'investisseur_la-methode_indicateurs' => 'investisseur_methode_indicateurs',

            // Routes de la bibliothèque (utiliser les routes dynamiques)
            'investisseur_bibliotheque_bulles-type-1' => 'investisseur_bibliotheque_category',
            'investisseur_bibliotheque_bulles-type-2' => 'investisseur_bibliotheque_category',
            'investisseur_bibliotheque_ramassage' => 'investisseur_bibliotheque_category',
            'investisseur_bibliotheque_ramassage-pic' => 'investisseur_bibliotheque_category',
            'investisseur_bibliotheque_pic-ramassage' => 'investisseur_bibliotheque_category',
            'investisseur_bibliotheque_pics-de-volumes' => 'investisseur_bibliotheque_category',
            'investisseur_bibliotheque_volumes-faibles' => 'investisseur_bibliotheque_category',
            'investisseur_bibliotheque_introductions' => 'investisseur_bibliotheque_category',
        ];

        foreach ($menus as $menu) {
            $oldRoute = $menu->getRoute();
            $newRoute = $this->generateRoute($menu);

            if ($oldRoute !== $newRoute) {
                $menu->setRoute($newRoute);
                $io->text(sprintf('✓ Route mise à jour pour "%s": "%s" → "%s"', $menu->getLabel(), $oldRoute, $newRoute));
                $updatedCount++;
            } else {
                $io->text(sprintf('- Route déjà correcte pour "%s": "%s"', $menu->getLabel(), $oldRoute));
                $skippedCount++;
            }
        }

        // Persiste les changements
        $this->entityManager->flush();

        $io->success([
            sprintf('%d routes mises à jour', $updatedCount),
            sprintf('%d routes déjà correctes', $skippedCount),
            'Toutes les routes ont été sauvegardées en base de données.'
        ]);

        return Command::SUCCESS;
    }

    private function generateRoute($menu): string
    {
        $baseRoute = strtolower($menu->getSection());
        $slug = $menu->getSlug();

        // Si c'est un menu enfant, on ajoute le slug du parent
        if ($menu->getParent()) {
            $parentSlug = $menu->getParent()->getSlug();
            return $baseRoute . '_' . $parentSlug . '_' . $slug;
        }

        return $baseRoute . '_' . $slug;
    }
}
