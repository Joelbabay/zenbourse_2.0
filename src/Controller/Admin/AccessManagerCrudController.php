<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AccessManagerCrudController extends AbstractCrudController
{
    public function __construct(
        private RequestStack $requestStack
    ) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Gestionnaire des accès - Attribution des privilèges administrateur')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier les accès administrateur')
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setDefaultSort(['lastname' => 'ASC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action
                    ->setIcon('fas fa-edit')
                    ->setLabel('Modifier les accès');
            });
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('lastname', 'Nom'))
            ->add(TextFilter::new('firstname', 'Prénom'))
            ->add(TextFilter::new('email', 'Email'))
            ->add(BooleanFilter::new('hasAdminAccess', 'A un accès admin')
                ->setFormTypeOption('choices', [
                    'Oui' => true,
                    'Non' => false,
                ]));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('lastname', 'Nom')
                ->onlyOnIndex(),
            TextField::new('firstname', 'Prénom')
                ->onlyOnIndex(),
            EmailField::new('email', 'Email')
                ->onlyOnIndex(),

            // Champ pour afficher le nom complet dans le formulaire
            TextField::new('fullName', 'Nom complet')
                ->formatValue(function ($value, $entity) {
                    return $entity->getFullName() ?: $entity->getEmail();
                })
                ->onlyOnForms()
                ->setDisabled(true),

            EmailField::new('email', 'Email')
                ->onlyOnForms()
                ->setDisabled(true),

            // Checkboxes pour les rôles administrateur (champ virtuel non mappé)
            ChoiceField::new('adminRoles', 'Rôles administrateur')
                ->setChoices([
                    'Super Administrateur' => 'ROLE_SUPER_ADMIN',
                    'Administrateur'       => 'ROLE_ADMIN',
                    'Éditeur'              => 'ROLE_EDITOR',
                ])
                ->allowMultipleChoices(true)
                ->renderExpanded(true)
                ->setHelp('Sélectionnez les rôles à attribuer')
                ->onlyOnForms(),

            // Badge pour afficher les rôles dans la liste
            TextField::new('adminRolesBadge', 'Accès administrateur')
                ->onlyOnIndex()
                ->formatValue(function ($value, $entity) {
                    $roles = $entity->getRoles();
                    $adminRoles = array_filter($roles, function ($role) {
                        return in_array($role, ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_EDITOR']);
                    });

                    if (empty($adminRoles)) {
                        return '<span class="badge bg-secondary">Aucun accès</span>';
                    }

                    $badges = [];
                    $roleBadges = [
                        'ROLE_SUPER_ADMIN' => '<span class="badge bg-danger">Super Admin</span>',
                        'ROLE_ADMIN' => '<span class="badge bg-primary">Admin</span>',
                        'ROLE_EDITOR' => '<span class="badge bg-info">Éditeur</span>',
                    ];

                    foreach ($adminRoles as $role) {
                        if (isset($roleBadges[$role])) {
                            $badges[] = $roleBadges[$role];
                        }
                    }

                    return implode(' ', $badges);
                })
                ->renderAsHtml(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Cette méthode ne devrait pas être appelée car on a désactivé la création
        parent::persistEntity($entityManager, $entityInstance);
    }
}
