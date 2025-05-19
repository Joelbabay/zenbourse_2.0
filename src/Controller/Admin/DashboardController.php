<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Entity\Download;
use App\Entity\IntradayRequest;
use App\Entity\InvestisseurRequest;
use App\Entity\Menu;
use App\Entity\PageContent;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(ContactCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Zenbourse 50');
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            ->setPaginatorPageSize(15);
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile('/css/admin.css')
            ->addHtmlContentToHead('<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/45.0.0/ckeditor5.css" />')
            ->addHtmlContentToHead('<script src="https://cdn.ckeditor.com/ckeditor5/45.0.0/ckeditor5.umd.js"></script>')
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
                 
                 ClassicEditor
                    .create( document.querySelector( '.ckeditor' ), {
                        licenseKey: 'eyJhbGciOiJFUzI1NiJ9.eyJleHAiOjE3NzY5ODg3OTksImp0aSI6IjI3ZmZjNmZjLTA4ZGItNDg1MS1iZDdkLThmNmIwM2I5Zjk5NiIsInVzYWdlRW5kcG9pbnQiOiJodHRwczovL3Byb3h5LWV2ZW50LmNrZWRpdG9yLmNvbSIsImRpc3RyaWJ1dGlvbkNoYW5uZWwiOlsiY2xvdWQiLCJkcnVwYWwiXSwiZmVhdHVyZXMiOlsiRFJVUCJdLCJ2YyI6IjA1NzIwN2I0In0.hm5C4s5dGb2wmhGMqp9462Jinh5lrTcIPAboSivr4H2B86gtqHTS_IJp-3CJQk7VHzTZjpb-Ayv4jlxU5qpSRQ',
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
                            shouldNotGroupWhenFull: false
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
                                    'Impact',
                                    'Segoe UI',
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
            </script>");
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::linkToCrud('Boîte de réception', 'fa fa-inbox', Contact::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user-friends', User::class)->setController(UserCrudController::class);
        yield MenuItem::linkToCrud('Téléchargement liste valeurs 2020', 'fa fa-download', Download::class)
            ->setController(UserDownloadCrudController::class);
        yield MenuItem::linkToCrud('Méthode Investisseur', 'fa fa-star', InvestisseurRequest::class)
            ->setController(InterestedUsersCrudController::class);
        yield MenuItem::linkToCrud('Méthode Intraday', 'fa fa-star', IntradayRequest::class)
            ->setController(IntradayRequestCrudController::class);
        yield MenuItem::linkToCrud('Menus', 'fa fa-list', Menu::class);
        yield MenuItem::linkToCrud('Contenus des Pages', 'fa fa-edit', PageContent::class);
    }
}