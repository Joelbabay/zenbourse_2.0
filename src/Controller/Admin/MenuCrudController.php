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
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

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
            ->setPageTitle(Crud::PAGE_INDEX, 'Navigation : gestion hiérarchique des menus')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modification du menu')
            ->setPageTItle(Crud::PAGE_NEW, 'Création d\'un menu')
            ->showEntityActionsInlined()
            ->overrideTemplates([
                'crud/new' => 'admin/menu_form.html.twig',
                'crud/edit' => 'admin/menu_edit_form.html.twig'
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        // Action pour monter un élément dans la liste
        $moveUp = Action::new('moveUp', false, 'fa fa-arrow-up')
            ->linkToCrudAction('moveUp') // Pointe vers la méthode moveUp()
            ->setCssClass('btn btn-link text-success');

        // Action pour descendre un élément dans la liste
        $moveDown = Action::new('moveDown', false, 'fa fa-arrow-down')
            ->linkToCrudAction('moveDown') // Pointe vers la méthode moveDown()
            ->setCssClass('btn btn-link text-danger');

        return $actions
            ->add(Crud::PAGE_INDEX, $moveUp)
            ->add(Crud::PAGE_INDEX, $moveDown)
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
            })
            // Réorganise les boutons pour que les flèches soient en premier
            ->reorder(Crud::PAGE_INDEX, ['moveUp', 'moveDown', Action::EDIT, Action::DELETE]);
    }

    public function configureFields(string $pageName): iterable
    {
        return [

            TextField::new('label', 'Label')
                ->setHelp('Le nom affiché du menu')
                ->setTemplatePath('admin/fields/menu_label.html.twig'),

            BooleanField::new('isActive', 'État')
                ->setHelp('Si ce menu doit être visible sur le site public.')
                // Utilise un template personnalisé sur l'index pour contourner le problème de la requête PATCH
                ->setTemplatePath($pageName === Crud::PAGE_INDEX ? 'admin/fields/is_active_toggle.html.twig' : null)
                // Garde l'interrupteur classique sur les pages de création/modification
                ->renderAsSwitch($pageName !== Crud::PAGE_INDEX),

            TextField::new('slug')
                ->setHelp('Laissez vide pour générer automatiquement à partir du label')
                ->setDisabled(true)
                ->hideOnIndex()->hideOnForm(),

            ChoiceField::new('section', 'Section')
                ->setChoices([
                    'ACCUEIL' => 'HOME',
                    'INVESTISSEUR' => 'INVESTISSEUR',
                    'INTRADAY' => 'INTRADAY'
                ])
                ->setRequired(true)
                ->renderAsBadges([
                    'HOME' => 'warning',
                    'INVESTISSEUR' => 'success',
                    'INTRADAY' => 'primary'
                ]),

            IntegerField::new('menuorder', 'Position')
                ->setHelp('La position est gérée automatiquement avec les flèches d\'action.')
                ->setTemplatePath('admin/fields/menu_position.html.twig')
                ->hideOnForm(), // On cache le champ du formulaire

            TextField::new('parent')
                ->setHelp('Menu parent (seuls les menus de la même section sont affichés)')
                ->setDisabled(true)
                ->formatValue(function ($value, $entity) {
                    if ($entity->getParent()) {
                        return $entity->getParent()->getLabel() . ' (' . $entity->getParent()->getSection() . ')';
                    }
                    return 'Aucun parent';
                })
                ->hideOnIndex(),
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
        } elseif ($menu->getSection() === 'INVESTISSEUR') {
            // Si c'est un sous-menu (avec parent), on utilise la route enfant
            if ($menu->getParent()) {
                $menu->setRoute('app_investisseur_child_page');
            } else {
                // Si c'est un menu parent, on utilise la route parent
                $menu->setRoute('app_investisseur_page');
            }
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
        } elseif ($menu->getSection() === 'INVESTISSEUR') {
            // Si c'est un sous-menu (avec parent), on utilise la route enfant
            if ($menu->getParent()) {
                $menu->setRoute('app_investisseur_child_page');
            } else {
                // Si c'est un menu parent, on utilise la route parent
                $menu->setRoute('app_investisseur_page');
            }
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
        $section = $menu->getSection();
        $parent = $menu->getParent();

        // Validation de base
        if ($newPosition !== null && $newPosition < 1) {
            throw new \InvalidArgumentException('La position doit être supérieure ou égale à 1');
        }

        // Si aucune position n'est spécifiée, placer en dernière position
        if ($newPosition === null || $newPosition === 0) {
            $lastPosition = $this->getLastPositionForSectionAndParent($entityManager, $section, $parent);
            $menu->setMenuorder($lastPosition + 1);
            return;
        }

        // Si c'est une modification et que la position n'a pas changé, ne rien faire
        if ($oldPosition !== null && $oldPosition === $newPosition) {
            return;
        }

        // Récupérer tous les menus de la même section et du même parent
        $existingMenus = $this->getAllMenusInSectionAndParent($entityManager, $section, $parent, $menu->getId());

        // Vérifier si la position demandée est valide
        $maxPosition = count($existingMenus) + 1;
        if ($newPosition > $maxPosition) {
            $newPosition = $maxPosition;
            $menu->setMenuorder($newPosition);
        }

        // Réorganiser tous les menus pour éviter les conflits
        $this->reorganizeMenuPositions($entityManager, $section, $parent, $menu, $oldPosition, $newPosition);
    }

    private function getAllMenusInSectionAndParent(EntityManagerInterface $entityManager, string $section, ?Menu $parent, ?int $excludeId = null): array
    {
        $repository = $entityManager->getRepository(Menu::class);
        $qb = $repository->createQueryBuilder('m')
            ->where('m.section = :section')
            ->setParameter('section', $section);

        if ($parent) {
            $qb->andWhere('m.parent = :parent')
                ->setParameter('parent', $parent);
        } else {
            $qb->andWhere('m.parent IS NULL');
        }

        if ($excludeId) {
            $qb->andWhere('m.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return $qb->orderBy('m.menuorder', 'ASC')
            ->addOrderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    private function reorganizeMenuPositions(EntityManagerInterface $entityManager, string $section, ?Menu $parent, Menu $menu, ?int $oldPosition, int $newPosition): void
    {
        // Récupérer tous les menus existants (sans le menu en cours de modification)
        $existingMenus = $this->getAllMenusInSectionAndParent($entityManager, $section, $parent, $menu->getId());

        // Créer un tableau temporaire des positions
        $positions = [];
        $currentPosition = 1;

        // Traiter d'abord les menus avant la nouvelle position
        foreach ($existingMenus as $existingMenu) {
            if ($currentPosition < $newPosition) {
                $positions[$existingMenu->getId()] = $currentPosition;
                $currentPosition++;
            } else {
                break;
            }
        }

        // Placer le menu en cours de modification
        $positions[$menu->getId()] = $newPosition;
        $currentPosition++;

        // Traiter les menus restants
        foreach ($existingMenus as $existingMenu) {
            if (!isset($positions[$existingMenu->getId()])) {
                $positions[$existingMenu->getId()] = $currentPosition;
                $currentPosition++;
            }
        }

        // Appliquer les nouvelles positions
        foreach ($positions as $menuId => $position) {
            if ($menuId === $menu->getId()) {
                $menu->setMenuorder($position);
            } else {
                $existingMenu = $entityManager->getRepository(Menu::class)->find($menuId);
                if ($existingMenu) {
                    $existingMenu->setMenuorder($position);
                }
            }
        }

        // Validation finale : s'assurer qu'il n'y a pas de doublons
        $this->validateAndFixPositions($entityManager, $section, $parent);
    }

    private function validateAndFixPositions(EntityManagerInterface $entityManager, string $section, ?Menu $parent): void
    {
        $menus = $this->getAllMenusInSectionAndParent($entityManager, $section, $parent);

        // Vérifier s'il y a des doublons
        $positions = [];
        $duplicates = [];

        foreach ($menus as $menu) {
            $pos = $menu->getMenuorder();
            if (isset($positions[$pos])) {
                $duplicates[] = $pos;
            }
            $positions[$pos] = $menu->getId();
        }

        // S'il y a des doublons, réorganiser complètement
        if (!empty($duplicates)) {
            $this->completeReorganization($entityManager, $section, $parent);
        }
    }

    private function completeReorganization(EntityManagerInterface $entityManager, string $section, ?Menu $parent): void
    {
        $menus = $this->getAllMenusInSectionAndParent($entityManager, $section, $parent);

        // Réassigner toutes les positions de manière séquentielle
        $position = 1;
        foreach ($menus as $menu) {
            $menu->setMenuorder($position);
            $position++;
        }
    }

    private function getMenuAtPosition(EntityManagerInterface $entityManager, string $section, ?Menu $parent, int $position, ?int $excludeId = null): ?Menu
    {
        $repository = $entityManager->getRepository(Menu::class);
        $qb = $repository->createQueryBuilder('m')
            ->where('m.section = :section')
            ->andWhere('m.menuorder = :position')
            ->setParameter('section', $section)
            ->setParameter('position', $position);

        if ($parent) {
            $qb->andWhere('m.parent = :parent')
                ->setParameter('parent', $parent);
        } else {
            $qb->andWhere('m.parent IS NULL');
        }

        if ($excludeId) {
            $qb->andWhere('m.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        // Utiliser getResult() au lieu de getOneOrNullResult() pour éviter l'erreur
        $results = $qb->getQuery()->getResult();

        // Retourner le premier résultat trouvé ou null
        return !empty($results) ? $results[0] : null;
    }

    private function getMenusToShift(EntityManagerInterface $entityManager, string $section, ?Menu $parent, int $newPosition, ?int $excludeId = null): array
    {
        $repository = $entityManager->getRepository(Menu::class);
        $qb = $repository->createQueryBuilder('m')
            ->where('m.section = :section');

        if ($parent) {
            $qb->andWhere('m.parent = :parent')
                ->setParameter('parent', $parent);
        }

        $qb->andWhere('m.menuorder >= :newPosition')
            ->setParameter('section', $section)
            ->setParameter('newPosition', $newPosition)
            ->orderBy('m.menuorder', 'ASC');

        if ($excludeId) {
            $qb->andWhere('m.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return $qb->getQuery()
            ->getResult();
    }

    private function shiftMenusUp(EntityManagerInterface $entityManager, string $section, ?Menu $parent, int $fromPosition, int $toPosition, ?int $excludeId = null): void
    {
        $repository = $entityManager->getRepository(Menu::class);

        $qb = $repository->createQueryBuilder('m')
            ->where('m.section = :section');

        if ($parent) {
            $qb->andWhere('m.parent = :parent')
                ->setParameter('parent', $parent);
        }

        $qb->andWhere('m.menuorder >= :fromPosition')
            ->andWhere('m.menuorder <= :toPosition')
            ->setParameter('section', $section)
            ->setParameter('fromPosition', $fromPosition)
            ->setParameter('toPosition', $toPosition)
            ->orderBy('m.menuorder', 'ASC');

        if ($excludeId) {
            $qb->andWhere('m.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        $menusToShift = $qb->getQuery()
            ->getResult();

        // Décaler chaque menu vers le haut
        foreach ($menusToShift as $menuToShift) {
            if ($menuToShift->getId() !== $excludeId) {
                $menuToShift->setMenuorder($menuToShift->getMenuorder() + 1);
            }
        }
    }

    private function getLastPositionForSectionAndParent(EntityManagerInterface $entityManager, string $section, ?Menu $parent): int
    {
        $repository = $entityManager->getRepository(Menu::class);
        $qb = $repository->createQueryBuilder('m')
            ->where('m.section = :section')
            ->setParameter('section', $section);

        if ($parent) {
            $qb->andWhere('m.parent = :parent')
                ->setParameter('parent', $parent);
        }

        $lastMenu = $qb->orderBy('m.menuorder', 'DESC')
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

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // Récupérer la section depuis les paramètres de la requête
        $section = $this->getContext()->getRequest()->query->get('section');

        if ($section) {
            $queryBuilder->andWhere('entity.section = :section')
                ->setParameter('section', $section);
        }

        // Jointure pour accéder aux informations du parent
        $queryBuilder->leftJoin('entity.parent', 'p');

        // Crée un tri hiérarchique :
        // 1. D'abord par section.
        // 2. Ensuite, on regroupe les enfants avec leur parent en utilisant le `menuorder` du parent.
        // 3. Puis, on affiche le parent avant ses enfants dans le groupe.
        // 4. Enfin, on trie les enfants entre eux par leur propre `menuorder`.
        $queryBuilder
            ->addSelect('CASE WHEN entity.parent IS NULL THEN entity.menuorder ELSE p.menuorder END AS HIDDEN group_order')
            ->addSelect('CASE WHEN entity.parent IS NULL THEN 0 ELSE 1 END AS HIDDEN parent_first')
            ->orderBy('entity.section', 'ASC')
            ->addOrderBy('group_order', 'ASC')
            ->addOrderBy('parent_first', 'ASC')
            ->addOrderBy('entity.menuorder', 'ASC');

        return $queryBuilder;
    }

    public function toggleIsActive(AdminContext $context, EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator): Response
    {
        /** @var Menu $menu */
        $menu = $context->getEntity()->getInstance();
        if ($menu) {
            $menu->setIsActive(!$menu->isIsActive());
            $entityManager->flush();
            // $this->addFlash('success', 'Le statut du menu a été mis à jour.');
        } else {
            //$this->addFlash('error', 'Menu non trouvé.');
        }

        // Redirige vers la page d'où l'action a été initiée (en conservant les filtres, la page, etc.)
        return $this->redirect($context->getReferrer() ?? $adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl());
    }

    public function moveUp(AdminContext $context, EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator): Response
    {
        return $this->move($context, $entityManager, $adminUrlGenerator, 'up');
    }

    public function moveDown(AdminContext $context, EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator): Response
    {
        return $this->move($context, $entityManager, $adminUrlGenerator, 'down');
    }

    private function move(AdminContext $context, EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator, string $direction): Response
    {
        $menuToMove = $context->getEntity()->getInstance();
        if (!$menuToMove instanceof Menu) {
            $this->addFlash('error', 'Impossible de trouver l\'élément à déplacer.');
            return $this->redirect($this->generateDefaultUrl($adminUrlGenerator));
        }

        $repository = $entityManager->getRepository(Menu::class);
        $swapWith = $repository->findNeighbor($menuToMove, $direction);

        if ($swapWith) {
            $orderToMove = $menuToMove->getMenuorder();
            $orderToSwap = $swapWith->getMenuorder();

            $menuToMove->setMenuorder($orderToSwap);
            $swapWith->setMenuorder($orderToMove);

            $entityManager->flush();
            //$this->addFlash('success', 'La position du menu a été mise à jour.');
        } else {
            $this->addFlash('warning', 'Le déplacement est impossible (déjà en première ou dernière position).');
        }

        return $this->redirect($this->generateDefaultUrl($adminUrlGenerator));
    }

    private function generateDefaultUrl(AdminUrlGenerator $adminUrlGenerator): string
    {
        return $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();
    }
}
