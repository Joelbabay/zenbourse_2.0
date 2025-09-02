<?php

namespace App\Controller\Admin;

use App\Entity\PageContent;
use App\Entity\Menu;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use App\Entity\StockExample;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use App\Repository\MenuRepository;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

class PageContentCrudController extends AbstractCrudController
{
    private MenuRepository $menuRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(MenuRepository $menuRepository, EntityManagerInterface $entityManager)
    {
        $this->menuRepository = $menuRepository;
        $this->entityManager = $entityManager;
    }

    public static function getEntityFqcn(): string
    {
        return PageContent::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Pages : création, modification, suppression.')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modification')
            ->setPageTitle(Crud::PAGE_NEW, 'Création')
            ->showEntityActionsInlined()
            ->setDefaultSort(['title' => 'ASC'])
            //->setDefaultSort(['menu.section' => 'ASC']) // Tri par défaut en ordre ascendant
            ->overrideTemplates([
                'crud/new' => 'admin/page_content_new.html.twig',
                'crud/edit' => 'admin/page_content_edit.html.twig'
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {

        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-plus')->setLabel('Créer nouveau contenu');
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
            TextField::new('title', 'Titre'),

            ChoiceField::new('contentType', 'Type de contenu')
                ->setChoices([
                    'Lier à un Menu' => 'menu',
                    'Lier à un Ticker de la bibliothèque' => 'stock_example',
                ])
                ->setHelp('Choisissez si ce contenu est pour une page de menu standard ou pour un ticker spécifique.')
                ->onlyOnForms()
                ->setFormTypeOption('attr', ['onchange' => 'toggleContentTypeFields()']),

            // Champs pour le type "Menu"
            ChoiceField::new('section', 'Section du Menu')
                ->setChoices([
                    'ACCUEIL' => 'HOME',
                    'INVESTISSEUR' => 'INVESTISSEUR',
                    'INTRADAY' => 'INTRADAY',
                ])
                ->setHelp('Sélectionnez la section pour filtrer les menus.')
                ->onlyOnForms()
                ->setFormTypeOption('row_attr', ['class' => 'content-type-field content-type-menu']),

            AssociationField::new('menu', 'Menu lié')
                ->setHelp('Choisissez le menu auquel ce contenu sera lié.')
                ->onlyOnForms()
                ->setFormTypeOption('row_attr', ['class' => 'content-type-field content-type-menu']),

            // Champ pour le type "StockExample"
            AssociationField::new('stockExample', 'Ticker lié')
                ->setHelp('Choisissez le ticker auquel ce contenu sera lié.')
                ->setQueryBuilder(function ($queryBuilder) {
                    return $queryBuilder->from(StockExample::class, 's')->orderBy('s.title', 'ASC');
                })
                ->onlyOnForms()
                ->setFormTypeOption('row_attr', ['class' => 'content-type-field content-type-stock_example']),

            TextareaField::new('content', 'Contenu')
                ->onlyOnForms()
                ->setFormTypeOption('attr', ['class' => 'ckeditor']),

            // Colonnes pour l'index
            AssociationField::new('menu', 'Menu Lié')
                ->onlyOnIndex()
                ->formatValue(function ($value) {
                    return $value ? mb_strtoupper($value, 'UTF-8') : null;
                }),
            TextField::new('section', 'Section')
                ->onlyOnIndex()
                // ->setSortable(true) -> On ne peut pas trier sur cette colonne virtuelle
                // ->setProperty('menu.section') -> car la source de donnée est multiple
                ->formatValue(function ($value, $entity) {
                    $sectionValue = null;
                    if ($entity->getMenu()) {
                        $sectionValue = $entity->getMenu()->getSection();
                    } elseif ($entity->getStockExample()) {
                        $sectionValue = 'INVESTISSEUR';
                    }

                    if ($sectionValue) {
                        $badgeClass = match ($sectionValue) {
                            'HOME' => 'warning',
                            'INVESTISSEUR' => 'success',
                            'INTRADAY' => 'primary',
                            default => 'secondary'
                        };

                        $sectionLabel = match ($sectionValue) {
                            'HOME' => 'ACCUEIL',
                            default => $sectionValue
                        };

                        return sprintf('<span class="badge badge-%s">%s</span>', $badgeClass, $sectionLabel);
                    }

                    return '<span class="text-muted">N/A</span>';
                })
                ->renderAsHtml(),
        ];
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // Récupérer la section depuis les paramètres de la requête
        $section = $this->getContext()->getRequest()->query->get('section');

        if ($section) {
            $queryBuilder->leftJoin('entity.menu', 'm');

            if ($section === 'INVESTISSEUR') {
                // Pour la section INVESTISSEUR, on inclut les contenus liés aux menus de cette section
                // ET les contenus liés à un StockExample (qui appartiennent implicitement à cette section).
                $queryBuilder->andWhere($queryBuilder->expr()->orX(
                    'm.section = :section',
                    'entity.stockExample IS NOT NULL'
                ))
                    ->setParameter('section', $section);
            } else {
                // Pour les autres sections, le filtrage simple suffit.
                $queryBuilder->andWhere('m.section = :section')
                    ->setParameter('section', $section);
            }
        }

        return $queryBuilder;
    }

    public function createEntity(string $entityFqcn)
    {
        $pageContent = parent::createEntity($entityFqcn);

        // Récupérer la section depuis les paramètres de la requête
        $section = $this->getContext()->getRequest()->query->get('section');

        if ($section) {
            // Pré-remplir la section et le type de contenu
            $pageContent->setSection($section);
            $pageContent->setContentType('menu'); // On assume que c'est pour un menu
        }

        return $pageContent;
    }

    // Les méthodes createNewFormBuilder, createEditFormBuilder et updateMenuField
    // ont été supprimées car la nouvelle logique dans configureFields
    // gère maintenant la sélection du type de contenu et l'affichage des champs associés.
    // Cette simplification rend le code plus facile à maintenir.

    public function configureAssets(Assets $assets): Assets
    {
        // 1. Préparer les données des menus pour JavaScript
        $allMenus = $this->menuRepository->findAll();
        $menusData = [];
        foreach ($allMenus as $menu) {
            $menusData[] = [
                'id' => $menu->getId(),
                'label' => $menu->getLabel(),
                'section' => $menu->getSection()
            ];
        }

        // 2. Injecter les données et le script de pilotage
        return $assets
            ->addJsFile('https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js')
            ->addHtmlContentToBody(sprintf(
                '<script>
                    window.allMenusData = %s;
                </script>',
                json_encode($menusData)
            ))
            ->addHtmlContentToBody('
                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const contentTypeSelect = document.querySelector(\'[name="PageContent[contentType]"]\');
                    const sectionSelect = document.querySelector(\'[name="PageContent[section]"]\');
                    const menuSelect = document.querySelector(\'[name="PageContent[menu]"]\');

                    const tomSelectInstance = menuSelect ? menuSelect.tomselect : null;

                    function updateMenuOptions() {
                        if (!tomSelectInstance || !sectionSelect) return;
                        
                        const selectedSection = sectionSelect.value;
                        const currentValue = tomSelectInstance.getValue();
                        
                        tomSelectInstance.clearOptions();

                        const filteredMenus = window.allMenusData.filter(menu => menu.section === selectedSection);
                        tomSelectInstance.addOptions(filteredMenus.map(menu => ({ value: menu.id, text: menu.label })));
                        
                        // Re-appliquer la valeur si elle est toujours valide, sinon effacer.
                        const currentSelectionIsValid = filteredMenus.some(menu => menu.id == currentValue);
                        if (currentSelectionIsValid) {
                            tomSelectInstance.setValue(currentValue);
                        } else {
                            tomSelectInstance.clear();
                        }
                    }
                    
                    function toggleContentTypeFields() {
                        if (!contentTypeSelect) return;
                        const contentType = contentTypeSelect.value;
                        const showMenuFields = contentType === "menu";

                        document.querySelectorAll(".content-type-menu").forEach(field => {
                            field.closest(".form-group").style.display = showMenuFields ? "block" : "none";
                        });

                        document.querySelectorAll(".content-type-stock_example").forEach(field => {
                            field.closest(".form-group").style.display = contentType === "stock_example" ? "block" : "none";
                        });

                        if (showMenuFields) {
                            updateMenuOptions();
                        }
                    }

                    if (contentTypeSelect) {
                        contentTypeSelect.addEventListener("change", toggleContentTypeFields);
                    }

                    if (sectionSelect) {
                        sectionSelect.addEventListener("change", updateMenuOptions);
                    }
                    
                    // Initialisation
                    toggleContentTypeFields();
                });
                </script>
            ');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var PageContent $pageContent */
        $pageContent = $entityInstance;

        // Gérer la nouvelle association
        if ($currentMenu = $pageContent->getMenu()) {
            $currentMenu->setPageContent($pageContent);
            $entityManager->persist($currentMenu);
        }

        // Assurer la cohérence (ne lier qu'à un seul type)
        $this->ensureAssociationConsistency($pageContent);

        parent::persistEntity($entityManager, $pageContent);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var PageContent $pageContent */
        $pageContent = $entityInstance;

        // Récupérer l'état original de l'entité avant les modifications du formulaire
        $originalUnitOfWork = $entityManager->getUnitOfWork();
        $originalData = $originalUnitOfWork->getOriginalEntityData($pageContent);
        $originalMenu = $originalData['menu'] ?? null;

        $currentMenu = $pageContent->getMenu();

        // 1. Gérer la dissociation de l'ancien menu
        if ($originalMenu && $originalMenu !== $currentMenu) {
            $originalMenu->setPageContent(null);
            $entityManager->persist($originalMenu);
        }

        // 2. Gérer la nouvelle association
        if ($currentMenu) {
            $currentMenu->setPageContent($pageContent);
            $entityManager->persist($currentMenu);
        }

        // 3. Assurer la cohérence (ne lier qu'à un seul type)
        $this->ensureAssociationConsistency($pageContent);

        parent::updateEntity($entityManager, $pageContent);
    }

    private function ensureAssociationConsistency(PageContent $pageContent): void
    {
        if ($pageContent->getContentType() === 'menu') {
            $pageContent->setStockExample(null);
        } elseif ($pageContent->getContentType() === 'stock_example') {
            $pageContent->setMenu(null);
        }
    }
}
