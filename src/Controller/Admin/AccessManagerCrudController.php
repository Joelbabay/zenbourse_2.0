<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AccessManagerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    /* ============================
     * CRUD CONFIG
     * ============================ */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Gestion des administrateurs')
            ->setEntityLabelInSingular('Administrateur')
            ->setEntityLabelInPlural('Administrateurs')
            ->showEntityActionsInlined()
            ->setDefaultSort(['email' => 'ASC']);
    }
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $qb = parent::createIndexQueryBuilder(
            $searchDto,
            $entityDto,
            $fields,
            $filters
        );

        $qb->andWhere(
            $qb->expr()->orX(
                'entity.roles LIKE :superAdmin',
                'entity.roles LIKE :admin',
                'entity.roles LIKE :editor'
            )
        )
            ->setParameter('superAdmin', '%ROLE_SUPER_ADMIN%')
            ->setParameter('admin', '%ROLE_ADMIN%')
            ->setParameter('editor', '%ROLE_EDITOR%');

        return $qb;
    }

    /* ============================
     * ACTIONS
     * ============================ */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action
                    ->setIcon('fa fa-pen')
                    ->addCssClass('btn btn-sm btn-primary');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->addCssClass('text-white btn btn-sm btn-danger');
            });
    }

    /* ============================
     * FIELDS (INDEX)
     * ============================ */
    public function configureFields(string $pageName): iterable
    {
        $rolesField = TextField::new('adminRoles', 'Rôle')
            ->hideOnForm()
            ->formatValue(function ($value, User $user) {
                $badges = [];

                foreach ($user->getRoles() as $role) {
                    switch ($role) {
                        case 'ROLE_SUPER_ADMIN':
                            $badges[] = '<span class="text-white badge bg-danger">SUPER ADMIN</span>';
                            break;
                        case 'ROLE_ADMIN':
                            $badges[] = '<span class="text-white badge bg-primary">ADMIN</span>';
                            break;
                        case 'ROLE_EDITOR':
                            $badges[] = '<span class="text-white badge bg-info">EDITOR</span>';
                            break;
                    }
                }

                return sort($badges)
                    ? implode(' ', $badges)
                    : '<span class="text-white badge bg-secondary">—</span>';
            })
            ->renderAsHtml()
            ->setSortable(false);

        return [
            EmailField::new('email', 'Utilisateur'),
            $rolesField,

            // Pour le formulaire
            ChoiceField::new('roles', 'Rôles')
                ->onlyOnForms()
                ->setChoices([
                    'Super Admin' => 'ROLE_SUPER_ADMIN',
                    'Admin' => 'ROLE_ADMIN',
                    'Editor' => 'ROLE_EDITOR',
                ])
                ->allowMultipleChoices()
                ->renderExpanded(),
        ];
    }

    /* ============================
     * ACTION : RETIRER ACCÈS ADMIN
     * ============================ */
    public function removeAdminRoles(
        EntityManagerInterface $em,
        EntityDto $entityDto
    ): RedirectResponse {
        /** @var User $user */
        $user = $entityDto->getInstance();

        $roles = array_filter(
            $user->getRoles(),
            fn($role) => !in_array($role, [
                'ROLE_SUPER_ADMIN',
                'ROLE_ADMIN',
                'ROLE_EDITOR'
            ])
        );

        $user->setRoles($roles);
        $em->flush();

        $this->addFlash(
            'success',
            sprintf('Les accès administrateur ont été retirés pour %s', $user->getEmail())
        );

        return $this->redirect($this->generateUrl('admin'));
    }
}
