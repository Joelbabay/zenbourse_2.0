<?php

namespace App\Command;

use App\Entity\Menu;
use App\Repository\MenuRepository;
use App\Twig\Extension\AppExtension;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCommand(
    name: 'app:test-investisseur-parent-active',
    description: 'Test spécifique pour vérifier que les menus parents sont actifs quand on visite leurs sous-menus'
)]
class TestInvestisseurParentActiveCommand extends Command
{
    public function __construct(
        private MenuRepository $menuRepository,
        private AppExtension $appExtension,
        private RequestStack $requestStack
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test des menus parents actifs pour les sous-menus INVESTISSEUR');
        $io->section('Vérification de la logique des menus parents actifs');

        // Récupérer les menus parents INVESTISSEUR qui ont des enfants
        $parentMenus = $this->menuRepository->createQueryBuilder('m')
            ->where('m.section = :section')
            ->andWhere('m.parent IS NULL')
            ->andWhere('m.children IS NOT EMPTY')
            ->setParameter('section', 'INVESTISSEUR')
            ->orderBy('m.menuorder', 'ASC')
            ->getQuery()
            ->getResult();

        if (empty($parentMenus)) {
            $io->error('Aucun menu parent INVESTISSEUR avec des enfants trouvé !');
            return Command::FAILURE;
        }

        $io->text(sprintf('📋 %d menus parents INVESTISSEUR avec enfants trouvés', count($parentMenus)));

        foreach ($parentMenus as $parentMenu) {
            $io->section(sprintf('Menu parent: "%s" (slug: %s)', $parentMenu->getLabel(), $parentMenu->getSlug()));

            $children = $parentMenu->getChildren();
            $io->text(sprintf('  • Nombre d\'enfants: %d', $children->count()));

            // Tester chaque sous-menu pour voir si le parent devient actif
            foreach ($children as $child) {
                $io->text(sprintf('  • Test avec sous-menu: "%s" (slug: %s)', $child->getLabel(), $child->getSlug()));

                // Simuler une visite du sous-menu
                $request = new Request();
                $request->attributes->set('_route', 'app_investisseur_page');
                $request->attributes->set('slug', $child->getSlug());

                $this->requestStack->push($request);

                // Vérifier si le parent est actif
                $parentIsActive = $this->appExtension->isActiveMenu('app_investisseur_page', $parentMenu);

                // Vérifier si l'enfant est actif
                $childIsActive = $this->appExtension->isActiveChild('app_investisseur_page', $child);

                $io->text(sprintf(
                    '    - Parent actif: %s | Enfant actif: %s',
                    $parentIsActive ? '✅ OUI' : '❌ NON',
                    $childIsActive ? '✅ OUI' : '❌ NON'
                ));

                $this->requestStack->pop();
            }
        }

        $io->success('Test terminé ! Les menus parents doivent être actifs quand on visite leurs sous-menus.');
        $io->note('Si un menu parent n\'est pas actif, cela signifie que la logique doit être corrigée.');

        return Command::SUCCESS;
    }
}
