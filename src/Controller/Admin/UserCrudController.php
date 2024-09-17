<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Trait\NullValueFormatterTrait;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository as OrmEntityRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @method User getUser()
 */
class UserCrudController extends AbstractCrudController
{
    use NullValueFormatterTrait;
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
        //->overrideTemplates(['label/null' => 'admin/labels/null_label.html.twig']);
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

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(
                ChoiceFilter::new('statut', 'Statut')
                    ->setChoices([
                        'Prospect' => 'PROSPECT',
                        'Client' => 'CLIENT',
                        'Invité' => 'INVITE',
                    ])
            )
            ->add(
                ChoiceFilter::new('note', 'Catégorie (CAT)')
                    ->setChoices([
                        '1' => 1,
                        '2' => 2,
                        '3' => 3,
                        '4' => 4,
                        '5' => 5
                    ]) //->setFormTypeOption('multiple', true)
            );
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            ChoiceField::new('civility')
                ->setLabel('Civilité')
                ->setChoices([
                    'Mr' => 'Mr',
                    'Mme' => 'Mme',
                    'Mlle' => 'Mlle'
                ])->formatValue(function ($value, $entity) {
                    return $value ?? ' ';
                }),
            TextField::new('lastname', 'Nom'),
            TextField::new('firstname', 'Prénom'),
            TextField::new('email', 'E-mail'),
            TextField::new('password')->onlyOnForms(),
            ChoiceField::new('statut')
                ->renderAsBadges([
                    'INVITE' => 'warning',
                    'CLIENT' => 'success',
                    'PROSPECT' => 'info'
                ])
                ->setChoices([
                    'CLIENT' => 'CLIENT',
                    'INVITE' => 'INVITE',
                    'PROSPECT' => 'PROSPECT',
                ])
                ->allowMultipleChoices(false)->setRequired(true)->renderExpanded(),
            ChoiceField::new('note')
                ->setLabel('CAT')
                ->setChoices([
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4,
                    '5' => 5
                ])
                ->setHelp('Choisissez une note de 1 à 5')
                ->formatValue(function ($value, $entity) {
                    return $value ?? ' ';
                }),
            TelephoneField::new('phone', 'Téléphone')
                ->formatValue(function ($value, $entity) {
                    return $value ?? ' ';
                })
                ->setFormTypeOptions([
                    'attr' => [
                        'maxlength' => 15,
                        'pattern' => '^\+?[0-9]{8,15}$',
                        'title' => 'Veuillez entrer un numéro de téléphone valide (entre 8 et 15 chiffres, avec un code international optionnel).',
                    ]
                ]),
            TextField::new('city', 'Ville')
                ->formatValue(function ($value, $entity) {
                    return $value ?? ' ';
                }),
            BooleanField::new('isInvestisseur', 'Investisseur')->setFormTypeOptions([
                'mapped' => true,
                'attr' => [
                    'class' => 'js-investisseur-checkbox'
                ]
            ]),
            DateTimeField::new('investorAccessDate', 'Date')->setFormat('dd/MM/YYYY')->onlyOnIndex()
                ->formatValue(function ($value, $entity) {
                    $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
                    return $value ? $formatter->format($value) : ' ';
                }),
            BooleanField::new('isIntraday', 'Intraday')->setFormTypeOptions([
                'mapped' => true,
                'attr' => [
                    'class' => 'js-intraday-checkbox'
                ]
            ]),
            DateTimeField::new('intradayAccessDate', 'Date')->setFormat('dd/MM/YYYY')->onlyOnIndex()
                ->formatValue(function ($value, $entity) {
                    $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
                    return $value ? $formatter->format($value) : ' ';
                }),
            DateTimeField::new('lastConnexion', 'Dernière Connexion')->setFormat('dd/MM/YYYY - HH:mm')->hideOnForm()
                ->formatValue(function ($value, $entity) {
                    $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::RELATIVE_MEDIUM, \IntlDateFormatter::SHORT);
                    return $value ? $formatter->format($value) : ' ';
                }),
            TextareaField::new('comment', 'Commentaire')
                ->formatValue(function ($value, $entity) {
                    return $value ?? ' ';
                }),
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
        $formattedValue = $this->formatNullValue($entityInstance->getPhone());

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
