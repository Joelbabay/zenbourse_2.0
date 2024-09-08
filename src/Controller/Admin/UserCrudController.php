<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository as OrmEntityRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @method User getUser()
 */
class UserCrudController extends AbstractCrudController
{
    private $userRepository;
    private $entityRepository;

    public function __construct(OrmEntityRepository $entityRepository, UserRepository $userRepository, private UserPasswordHasherInterface $passwordHasher, private RequestStack $requestStack)
    {
        $this->userRepository = $userRepository;
        $this->entityRepository = $entityRepository;
    }
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Les utilisateurs')
            ->setPageTitle(Crud::PAGE_EDIT, 'Informations utilisateur')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer un utilisateur')
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {

        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-plus')->setLabel('Créer un utilisateur');
            })

            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action
                    ->setIcon('fas fa-edit')
                    ->setLabel(false)
                    ->addCssClass('btn btn-link');
            })
            // Mise à jour de l'action de suppression pour utiliser une icône spécifique
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
            ChoiceField::new('civility')
                ->setLabel('Civilité')
                ->setChoices([
                    'Mr' => 'Mr',
                    'Mme' => 'Mme',
                ]),
            TextField::new('lastname', 'Nom'),
            TextField::new('firstname', 'Prénom'),
            TextField::new('email', 'E-mail'),
            ChoiceField::new('roles')
                ->renderAsBadges([
                    'ROLE_ADMIN' => 'danger',
                    'ROLE_INVITE' => 'primary',
                    'ROLE_CLIENT' => 'success',
                    'ROLE_PROSPECT' => 'info'
                ])
                ->setChoices([
                    'Admin' => 'ROLE_ADMIN',
                    'Client' => 'ROLE_CLIENT',
                    'Invité' => 'ROLE_INVITE',
                    'Prospect' => 'ROLE_PROSPECT',
                ])
                ->allowMultipleChoices()->setRequired(false),
            ChoiceField::new('note')
                ->setLabel('Étoile')
                ->setChoices([
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4,
                    '5' => 5
                ])
                ->setHelp('Choisissez une note de 1 à 5'),
            TextField::new('phone', 'Téléphone'),
            TextField::new('password')->onlyOnForms(),
            TextField::new('city', 'Ville'),
            BooleanField::new('isInvestisseur', 'Investisseur')->setFormTypeOptions([
                'mapped' => true,
                'attr' => [
                    'class' => 'js-investisseur-checkbox'
                ]
            ]),
            DateTimeField::new('investorAccessDate', 'Date')->setFormat('dd/MM/YYYY')->onlyOnIndex(),
            BooleanField::new('isIntraday', 'Intraday')->setFormTypeOptions([
                'mapped' => true,
                'attr' => [
                    'class' => 'js-intraday-checkbox'
                ]
            ]),
            DateTimeField::new('intradayAccessDate', 'Date')->setFormat('dd/MM/YYYY')->onlyOnIndex(),
            DateTimeField::new('lastConnexion', 'Dernière Connexion')->setFormat('dd/MM/YYYY - HH:mm')->hideOnForm(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $user = $entityInstance;
        $plainPassword = $user->getPassword();
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);

        if (!$entityInstance instanceof User) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        $formData = $request->request->all();

        $roles[] = 'ROLE_USER';
        if (!empty($formData['User']['isInvestisseur'])) {
            $roles[] = 'ROLE_INVESTISSEUR';
            $user->setInvestorAccessDate(new \DateTime());
        }
        if ($user->isInvestisseur() && !empty($formData['User']['isIntraday'])) {
            $roles[] = 'ROLE_INTRADAY';
            $user->setIntradayAccessDate(new \DateTime());
        }
        $user->setRoles($roles);

        $user->setPassword($hashedPassword);

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {

        if (!$entityInstance instanceof User) {
            return;
        }

        $user = $entityInstance;

        $roles = $user->getRoles();

        if (!$user->isInvestisseur()) {
            $user->setIsIntraday(false);
        }

        if ($user->isInvestisseur()) {
            $user->setInterestedInInvestorMethod(false);
            if ($user->getInvestorAccessDate() === null || !in_array('ROLE_INVESTISSEUR', $roles)) {
                $user->setInvestorAccessDate(new \DateTime());
            }
            if (!in_array('ROLE_INVESTISSEUR', $roles)) {
                $roles[] = 'ROLE_INVESTISSEUR';
            }
        } else {
            $roles = array_diff($roles, ['ROLE_INVESTISSEUR']);
            $user->setIntradayAccessDate(null);
        }

        if ($user->isIntraday()) {
            if ($user->getIntradayAccessDate() === null || !in_array('ROLE_INTRADAY', $roles)) {
                $user->setIntradayAccessDate(new \DateTime());
            }
            if (!in_array('ROLE_INTRADAY', $roles)) {
                $roles[] = 'ROLE_INTRADAY';
            }
        } else {
            $roles = array_diff($roles, ['ROLE_INTRADAY']);
            $user->setIntradayAccessDate(null);
        }
        $entityInstance->setRoles(array_unique(array_values($roles)));

        // Encoder le mot de passe s'il a été modifié
        $originalUser = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);

        if ($originalUser['password'] !== $user->getPassword()) {
            $encodedPassword = $this->passwordHasher->hashPassword(
                $entityInstance,
                $user->getPassword()
            );
            $entityInstance->setPassword($encodedPassword);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
