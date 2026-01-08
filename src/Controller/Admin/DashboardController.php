<?php

namespace App\Controller\Admin;

use App\Entity\CarouselImage;
use App\Entity\Contact;
use App\Entity\Download;
use App\Entity\IntradayRequest;
use App\Entity\InvestisseurRequest;
use App\Entity\Menu;
use App\Entity\PageContent;
use App\Entity\User;
use App\Entity\StockExample;
use App\Form\MailingType;
use App\Repository\ContactRepository;
use App\Repository\IntradayRequestRepository;
use App\Repository\InvestisseurRequestRepository;
use App\Repository\UserRepository;
use App\Repository\StockExampleRepository;
use App\Service\EmailService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private UserRepository $userRepository,
        private ContactRepository $contactRepository,
        private InvestisseurRequestRepository $investisseurRequestRepository,
        private IntradayRequestRepository $intradayRequestRepository,
        private StockExampleRepository $stockExampleRepository,
        private EmailService $emailService,
        private AdminUrlGenerator $adminUrlGenerator,
        private RequestStack $requestStack
    ) {}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Si l'action demandée est "mailing", rediriger vers la méthode mailing
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $crudAction = $request->query->get('crudAction');
            if ($crudAction === 'mailing') {
                return $this->mailing($request);
            }
        }

        // Récupérer les statistiques
        $stats = [
            'total_users' => $this->userRepository->count([]),
            'investisseur_subscribers' => $this->investisseurRequestRepository->count([]),
            'intraday_subscribers' => $this->intradayRequestRepository->count([]),
            'total_contacts' => $this->contactRepository->count([]),
            'recent_contacts' => $this->contactRepository->findBy([], ['createdAt' => 'DESC'], 5),
            'recent_users' => $this->userRepository->findBy([], ['createdAt' => 'DESC'], 5),
        ];

        $statuts = ['PROSPECT', 'INVITE', 'CLIENT'];
        $methods = ['investisseur', 'intraday'];
        $statutStats = [];

        $unreadContacts = $this->contactRepository->count(['isRead' => false]);

        foreach ($statuts as $statut) {
            $statutStats[$statut] = [
                'total' => $this->userRepository->countByStatut($statut),
                'investisseur' => $this->userRepository->countByStatutAndMethod($statut, 'investisseur'),
                'intraday' => $this->userRepository->countByStatutAndMethod($statut, 'intraday'),
            ];
        }

        // Calcul du nombre d'utilisateurs avec accès temporaire investisseur actif
        $usersWithTempAccess = $this->userRepository->createQueryBuilder('u')
            ->select('u')
            ->where('u.temporaryInvestorAccessStart IS NOT NULL')
            ->getQuery()
            ->getResult();

        $totalTemporaryInvestorAccess = 0;
        $totalTemporaryInvestorAccessActive = 0;
        $totalTemporaryInvestorAccessExpired = 0;
        $now = new \DateTime();

        foreach ($usersWithTempAccess as $user) {
            $end = (clone $user->getTemporaryInvestorAccessStart())->modify('+10 days');
            if ($user->getHasTemporaryInvestorAccess() && $now <= $end) {
                $totalTemporaryInvestorAccessActive++;
            } else {
                $totalTemporaryInvestorAccessExpired++;
            }
        }

        $totalTemporaryInvestorAccess = $totalTemporaryInvestorAccessActive + $totalTemporaryInvestorAccessExpired;

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'statutStats' => $statutStats,
            'unreadContacts' => $unreadContacts,
            'totalTemporaryInvestorAccessActive' => $totalTemporaryInvestorAccessActive,
            'totalTemporaryInvestorAccessExpired' => $totalTemporaryInvestorAccessExpired,
            'totalTemporaryInvestorAccess' => $totalTemporaryInvestorAccess,
        ]);
    }

    public function mailing(Request $request): Response
    {
        $form = $this->createForm(MailingType::class);
        $form->handleRequest($request);

        $recipientCount = 0;
        $sendResults = null;

        if ($form->isSubmitted()) {
            // Afficher les erreurs de validation pour le débogage
            if (!$form->isValid()) {
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                $this->addFlash('error', 'Erreurs de validation : ' . implode(', ', $errors));
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $subject = $data['subject'];
            $textContent = $data['textContent'] ?? '';

            // Convertir le HTML de CKEditor en texte brut
            if (!empty($textContent)) {
                $textContent = strip_tags($textContent);
                $textContent = html_entity_decode($textContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                // Nettoyer les espaces multiples et les sauts de ligne
                $textContent = preg_replace('/\s+/', ' ', $textContent);
                $textContent = trim($textContent);
            }

            // Vérifier que le contenu n'est pas vide après nettoyage
            if (empty($textContent)) {
                $this->addFlash('error', 'Le contenu du message ne peut pas être vide');
                $form = $this->createForm(MailingType::class, $data);
            } else {
                $recipientTypes = $data['recipientType'];

                // Normaliser en tableau si ce n'est pas déjà le cas
                if (!is_array($recipientTypes)) {
                    $recipientTypes = [$recipientTypes];
                }

                // Vérifier si "test" ou "all" est sélectionné (ne peut pas être combiné avec d'autres)
                if (in_array('test', $recipientTypes) && count($recipientTypes) > 1) {
                    $this->addFlash('error', 'L\'option "Email spécifique (test)" ne peut pas être combinée avec d\'autres options');
                    $form = $this->createForm(MailingType::class, $data);
                } elseif (in_array('all', $recipientTypes) && count($recipientTypes) > 1) {
                    $this->addFlash('error', 'L\'option "Tous les utilisateurs" ne peut pas être combinée avec d\'autres options');
                    $form = $this->createForm(MailingType::class, $data);
                } elseif (in_array('test', $recipientTypes)) {
                    // Envoi de test à un email spécifique
                    $testEmail = $data['testEmail'] ?? null;
                    if ($testEmail) {
                        // Créer un utilisateur temporaire pour le test
                        $testUser = new User();
                        $testUser->setEmail($testEmail);
                        $testUser->setFirstname('Test');
                        $testUser->setLastname('User');

                        // Envoyer uniquement en texte (pas de HTML)
                        try {
                            $result = $this->emailService->sendToUser($testUser, $subject, null, $textContent);
                            if ($result) {
                                $this->addFlash('success', 'Email de test envoyé avec succès à ' . $testEmail);
                            } else {
                                $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email de test. Vérifiez la configuration SMTP dans les logs.');
                            }
                        } catch (\Exception $e) {
                            $errorMessage = $e->getMessage();
                            // Messages d'erreur plus clairs
                            if (strpos($errorMessage, 'authentication') !== false || strpos($errorMessage, '535') !== false) {
                                $this->addFlash('error', 'Erreur d\'authentification SMTP. Vérifiez vos identifiants de messagerie dans la configuration (MAILER_DSN).');
                            } elseif (strpos($errorMessage, 'connection') !== false || strpos($errorMessage, 'timeout') !== false) {
                                $this->addFlash('error', 'Erreur de connexion au serveur SMTP. Vérifiez votre connexion internet et la configuration du serveur.');
                            } else {
                                $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email : ' . $errorMessage);
                            }
                        }
                    } else {
                        $this->addFlash('error', 'Veuillez saisir un email de test');
                    }
                } else {
                    // Récupérer les utilisateurs pour tous les types sélectionnés
                    $users = $this->getUsersForRecipientTypes($recipientTypes);
                    $recipientCount = count($users);

                    if ($recipientCount > 0) {
                        // Envoyer les emails uniquement en texte (pas de HTML)
                        $sendResults = $this->emailService->sendToUsers($users, $subject, null, $textContent);

                        if ($sendResults['success'] > 0) {
                            $this->addFlash('success', sprintf(
                                'Emails envoyés avec succès : %d/%d',
                                $sendResults['success'],
                                $recipientCount
                            ));
                        }

                        if ($sendResults['failed'] > 0) {
                            $this->addFlash('warning', sprintf(
                                '%d email(s) n\'ont pas pu être envoyés',
                                $sendResults['failed']
                            ));
                        }
                    } else {
                        $this->addFlash('warning', 'Aucun destinataire trouvé avec les critères sélectionnés');
                    }
                }

                // Réinitialiser le formulaire après envoi
                $form = $this->createForm(MailingType::class);
            }
        } else {
            // Compter les destinataires pour l'affichage
            if ($form->isSubmitted()) {
                $data = $form->getData();
                $recipientTypes = $data['recipientType'] ?? ['all'];
                if (!is_array($recipientTypes)) {
                    $recipientTypes = [$recipientTypes];
                }
                // Exclure "test" et "all" du comptage si combinés avec d'autres
                if (in_array('test', $recipientTypes) || (in_array('all', $recipientTypes) && count($recipientTypes) > 1)) {
                    $recipientCount = 0;
                } else {
                    $users = $this->getUsersForRecipientTypes($recipientTypes);
                    $recipientCount = count($users);
                }
            }
        }

        return $this->render('admin/mailing/index.html.twig', [
            'form' => $form,
            'recipientCount' => $recipientCount,
            'sendResults' => $sendResults,
        ]);
    }

    #[Route('/admin/mailing/count', name: 'admin_mailing_count', methods: ['POST'])]
    public function countRecipients(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $recipientTypes = $data['recipientTypes'] ?? [];

        if (empty($recipientTypes)) {
            return $this->json(['count' => 0]);
        }

        // Normaliser en tableau si ce n'est pas déjà le cas
        if (!is_array($recipientTypes)) {
            $recipientTypes = [$recipientTypes];
        }

        // Exclure "test" du comptage
        if (in_array('test', $recipientTypes)) {
            return $this->json(['count' => 0]);
        }

        // Récupérer les utilisateurs pour tous les types sélectionnés
        $users = $this->getUsersForRecipientTypes($recipientTypes);
        $count = count($users);

        return $this->json(['count' => $count]);
    }

    private function getFiltersForRecipientType(string $recipientType): ?array
    {
        return match ($recipientType) {
            'all' => null,
            'client' => ['statut' => 'CLIENT'],
            'prospect' => ['statut' => 'PROSPECT'],
            'invite' => ['statut' => 'INVITE'],
            'investisseur' => ['isInvestisseur' => true],
            'intraday' => ['isIntraday' => true],
            'temporary_active' => ['hasTemporaryInvestorAccess' => true],
            default => null,
        };
    }

    /**
     * Récupère les utilisateurs pour plusieurs types de destinataires (combinaison)
     */
    private function getUsersForRecipientTypes(array $recipientTypes): array
    {
        $allUsers = [];
        $userIds = [];

        foreach ($recipientTypes as $recipientType) {
            if ($recipientType === 'all') {
                // Si "all" est sélectionné, retourner tous les utilisateurs
                return $this->userRepository->findAll();
            }

            $filters = $this->getFiltersForRecipientType($recipientType);
            $users = $this->emailService->getUsersByFilters($filters);

            // Ajouter les utilisateurs en évitant les doublons
            foreach ($users as $user) {
                $userId = $user->getId();
                if (!isset($userIds[$userId])) {
                    $allUsers[] = $user;
                    $userIds[$userId] = true;
                }
            }
        }

        return $allUsers;
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Zenbourse');
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            ->setPaginatorPageSize(15);
    }

    public function configureAssets(): Assets
    {
        $ckeditorLicenseKey = $_ENV['CKEDITOR_LICENSE_KEY'];
        return Assets::new()
            ->addCssFile('/css/admin.css')
            ->addHtmlContentToBody('<script>document.documentElement.setAttribute(\'data-turbo\', \'false\');</script>')
            ->addHtmlContentToHead('<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/45.0.0/ckeditor5.css" />')
            ->addHtmlContentToHead('<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css"/>')
            ->addHtmlContentToHead('<script src="https://cdn.ckeditor.com/ckeditor5/45.0.0/ckeditor5.umd.js"></script>')
            ->addHtmlContentToHead('<script src="https://cdn.ckeditor.com/ckeditor5/46.0.1/translations/fr.umd.js"></script>"')
            ->addHtmlContentToHead('<script src="https://cdn.ckeditor.com/ckeditor5-premium-features/46.0.1/translations/fr.umd.js"></script>"')
            ->addHtmlContentToBody("
            <script>
                const {
                    ClassicEditor,
                    Alignment,
                    Autoformat,
                    AutoImage,
                    Autosave,
                    Base64UploadAdapter,
                    BlockQuote,
                    Bold,
                    Bookmark,
                    Code,
                    CodeBlock,
                    Emoji,
                    Essentials,
                    FindAndReplace,
                    FontBackgroundColor,
                    FontColor,
                    FontFamily,
                    FontSize,
                    FullPage,
                    Fullscreen,
                    GeneralHtmlSupport,
                    Heading,
                    Highlight,
                    HorizontalLine,
                    HtmlComment,
                    HtmlEmbed,
                    ImageBlock,
                    ImageCaption,
                    ImageEditing,
                    ImageInline,
                    ImageInsert,
                    ImageInsertViaUrl,
                    ImageResize,
                    ImageStyle,
                    ImageTextAlternative,
                    ImageToolbar,
                    ImageUpload,
                    ImageUtils,
                    Indent,
                    IndentBlock,
                    Italic,
                    Link,
                    LinkImage,
                    List,
                    ListProperties,
                    MediaEmbed,
                    Mention,
                    PageBreak,
                    Paragraph,
                    PasteFromMarkdownExperimental,
                    PasteFromOffice,
                    RemoveFormat,
                    ShowBlocks,
                    SimpleUploadAdapter,
                    SourceEditing,
                    SpecialCharacters,
                    SpecialCharactersArrows,
                    SpecialCharactersCurrency,
                    SpecialCharactersEssentials,
                    SpecialCharactersLatin,
                    SpecialCharactersMathematical,
                    SpecialCharactersText,
                    Strikethrough,
                    Style,
                    Subscript,
                    Superscript,
                    Table,
                    TableCaption,
                    TableCellProperties,
                    TableColumnResize,
                    TableLayout,
                    TableProperties,
                    TableToolbar,
                    TextTransformation,
                    TodoList,
                    Underline,
                    WordCount
                } = CKEDITOR;
                 
                 // Initialiser CKEditor pour tous les éléments .ckeditor
                 document.querySelectorAll( '.ckeditor' ).forEach( function( element ) {
                    ClassicEditor
                    .create( element, {
                        licenseKey: '{$ckeditorLicenseKey}',
                        language: 'fr',
                        plugins: [ Alignment, Autoformat,
                            AutoImage,
                            Autosave,
                            Base64UploadAdapter,
                            BlockQuote,
                            Bold,
                            Bookmark,
                            Code,
                            CodeBlock,
                            Emoji,
                            Essentials,
                            FindAndReplace,
                            FontBackgroundColor,
                            FontColor,
                            FontFamily,
                            FontSize,
                            FullPage,
                            Fullscreen,
                            GeneralHtmlSupport,
                            Heading,
                            Highlight,
                            HorizontalLine,
                            HtmlComment,
                            HtmlEmbed,
                            ImageBlock,
                            ImageCaption,
                            ImageEditing,
                            ImageInline,
                            ImageInsert,
                            ImageInsertViaUrl,
                            ImageResize,
                            ImageStyle,
                            ImageTextAlternative,
                            ImageToolbar,
                            ImageUpload,
                            ImageUtils,
                            Indent,
                            IndentBlock,
                            Italic,
                            Link,
                            LinkImage,
                            List,
                            ListProperties,
                            MediaEmbed,
                            Mention,
                            PageBreak,
                            Paragraph,
                            PasteFromMarkdownExperimental,
                            PasteFromOffice,
                            RemoveFormat,
                            ShowBlocks,
                            SimpleUploadAdapter,
                            SourceEditing,
                            SpecialCharacters,
                            SpecialCharactersArrows,
                            SpecialCharactersCurrency,
                            SpecialCharactersEssentials,
                            SpecialCharactersLatin,
                            SpecialCharactersMathematical,
                            SpecialCharactersText,
                            Strikethrough,
                            Style,
                            Subscript,
                            Superscript,
                            Table,
                            TableCaption,
                            TableCellProperties,
                            TableColumnResize,
                            TableLayout,
                            TableProperties,
                            TableToolbar,
                            TextTransformation,
                            TodoList,
                            Underline,
                            WordCount ],
                        toolbar: {
                            items: [
                                'sourceEditing',
                                'showBlocks',
                                'findAndReplace',
                                'fullscreen',
                                '|',
                                'heading',
                                'style',
                                '|',
                                'fontSize',
                                'fontFamily',
                                'fontColor',
                                'fontBackgroundColor',
                                '|',
                                'bold',
                                'italic',
                                'underline',
                                'strikethrough',
                                'subscript',
                                'superscript',
                                'code',
                                'removeFormat',
                                '|',
                                'emoji',
                                'specialCharacters',
                                'horizontalLine',
                                'pageBreak',
                                'link',
                                'bookmark',
                                'insertImage',
                                'insertImageViaUrl',
                                'mediaEmbed',
                                'insertTable',
                                'insertTableLayout',
                                'highlight',
                                'blockQuote',
                                'codeBlock',
                                'htmlEmbed',
                                '|',
                                'alignment',
                                '|',
                                'bulletedList',
                                'numberedList',
                                'todoList',
                                'outdent',
                                'indent'
                            ],
                            shouldNotGroupWhenFull: true,
                        },
                        fullscreen: {
                            onEnterCallback: container =>
                                container.classList.add(
                                    'editor-container',
                                    'editor-container_classic-editor',
                                    'editor-container_include-style',
                                    'editor-container_include-word-count',
                                    'editor-container_include-fullscreen',
                                    'main-container'
                                )
                        },
                        fontFamily: {
                            options: [
                                    'default', 
                                    'Arial, Helvetica',
                                    'Calibri, sans-serif',
                                    'Georgia, serif',
                                    'Sans-serif',
                                    'Impact',
                                    'Segoe UI',
                                    'Verdana',
                                    'Ubuntu, Arial, sans-serif',
                                    'Ubuntu Mono, Courier New, Courier, monospace'
                                ],
                            supportAllValues: true
                        },
                        fontSize: {
                            options: [10, 12, 14, 'default', 18, 20, 21, 22, 23, 24, 26, 28, 30, 32, 35, 40, 45],
                            supportAllValues: true
                        },
                        heading: {
                            options: [
                                {
                                    model: 'paragraph',
                                    title: 'Paragraph',
                                    class: 'ck-heading_paragraph'
                                },
                                {
                                    model: 'heading1',
                                    view: 'h1',
                                    title: 'Heading 1',
                                    class: 'ck-heading_heading1'
                                },
                                {
                                    model: 'heading2',
                                    view: 'h2',
                                    title: 'Heading 2',
                                    class: 'ck-heading_heading2'
                                },
                                {
                                    model: 'heading3',
                                    view: 'h3',
                                    title: 'Heading 3',
                                    class: 'ck-heading_heading3'
                                },
                                {
                                    model: 'heading4',
                                    view: 'h4',
                                    title: 'Heading 4',
                                    class: 'ck-heading_heading4'
                                },
                                {
                                    model: 'heading5',
                                    view: 'h5',
                                    title: 'Heading 5',
                                    class: 'ck-heading_heading5'
                                },
                                {
                                    model: 'heading6',
                                    view: 'h6',
                                    title: 'Heading 6',
                                    class: 'ck-heading_heading6'
                                }
                            ]
                        },
                        htmlSupport: {
                            allow: [
                                {
                                    name: /^.*$/,
                                    styles: true,
                                    attributes: true,
                                    classes: true
                                }
                            ]
                        },
                        image: {
                            toolbar: [
                                'toggleImageCaption',
                                'imageTextAlternative',
                                '|',
                                'imageStyle:inline',
                                'imageStyle:wrapText',
                                'imageStyle:breakText',
                                '|',
                                'resizeImage'
                            ]
                        },
                        link: {
                            addTargetToExternalLinks: true,
                            defaultProtocol: 'https://',
                            decorators: {
                                toggleDownloadable: {
                                    mode: 'manual',
                                    label: 'Downloadable',
                                    attributes: {
                                        download: 'file'
                                    }
                                }
                            }
                        },
                        list: {
                            properties: {
                                styles: true,
                                startIndex: true,
                                reversed: true
                            }
                        },
                        mention: {
                            feeds: [
                                {
                                    marker: '@',
                                    feed: [
                                        /* See: https://ckeditor.com/docs/ckeditor5/latest/features/mentions.html */
                                    ]
                                }
                            ]
                        },
                        menuBar: {
                            isVisible: true
                        },
                        placeholder: 'Type or paste your content here!',
                        style: {
                            definitions: [
                                {
                                    name: 'Article category',
                                    element: 'h3',
                                    classes: ['category']
                                },
                                {
                                    name: 'Title',
                                    element: 'h2',
                                    classes: ['document-title']
                                },
                                {
                                    name: 'Subtitle',
                                    element: 'h3',
                                    classes: ['document-subtitle']
                                },
                                {
                                    name: 'Info box',
                                    element: 'p',
                                    classes: ['info-box']
                                },
                                {
                                    name: 'CTA Link Primary',
                                    element: 'a',
                                    classes: ['button', 'button--green']
                                },
                                {
                                    name: 'CTA Link Secondary',
                                    element: 'a',
                                    classes: ['button', 'button--black']
                                },
                                {
                                    name: 'Marker',
                                    element: 'span',
                                    classes: ['marker']
                                },
                                {
                                    name: 'Spoiler',
                                    element: 'span',
                                    classes: ['spoiler']
                                }
                            ]
                        },
                        table: {
                            contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells', 'tableProperties', 'tableCellProperties']
                        },
                        simpleUpload: {
                            uploadUrl: '/upload-image',
                            headers: {
                                'X-CSRF-TOKEN': 'CSRF-Token' 
                            }
                        }
                    } )
                    .then( editor => {
                        // Stocker l'instance de l'éditeur sur l'élément pour y accéder plus tard
                        element.ckeditorInstance = editor;
                    } )
                    .catch( error => {
                        console.error( error );
                    } );
                 } );
            </script>");
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::linkToCrud('Messagerie', 'fa fa-inbox', Contact::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user-friends', User::class)->setController(UserCrudController::class);
        yield MenuItem::linkToCrud('Téléchargement liste des valeurs 2020', 'fa fa-download', Download::class)
            ->setController(UserDownloadCrudController::class);
        yield MenuItem::linkToCrud('Méthode Investisseur', 'fa fa-star', InvestisseurRequest::class)
            ->setController(InterestedUsersCrudController::class);
        yield MenuItem::linkToCrud('Méthode Intraday', 'fa fa-star', IntradayRequest::class)
            ->setController(IntradayRequestCrudController::class);

        yield MenuItem::subMenu('Liens de navigation', 'fa fa-list')->setSubItems([
            MenuItem::linkToCrud('ACCUEIL', 'fa fa-home', Menu::class)
                ->setQueryParameter('section', 'HOME'),
            MenuItem::linkToCrud('INVESTISSEUR', 'fa fa-chart-line', Menu::class)
                ->setQueryParameter('section', 'INVESTISSEUR'),
            MenuItem::linkToCrud('INTRADAY', 'fa fa-chart-line', Menu::class)
                ->setQueryParameter('section', 'INTRADAY'),
        ]);
        yield MenuItem::subMenu('Gestion des pages', 'fa fa-edit')->setSubItems([
            MenuItem::linkToCrud('ACCUEIL', 'fa fa-home', PageContent::class)
                ->setQueryParameter('section', 'HOME'),
            MenuItem::linkToCrud('INVESTISSEUR', 'fa fa-chart-line', PageContent::class)
                ->setQueryParameter('section', 'INVESTISSEUR'),
            MenuItem::linkToCrud('INTRADAY', 'fa fa-chart-line', PageContent::class)
                ->setQueryParameter('section', 'INTRADAY'),
        ]);

        yield MenuItem::linkToCrud('Images du carrousel', 'fa fa-images', CarouselImage::class);
        yield MenuItem::linkToCrud('Gestion des pages de la bibliothèque', 'fa fa-chart-line', StockExample::class)
            ->setController(StockExampleCrudController::class);

        // Mailing
        yield MenuItem::linkToUrl(
            'Envoi d\'emails',
            'fas fa-envelope',
            $this->adminUrlGenerator->setController(self::class)->setAction('mailing')->generateUrl()
        );

        // Lien vers le site en bas du menu
        yield MenuItem::linkToUrl('Voir le site', 'fas fa-external-link-alt', '/')
            ->setLinkRel('noopener noreferrer')
            ->setLinkTarget('_blank')
            ->setCssClass('ea-menu-item-link-to-site');
    }
}
