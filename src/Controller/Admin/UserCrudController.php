<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
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
use EasyCorp\Bundle\EasyAdminBundle\Filter\NullFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository as OrmEntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
            ->setFormThemes(['admin/fields/custom_boolean_toggle.html.twig'])
            ->setPageTitle(Crud::PAGE_INDEX, 'Utilisateurs : création, suppression, accès aux méthodes')
            ->setPageTitle(Crud::PAGE_EDIT, 'Informations utilisateur')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer un utilisateur')
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setDefaultSort(['lastname' => 'ASC'])
            ->showEntityActionsInlined();
        //->overrideTemplates(['label/null' => 'admin/labels/null_label.html.twig']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $toggleAction = Action::new('toggleBoolean', false)
            ->linkToCrudAction('toggleBoolean')
            ->setHtmlAttributes(['data-action' => 'toggle']);

        return $actions
            ->add(Crud::PAGE_INDEX, $toggleAction)
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
            ->add(TextFilter::new('lastname'))
            ->add(TextFilter::new('firstname'))
            ->add(
                ChoiceFilter::new('statut', 'Statut')
                    ->setChoices([
                        'PROSPECT' => 'PROSPECT',
                        'CLIENT' => 'CLIENT',
                        'INVITE' => 'INVITE',
                    ])
            )
            ->add(
                BooleanFilter::new('hasTemporaryInvestorAccess')
                    ->setLabel('Accès temporaire')
                    ->setFormTypeOption('choices', [
                        'Actif' => true,
                        'Expiré' => false,
                    ])
            )

            ->add(
                NullFilter::new('temporaryInvestorAccessStart')
                    ->setChoiceLabels('NULL', 'NOT_NULL')
            )

            ->add(
                BooleanFilter::new('isInvestisseur', 'Accès investisseur')
            )
            ->add(
                BooleanFilter::new('isIntraday', 'Accès intraday')
            )

        ;
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

            // Champs booléens pour le formulaire
            BooleanField::new('isInvestisseur', 'Accès Investisseur')
                ->onlyOnForms()
                ->setFormTypeOptions([
                    'attr' => ['class' => 'js-investisseur-checkbox']
                ]),
            BooleanField::new('isIntraday', 'Accès Intraday')
                ->onlyOnForms()
                ->setFormTypeOptions([
                    'attr' => ['class' => 'js-intraday-checkbox']
                ]),

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
                ->setLabel('Cat.')
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
            BooleanField::new('isInvestisseur', 'Investisseur')
                ->renderAsSwitch()
                ->onlyOnIndex(),
            DateTimeField::new('investorAccessDate', 'Date')->setFormat('dd/MM/YYYY')->onlyOnIndex()
                ->formatValue(function ($value, $entity) {
                    $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
                    return $value ? $formatter->format($value) : ' ';
                }),
            BooleanField::new('isIntraday', 'Intraday')
                //->setFormTypeOption('disabled',  fn($user) => !$user || !$user->isInvestisseur())
                ->onlyOnIndex()
                ->renderAsSwitch(),
            //->setTemplatePath('admin/fields/custom_boolean_toggle.html.twig'),
            DateTimeField::new('intradayAccessDate', 'Date')->setFormat('dd/MM/YYYY')->onlyOnIndex()
                ->formatValue(function ($value, $entity) {
                    $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
                    return $value ? $formatter->format($value) : ' ';
                }),

            // Badge custom pour accès temporaire actif ou expiré
            TextField::new('badgeTemporaryInvestorAccess', 'Accès temporaire')
                ->onlyOnIndex()
                ->formatValue(function ($value, $entity) {
                    $start = $entity->getTemporaryInvestorAccessStart();
                    $hasTemp = $entity->getHasTemporaryInvestorAccess();
                    if ($start) {
                        $startDate = new \DateTime($start->format('Y-m-d H:i:s'));
                        $dateEnd = $startDate->add(new \DateInterval('P10D'));
                        $now = new \DateTime();
                        if ($hasTemp && $now <= $dateEnd) {
                            return '<span class="badge bg-warning text-dark fw-bold">Accès temporaire actif<br><small>Expire le ' . $dateEnd->format('d/m/Y') . '</small></span>';
                        } else {
                            return '<span class="badge bg-danger text-white fw-bold">Accès temporaire expiré<br><small>Le ' . $dateEnd->format('d/m/Y') . '</small></span>';
                        }
                    }
                    return ' ';
                })
                ->renderAsHtml(),

            DateTimeField::new('temporaryInvestorAccessStart', 'Début accès temporaire')
                ->setFormat('dd/MM/YYYY HH:mm')
                ->onlyOnIndex()
                ->formatValue(function ($value, $entity) {
                    return $value ? $value->format('d/m/Y H:i') : ' ';
                }),

            BooleanField::new('hasTemporaryInvestorAccess', 'Accès temporaire Investisseur')
                ->renderAsSwitch(true)
                ->setHelp('Activez pour donner un accès temporaire de 10 jours à la méthode Investisseur')
                ->onlyOnForms(),

            DateTimeField::new('temporaryInvestorAccessStart', 'Début accès temporaire')
                ->setFormat('dd/MM/YYYY HH:mm')
                ->onlyOnForms(),

            DateTimeField::new('lastConnexion', 'Dernière Connexion')->setFormat('dd/MM/YYYY - HH:mm')->hideOnForm()
                ->formatValue(function ($value, $entity) {
                    $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::RELATIVE_MEDIUM, \IntlDateFormatter::SHORT);
                    return $value ? $formatter->format($value) : ' ';
                }),
            TextareaField::new('comment', 'Notes')
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

        if ($user->getHasTemporaryInvestorAccess() && !$user->getTemporaryInvestorAccessStart()) {
            $user->setTemporaryInvestorAccessStart(new \DateTime());
        }
        if (!$user->getHasTemporaryInvestorAccess()) {
            $user->setTemporaryInvestorAccessStart(null);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {

        if (!$entityInstance instanceof User) {
            return;
        }

        $user = $entityInstance;
        $roles = $user->getRoles();
        $originalUser = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);

        if (!$user->isInvestisseur()) {
            $user->setIsIntraday(false);
        }

        // --- INVESTISSEUR ---
        if (!$user->isInvestisseur()) {
            $user->setIsIntraday(false);
            $roles = array_diff($roles, ['ROLE_INVESTISSEUR']);
            $user->setIntradayAccessDate(null);
        } else {
            $user->setInterestedInInvestorMethod(false);

            // Détecter un changement de false -> true
            $wasInvestor = $originalUser['isInvestisseur'] ?? false;
            if (!$wasInvestor && $user->isInvestisseur()) {
                // Mise à jour de la date à chaque activation
                $user->setInvestorAccessDate(new \DateTime());
            }

            if (!in_array('ROLE_INVESTISSEUR', $roles)) {
                $roles[] = 'ROLE_INVESTISSEUR';
            }
        }

        // --- INTRADAY ---
        if ($user->isIntraday()) {
            $wasIntraday = $originalUser['isIntraday'] ?? false;
            if (!$wasIntraday && $user->isIntraday()) {
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

        // --- MOT DE PASSE ---
        if (($originalUser['password'] ?? null) !== $user->getPassword()) {
            $encodedPassword = $this->passwordHasher->hashPassword($entityInstance, $user->getPassword());
            $entityInstance->setPassword($encodedPassword);
        }

        // --- ACCÈS TEMPORAIRE ---
        if ($user->getHasTemporaryInvestorAccess()) {
            $user->setTemporaryInvestorAccessStart(new \DateTime());
        } else {
            $user->setTemporaryInvestorAccessStart(null);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function toggleBoolean(AdminContext $context, EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator): Response
    {
        /** @var User $user */
        $user = $context->getEntity()->getInstance();
        $fieldName = $context->getRequest()->query->get('field');

        if (!$user || !$fieldName) {
            return $this->redirect($adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl());
        }

        // Construire directement les méthodes avec le nom du champ tel quel
        $getter = $fieldName; // isInvestisseur
        $setter = 'set' . ucfirst($fieldName); // setIsInvestisseur

        if (!method_exists($user, $getter) || !method_exists($user, $setter)) {
            return $this->redirect($context->getReferrer() ?? $adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl());
        }

        // Inverser la valeur
        $currentValue = $user->$getter();
        $newValue = !$currentValue;
        $user->$setter($newValue);

        // Gérer les rôles
        $roles = $user->getRoles();

        // Gestion spéciale pour Investisseur
        if ($fieldName === 'isInvestisseur') {
            if ($newValue) {
                // Activer investisseur
                if (!$user->getInvestorAccessDate()) {
                    $user->setInvestorAccessDate(new \DateTime());
                }
                if (!in_array('ROLE_INVESTISSEUR', $roles)) {
                    $roles[] = 'ROLE_INVESTISSEUR';
                }
                $user->setInterestedInInvestorMethod(false);
            } else {
                // Désactiver investisseur (et intraday)
                $user->setIsIntraday(false);
                $user->setInvestorAccessDate(null);
                $user->setIntradayAccessDate(null);
                $roles = array_diff($roles, ['ROLE_INVESTISSEUR', 'ROLE_INTRADAY']);
            }
        }

        // Gestion spéciale pour Intraday
        if ($fieldName === 'isIntraday') {
            if ($newValue) {
                // Activer intraday (nécessite investisseur)
                if (!$user->isInvestisseur()) {
                    return $this->redirect($context->getReferrer() ?? $adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl());
                }
                if (!$user->getIntradayAccessDate()) {
                    $user->setIntradayAccessDate(new \DateTime());
                }
                if (!in_array('ROLE_INTRADAY', $roles)) {
                    $roles[] = 'ROLE_INTRADAY';
                }
            } else {
                // Désactiver intraday
                $user->setIntradayAccessDate(null);
                $roles = array_diff($roles, ['ROLE_INTRADAY']);
            }
        }

        $user->setRoles(array_unique(array_values($roles)));
        $entityManager->flush();

        return $this->redirect($context->getReferrer() ?? $adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl());
    }
    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addHtmlContentToHead('
                 <script>
                document.addEventListener("DOMContentLoaded", function() {
                    // Pour la page INDEX (liste des utilisateurs)
                    setTimeout(function() {
                        const rows = document.querySelectorAll("tbody tr");
                        
                        rows.forEach(function(row) {
                            const switches = row.querySelectorAll(".form-check-input");
                            
                            let investisseurSwitch = null;
                            let intradaySwitch = null;
                            
                            switches.forEach(function(sw) {
                                const url = sw.getAttribute("data-toggle-url");
                                if (url && url.includes("fieldName=isInvestisseur")) {
                                    investisseurSwitch = sw;
                                } else if (url && url.includes("fieldName=isIntraday")) {
                                    intradaySwitch = sw;
                                }
                            });
                            
                            if (!investisseurSwitch || !intradaySwitch) return;
                            
                            function updateIntradayState() {
                                if (!investisseurSwitch.checked) {
                                    intradaySwitch.disabled = true;
                                    intradaySwitch.checked = false;
                                    
                                    const container = intradaySwitch.closest(".form-check");
                                    if (container) {
                                        container.style.opacity = "0.5";
                                        container.style.cursor = "not-allowed";
                                        container.style.pointerEvents = "none";
                                    }
                                } else {
                                    intradaySwitch.disabled = false;
                                    
                                    const container = intradaySwitch.closest(".form-check");
                                    if (container) {
                                        container.style.opacity = "1";
                                        container.style.cursor = "pointer";
                                        container.style.pointerEvents = "auto";
                                    }
                                }
                            }
                            
                            updateIntradayState();
                            investisseurSwitch.addEventListener("change", function() {
                                setTimeout(updateIntradayState, 100);
                            });
                        });
                    }, 500);
                    
                    // Pour la page EDIT/NEW (formulaire)
                    setTimeout(function() {
                        const investisseurInput = document.querySelector("input[name*=\"[isInvestisseur]\"]");
                        const intradayInput = document.querySelector("input[name*=\"[isIntraday]\"]");
                        
                        if (!investisseurInput || !intradayInput) return;
                        
                        function updateFormIntradayState() {
                            if (!investisseurInput.checked) {
                                intradayInput.disabled = true;
                                intradayInput.checked = false;
                                
                                const container = intradayInput.closest(".form-check");
                                if (container) {
                                    container.style.opacity = "0.5";
                                    container.style.cursor = "not-allowed";
                                }
                            } else {
                                intradayInput.disabled = false;
                                
                                const container = intradayInput.closest(".form-check");
                                if (container) {
                                    container.style.opacity = "1";
                                    container.style.cursor = "pointer";
                                }
                            }
                        }
                        
                        updateFormIntradayState();
                        investisseurInput.addEventListener("change", updateFormIntradayState);
                    }, 500);
                });
            </script>
            ');
    }
}
