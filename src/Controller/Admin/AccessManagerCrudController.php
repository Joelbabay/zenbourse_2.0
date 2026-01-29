<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

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
        // Action GLOBALE - ouvre le modal
        $addAdmin = Action::new('addAdmin', 'Ajouter un administrateur')
            ->setIcon('fa fa-user-plus')
            ->linkToCrudAction('showAddAdminModal')
            ->addCssClass('btn btn-success')
            ->createAsGlobalAction();

        // Action PAR LIGNE
        $removeAdmin = Action::new('removeAdmin', 'Retirer accès')
            ->linkToCrudAction('removeAdminAccess')
            ->setIcon('fa fa-trash')
            ->addCssClass('btn btn-sm btn-danger');

        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->add(Crud::PAGE_INDEX, $addAdmin)
            ->setPermission('addAdmin', 'ROLE_SUPER_ADMIN')
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_INDEX, $removeAdmin);
    }

    /* ============================
     * ASSETS - Pour le modal
     * ============================ */
    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addHtmlContentToBody('
                <!-- Modal Ajouter Administrateur -->
                <div class="modal fade" id="addAdminModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content" id="modalContent">
                            <!-- Contenu chargé dynamiquement -->
                        </div>
                    </div>
                </div>
            ')
            ->addJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js');
    }

    /* ============================
     * FIELDS (INDEX)
     * ============================ */
    public function configureFields(string $pageName): iterable
    {
        return [
            EmailField::new('email', 'Utilisateur'),

            Field::new('adminRoles', 'Rôle')
                ->onlyOnIndex()
                ->formatValue(function ($value, User $user) {
                    $badges = [];

                    foreach ($user->getRoles() as $role) {
                        switch ($role) {
                            case 'ROLE_SUPER_ADMIN':
                                $badges[] = '<span class="badge bg-danger text-white">SUPER ADMIN</span>';
                                break;
                            case 'ROLE_ADMIN':
                                $badges[] = '<span class="badge bg-primary text-white">ADMIN</span>';
                                break;
                            case 'ROLE_EDITOR':
                                $badges[] = '<span class="badge bg-info text-white">EDITOR</span>';
                                break;
                        }
                    }

                    return $badges
                        ? implode(' ', $badges)
                        : '<span class="badge bg-secondary text-white">—</span>';
                })
                ->setTemplatePath('admin/fields/html.html.twig')
                ->setSortable(false),

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
     * MODAL : Afficher le formulaire
     * ============================ */
    public function showAddAdminModal(
        EntityManagerInterface $em
    ): Response {
        // Récupérer les utilisateurs qui ne sont PAS déjà admin
        $users = $em->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.roles NOT LIKE :superAdmin')
            ->andWhere('u.roles NOT LIKE :admin')
            ->andWhere('u.roles NOT LIKE :editor')
            ->setParameter('superAdmin', '%ROLE_SUPER_ADMIN%')
            ->setParameter('admin', '%ROLE_ADMIN%')
            ->setParameter('editor', '%ROLE_EDITOR%')
            ->orderBy('u.email', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/add_admin_modal.html.twig', [
            'users' => $users,
        ]);
    }

    /* ============================
     * MODAL : Traiter le formulaire
     * ============================ */
    public function processAddAdmin(
        Request $request,
        EntityManagerInterface $em,
        AdminUrlGenerator $adminUrlGenerator
    ): Response {
        if (!$request->isMethod('POST')) {
            return new JsonResponse(['error' => 'Méthode non autorisée'], 405);
        }

        $userId = $request->request->get('user_id');
        $selectedRoles = $request->request->all('admin_roles');

        if (!$userId || empty($selectedRoles)) {
            $this->addFlash('danger', 'Veuillez sélectionner un utilisateur et au moins un rôle');
            return $this->redirect(
                $adminUrlGenerator
                    ->setController(self::class)
                    ->setAction(Action::INDEX)
                    ->generateUrl()
            );
        }

        /** @var User|null $user */
        $user = $em->getRepository(User::class)->find($userId);

        if (!$user) {
            $this->addFlash('danger', 'Utilisateur introuvable');
            return $this->redirect(
                $adminUrlGenerator
                    ->setController(self::class)
                    ->setAction(Action::INDEX)
                    ->generateUrl()
            );
        }

        // Ajouter les nouveaux rôles
        $currentRoles = $user->getRoles();
        $newRoles = array_unique(array_merge($currentRoles, $selectedRoles));
        $user->setRoles($newRoles);

        $em->flush();

        $this->addFlash(
            'success',
            sprintf('%s a maintenant les droits d\'administrateur', $user->getEmail())
        );

        return $this->redirect(
            $adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );
    }

    /* ============================
     * ACTION : RETIRER ACCÈS ADMIN
     * ============================ */
    public function removeAdminAccess(
        AdminUrlGenerator $adminUrlGenerator,
        EntityManagerInterface $em,
        Request $request
    ): RedirectResponse {
        $entityId = $request->query->get('entityId');

        /** @var User $user */
        $user = $em->getRepository(User::class)->find($entityId);

        if (!$user) {
            $this->addFlash('danger', 'Utilisateur introuvable');
            return $this->redirect(
                $adminUrlGenerator
                    ->setController(self::class)
                    ->setAction(Action::INDEX)
                    ->generateUrl()
            );
        }

        $roles = array_filter(
            $user->getRoles(),
            fn($role) => !in_array($role, [
                'ROLE_SUPER_ADMIN',
                'ROLE_ADMIN',
                'ROLE_EDITOR'
            ])
        );

        $user->setRoles(array_values($roles));
        $em->flush();

        $this->addFlash(
            'success',
            sprintf('Les accès administrateur ont été retirés pour %s', $user->getEmail())
        );

        return $this->redirect(
            $adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );
    }
}
