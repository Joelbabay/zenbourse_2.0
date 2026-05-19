<?php
// src/Controller/Admin/UserCrudController.php

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
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
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
use App\Form\SendEmailToUserType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class UserCrudController extends AbstractCrudController
{
    private $userRepository;
    private $entityRepository;

    public function __construct(
        OrmEntityRepository $entityRepository,
        UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private RequestStack $requestStack,
        private MailerInterface $mailer
    ) {
        $this->userRepository = $userRepository;
        $this->entityRepository = $entityRepository;
    }

    /**
     * Affiche le formulaire d'envoi d'email
     */
    public function sendEmailForm(AdminContext $context): Response
    {
        /** @var User $user */
        $user = $context->getEntity()->getInstance();

        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('admin');
        }

        $form = $this->createForm(SendEmailToUserType::class);
        $form->handleRequest($context->getRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                // Construire l'email
                $email = (new Email())
                    ->from('contact@zenbourse.fr')
                    ->to($user->getEmail())
                    ->subject($data['subject'])
                    ->html($this->renderEmailTemplate($user, $data['message']));

                // Envoyer
                $this->mailer->send($email);

                $this->addFlash('success', sprintf(
                    'Email envoyé avec succès à %s (%s)',
                    $user->getFullName(),
                    $user->getEmail()
                ));

                // Retourner à la liste
                return $this->redirectToRoute('admin', [
                    'crudAction' => 'index',
                    'crudControllerFqcn' => self::class,
                ]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
            }
        }

        return $this->render('admin/user/send_email.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * Template de l'email avec mise en forme
     */
    private function renderEmailTemplate(User $user, string $message): string
    {
        return sprintf(
            '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    background: linear-gradient(90deg, #0d6efd 0%%, #00c9a7 100%%);
                    color: white;
                    padding: 20px;
                    text-align: center;
                    border-radius: 5px 5px 0 0;
                }
                .content {
                    background: #f8f9fa;
                    padding: 30px;
                    border-radius: 0 0 5px 5px;
                }
                .message {
                    background: white;
                    padding: 20px;
                    border-radius: 5px;
                    margin: 20px 0;
                    white-space: pre-line;
                }
                .footer {
                    text-align: center;
                    margin-top: 20px;
                    font-size: 12px;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Zenbourse</h1>
                </div>
                <div class="content">
                    <div class="message">%s</div>
                    <p>Cordialement,<br>L\'équipe Zenbourse</p>
                </div>
                <div class="footer">
                    <p>Cet email a été envoyé depuis votre espace administrateur Zenbourse.</p>
                </div>
            </div>
        </body>
        </html>
    ',
            nl2br(htmlspecialchars($message))
        );
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Utilisateurs : création, suppression, accès aux méthodes')
            ->setPageTitle(Crud::PAGE_EDIT, 'Informations utilisateur')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer un utilisateur')
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setDefaultSort(['lastname' => 'ASC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        // Action pour envoyer un email
        $sendEmail = Action::new('sendEmail', false)
            ->linkToCrudAction('sendEmailForm')
            ->setIcon('fas fa-envelope')
            ->setCssClass('btn btn-link text-primary')
            ->setHtmlAttributes(['title' => 'Envoyer un email']);

        $toggleAction = Action::new('toggleBoolean', false)
            ->linkToCrudAction('toggleBoolean')
            ->setHtmlAttributes(['data-action' => 'toggle']);

        return $actions
            ->add(Crud::PAGE_INDEX, $toggleAction)
            ->add(Crud::PAGE_INDEX, $sendEmail)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-plus')->setLabel('Créer un utilisateur');
            })
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
            ->reorder(Crud::PAGE_INDEX, ['sendEmail', Action::EDIT, Action::DELETE]);
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
                        'title' => 'Veuillez entrer un numéro de téléphone valide.',
                    ]
                ]),
            TextField::new('city', 'Ville')
                ->formatValue(function ($value, $entity) {
                    return $value ?? ' ';
                }),

            // ACCÈS INVESTISSEUR
            BooleanField::new('isInvestisseur', 'Investisseur')
                ->renderAsSwitch()
                ->onlyOnIndex(),
            DateTimeField::new('investorAccessDate', 'Date')->setFormat('dd/MM/YYYY')->onlyOnIndex()
                ->formatValue(function ($value, $entity) {
                    $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
                    return $value ? $formatter->format($value) : ' ';
                }),

            // ← NOUVEAU : Compteur connexions Investisseur
            IntegerField::new('investorLoginCount', 'Nb. Cx.')
                ->addCssClass('text-center'),


            // ACCÈS INTRADAY
            BooleanField::new('isIntraday', 'Intraday')
                ->onlyOnIndex()
                ->renderAsSwitch(),
            DateTimeField::new('intradayAccessDate', 'Date')->setFormat('dd/MM/YYYY')->onlyOnIndex()
                ->formatValue(function ($value, $entity) {
                    $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
                    return $value ? $formatter->format($value) : ' ';
                }),

            // ← NOUVEAU : Compteur connexions Intraday
            // COMPTEUR CONNEXIONS INTRADAY
            //IntegerField::new('intradayLoginCount', 'Nb. Cx Int.')
            //->addCssClass('text-center'),

            // ACCÈS TEMPORAIRE
            BooleanField::new('hasTemporaryInvestorAccess', 'Accès temporaire Investisseur')
                ->onlyOnIndex()->renderAsSwitch(),

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

            // DERNIÈRE CONNEXION
            DateTimeField::new('lastConnexion', 'Dernière Connexion')->setFormat('dd/MM/YYYY - HH:mm')->hideOnForm()
                ->formatValue(function ($value, $entity) {
                    $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::RELATIVE_MEDIUM, \IntlDateFormatter::SHORT);
                    return $value ? $formatter->format($value) : ' ';
                }),

            // ← NOUVEAU : Compteur total connexions (dans les formulaires)
            IntegerField::new('loginCount', 'Nombre total de connexions')
                ->setHelp('Nombre total de connexions (tous accès confondus)')
                ->onlyOnForms()
                ->setFormTypeOption('disabled', true),

            IntegerField::new('investorLoginCount', 'Connexions Investisseur')
                ->setHelp('Nombre de connexions avec accès Investisseur actif')
                ->onlyOnForms()
                ->setFormTypeOption('disabled', true),

            IntegerField::new('intradayLoginCount', 'Connexions Intraday')
                ->setHelp('Nombre de connexions avec accès Intraday actif')
                ->onlyOnForms()
                ->setFormTypeOption('disabled', true),

            TextareaField::new('comment', 'Notes')
                ->formatValue(function ($value, $entity) {
                    return $value ?? ' ';
                }),
        ];
    }

    // ... vos méthodes persistEntity, updateEntity, toggleBoolean, configureAssets restent identiques ...

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

            $wasInvestor = $originalUser['isInvestisseur'] ?? false;
            if (!$wasInvestor && $user->isInvestisseur()) {
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

        $getter = $fieldName;
        $setter = 'set' . ucfirst($fieldName);

        if (!method_exists($user, $getter) || !method_exists($user, $setter)) {
            return $this->redirect($context->getReferrer() ?? $adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl());
        }

        $currentValue = $user->$getter();
        $newValue = !$currentValue;
        $user->$setter($newValue);

        $roles = $user->getRoles();

        if ($fieldName === 'isInvestisseur') {
            if ($newValue) {
                if (!$user->getInvestorAccessDate()) {
                    $user->setInvestorAccessDate(new \DateTime());
                }
                if (!in_array('ROLE_INVESTISSEUR', $roles)) {
                    $roles[] = 'ROLE_INVESTISSEUR';
                }
                $user->setInterestedInInvestorMethod(false);
            } else {
                $user->setIsIntraday(false);
                $user->setInvestorAccessDate(null);
                $user->setIntradayAccessDate(null);
                $roles = array_diff($roles, ['ROLE_INVESTISSEUR', 'ROLE_INTRADAY']);
            }
        }

        if ($fieldName === 'isIntraday') {
            if ($newValue) {
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
                        const investisseurInput = document.querySelector("[name*=\"[isInvestisseur]\"]");
                        const intradayInput = document.querySelector("[name*=\"[isIntraday]\"]");
                        
                        if (!investisseurInput || !intradayInput) return;
                        
                        function updateIntradayState() {
                            if (!investisseurInput.checked) {
                                intradayInput.disabled = true;
                                intradayInput.checked = false;
                                intradayInput.closest(".form-switch").style.opacity = "0.5";
                            } else {
                                intradayInput.disabled = false;
                                intradayInput.closest(".form-switch").style.opacity = "1";
                            }
                        }
                        
                        updateIntradayState();
                        investisseurInput.addEventListener("change", updateIntradayState);
                    });
                </script>
            ');
    }
}
