<?php

namespace App\Controller\Admin;

use App\Entity\Menu;
use App\Service\MenuService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;

class MenuCrudController extends AbstractCrudController
{
    private $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    public static function getEntityFqcn(): string
    {
        return Menu::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Liens de navigation : création, modification, suppression.')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modification')
            ->setPageTitle(Crud::PAGE_NEW, 'Création')
            ->showEntityActionsInlined()
            ->overrideTemplates([
                'crud/new' => 'admin/menu_form.html.twig',
                'crud/edit' => 'admin/menu_edit_form.html.twig'
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action
                    ->setIcon('fas fa-edit')
                    ->setLabel(false)
                    ->addCssClass('btn btn-link');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fas fa-trash')
                    ->setLabel(false)
                    ->addCssClass('btn btn-link text-danger');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            TextField::new('label')
                ->setHelp('Le nom affiché du menu'),

            TextField::new('slug')
                ->setHelp('Laissez vide pour générer automatiquement à partir du label')
                ->setDisabled(true),

            ChoiceField::new('section')
                ->setChoices([
                    'Accueil' => 'HOME',
                    'Investisseur' => 'INVESTISSEUR',
                    'Intraday' => 'INTRADAY'
                ])
                ->setHelp('Sélectionnez la section du menu')
                ->renderAsBadges([
                    'HOME' => 'success',
                    'INVESTISSEUR' => 'primary',
                    'INTRADAY' => 'warning'
                ]),

            IntegerField::new('menuorder', 'Position')
                ->setHelp('Ordre d\'affichage du menu (1, 2, 3...). Laissez vide pour placer automatiquement en dernière position.')
                ->setRequired(false),

            TextField::new('parent')
                ->setHelp('Menu parent (seuls les menus de la même section sont affichés)')
                ->setDisabled(true)
                ->formatValue(function ($value, $entity) {
                    if ($entity->getParent()) {
                        return $entity->getParent()->getLabel() . ' (' . $entity->getParent()->getSection() . ')';
                    }
                    return 'Aucun parent';
                }),
        ];
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);

        // Remplacer le champ parent par un EntityType personnalisé
        $formBuilder->add('parent', EntityType::class, [
            'class' => Menu::class,
            'choice_label' => function (Menu $menu) {
                return $menu->getLabel() . ' (' . $menu->getSection() . ')';
            },
            'required' => false,
            'help' => 'Laissez vide pour un menu principal. Seuls les menus de la même section seront affichés.',
            'query_builder' => function ($repository) {
                return $repository->createQueryBuilder('m')
                    ->where('m.parent IS NULL')
                    ->orderBy('m.section', 'ASC')
                    ->addOrderBy('m.menuorder', 'ASC');
            }
        ]);

        // Écouteur pour mettre à jour dynamiquement les parents selon la section
        $formBuilder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $menu = $event->getData();
            $form = $event->getForm();

            if ($menu && $menu->getSection()) {
                $this->updateParentField($form, $menu->getSection());
            }
        });

        $formBuilder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['section'])) {
                $this->updateParentField($form, $data['section']);
            }
        });

        return $formBuilder;
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);

        // Remplacer le champ parent par un EntityType personnalisé
        $formBuilder->add('parent', EntityType::class, [
            'class' => Menu::class,
            'choice_label' => function (Menu $menu) {
                return $menu->getLabel() . ' (' . $menu->getSection() . ')';
            },
            'required' => false,
            'help' => 'Laissez vide pour un menu principal. Seuls les menus de la même section seront affichés.',
            'query_builder' => function ($repository) {
                return $repository->createQueryBuilder('m')
                    ->where('m.parent IS NULL')
                    ->orderBy('m.section', 'ASC')
                    ->addOrderBy('m.menuorder', 'ASC');
            }
        ]);

        // Écouteur pour mettre à jour dynamiquement les parents selon la section
        $formBuilder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $menu = $event->getData();
            $form = $event->getForm();

            if ($menu && $menu->getSection()) {
                $this->updateParentField($form, $menu->getSection());
            }
        });

        $formBuilder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['section'])) {
                $this->updateParentField($form, $data['section']);
            }
        });

        return $formBuilder;
    }

    private function updateParentField($form, string $section): void
    {
        if ($form->has('parent')) {
            $form->remove('parent');
            $form->add('parent', EntityType::class, [
                'class' => Menu::class,
                'choice_label' => function (Menu $menu) {
                    return $menu->getLabel() . ' (' . $menu->getSection() . ')';
                },
                'required' => false,
                'help' => 'Laissez vide pour un menu principal. Seuls les menus de la même section seront affichés.',
                'query_builder' => function ($repository) use ($section) {
                    return $repository->createQueryBuilder('m')
                        ->where('m.section = :section')
                        ->andWhere('m.parent IS NULL')
                        ->setParameter('section', $section)
                        ->orderBy('m.menuorder', 'ASC');
                }
            ]);
        }
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var Menu $menu */
        $menu = $entityInstance;

        if ($menu->getSection() === 'HOME') {
            $menu->setRoute('app_home_page');
            $slug = $this->menuService->generateSlug($menu->getLabel());
            $menu->setSlug($slug);
        } elseif ($menu->getSection() === 'INTRADAY') {
            $menu->setRoute('app_intraday_page');
            $slug = $this->menuService->generateSlug($menu->getLabel());
            $menu->setSlug($slug);
        }

        // Génère le slug automatiquement s'il est vide
        if (empty($menu->getSlug())) {
            $slug = $this->menuService->generateSlug($menu->getLabel());
            $menu->setSlug($slug);
        }

        // Génère la route automatiquement si elle est vide
        if (empty($menu->getRoute())) {
            $route = $this->generateRoute($menu);
            $menu->setRoute($route);
        }

        // Gère automatiquement la position
        $this->handleMenuPosition($entityManager, $menu, null);

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var Menu $menu */
        $menu = $entityInstance;

        // Récupérer l'ancienne position pour la comparaison
        $oldPosition = $entityManager->getUnitOfWork()->getOriginalEntityData($menu)['menuorder'] ?? null;

        if ($menu->getSection() === 'HOME') {
            $menu->setRoute('app_home_page');
            $slug = $this->menuService->generateSlug($menu->getLabel());
            $menu->setSlug($slug);
        } elseif ($menu->getSection() === 'INTRADAY') {
            $menu->setRoute('app_intraday_page');
            $slug = $this->menuService->generateSlug($menu->getLabel());
            $menu->setSlug($slug);
        }

        // Génère le slug automatiquement s'il est vide
        if (empty($menu->getSlug())) {
            $slug = $this->menuService->generateSlug($menu->getLabel(), $menu->getId());
            $menu->setSlug($slug);
        }

        // Génère la route automatiquement si elle est vide
        if (empty($menu->getRoute())) {
            $route = $this->generateRoute($menu);
            $menu->setRoute($route);
        }

        // Gère automatiquement la position
        $this->handleMenuPosition($entityManager, $menu, $oldPosition);

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function handleMenuPosition(EntityManagerInterface $entityManager, Menu $menu, ?int $oldPosition): void
    {
        $newPosition = $menu->getMenuorder();

        // Si aucune position n'est spécifiée, placer en dernière position
        if ($newPosition === null || $newPosition === 0) {
            $lastPosition = $this->getLastPositionForSection($entityManager, $menu->getSection());
            $menu->setMenuorder($lastPosition + 1);
            return;
        }

        // Vérifier si la position est déjà occupée par un autre menu
        $existingMenu = $this->getMenuAtPosition($entityManager, $menu->getSection(), $newPosition, $menu->getId());

        if ($existingMenu) {
            // Déplacer tous les menus à partir de cette position vers le haut
            $this->shiftMenusFromPosition($entityManager, $menu->getSection(), $newPosition, $oldPosition);
        }
    }

    private function getMenuAtPosition(EntityManagerInterface $entityManager, string $section, int $position, ?int $excludeId = null): ?Menu
    {
        $repository = $entityManager->getRepository(Menu::class);
        $qb = $repository->createQueryBuilder('m')
            ->where('m.section = :section')
            ->andWhere('m.menuorder = :position')
            ->setParameter('section', $section)
            ->setParameter('position', $position);

        if ($excludeId) {
            $qb->andWhere('m.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    private function shiftMenusFromPosition(EntityManagerInterface $entityManager, string $section, int $position, ?int $oldPosition): void
    {
        $repository = $entityManager->getRepository(Menu::class);

        // Récupérer tous les menus de la section qui doivent être déplacés
        $menusToShift = $repository->createQueryBuilder('m')
            ->where('m.section = :section')
            ->andWhere('m.menuorder >= :position')
            ->setParameter('section', $section)
            ->setParameter('position', $position)
            ->orderBy('m.menuorder', 'DESC') // Important : traiter du plus grand au plus petit
            ->getQuery()
            ->getResult();

        // Déplacer chaque menu vers la position suivante
        foreach ($menusToShift as $menuToShift) {
            $menuToShift->setMenuorder($menuToShift->getMenuorder() + 1);
        }
    }

    private function getLastPositionForSection(EntityManagerInterface $entityManager, string $section): int
    {
        $repository = $entityManager->getRepository(Menu::class);
        $lastMenu = $repository->createQueryBuilder('m')
            ->where('m.section = :section')
            ->setParameter('section', $section)
            ->orderBy('m.menuorder', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $lastMenu ? $lastMenu->getMenuorder() : 0;
    }

    private function generateRoute(Menu $menu): string
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
