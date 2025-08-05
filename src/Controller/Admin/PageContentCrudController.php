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

class PageContentCrudController extends AbstractCrudController
{
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

            TextField::new('title', 'Titre')
                ->setCssClass('text-primary')
                ->setFormTypeOption('attr', [
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Ex: Méthode d\'investissement',
                    'data-bs-toggle' => 'tooltip',
                    'title' => 'Titre qui sera affiché sur la page'
                ])
                ->formatValue(function ($value, $entity) {
                    return $value ?: '<span class="text-muted fst-italic">Aucun titre</span>';
                })
                ->renderAsHtml(),

            // Champ de sélection de section (seulement lors de la création)
            ChoiceField::new('section', 'Section')
                ->setChoices([
                    'Accueil' => 'HOME',
                    'Investisseur' => 'INVESTISSEUR',
                    'Intraday' => 'INTRADAY'
                ])
                ->setHelp('Sélectionnez d\'abord la section, puis le menu')
                ->onlyOnForms()
                ->setFormTypeOption('attr', [
                    'class' => 'form-select form-select-lg',
                    'data-bs-toggle' => 'tooltip',
                    'title' => 'Choisissez la section principale',
                    'id' => 'section-select',
                    'onchange' => 'filterMenusBySection()'
                ])
                ->renderAsBadges([
                    'HOME' => 'warning',
                    'INVESTISSEUR' => 'success',
                    'INTRADAY' => 'primary'
                ]),

            // Colonne Section pour l'index
            TextField::new('section', 'Section')
                ->onlyOnIndex()
                ->formatValue(function ($value, $entity) {
                    if ($entity->getMenu()) {
                        $section = $entity->getMenu()->getSection();
                        $sectionBadge = match ($section) {
                            'HOME' => 'warning',
                            'INVESTISSEUR' => 'success',
                            'INTRADAY' => 'primary',
                            default => 'secondary'
                        };
                        return sprintf(
                            '<span class="badge badge-%s">%s</span>',
                            $sectionBadge,
                            $section
                        );
                    }
                    return '<span class="text-muted fst-italic">Aucune section</span>';
                })
                ->renderAsHtml(),

            // Champ de menu pour la création
            AssociationField::new('menu', 'Menu lié')
                ->onlyOnForms()
                ->setFormTypeOption('attr', [
                    'class' => 'form-select form-select-lg',
                    'data-bs-toggle' => 'tooltip',
                    'title' => 'Sélectionnez le menu spécifique',
                    'id' => 'menu-select'
                ])
                ->formatValue(function ($value, $entity) {
                    if ($entity->getMenu()) {
                        return '<span class="fw-semibold">' . $entity->getMenu()->getLabel() . '</span>';
                    }
                    return '<span class="text-muted fst-italic">Aucun menu</span>';
                })
                ->renderAsHtml(),

            // Colonne Menu pour l'index
            AssociationField::new('menu', 'Menu')
                ->onlyOnIndex()
                ->formatValue(function ($value, $entity) {
                    if ($entity->getMenu()) {
                        return '<span class="fw-semibold">' . $entity->getMenu()->getLabel() . '</span>';
                    }
                    return '<span class="text-muted fst-italic">Aucun menu</span>';
                })
                ->renderAsHtml(),

            TextareaField::new('content', 'Contenu')
                ->onlyOnForms()
                ->setFormTypeOption('attr', [
                    'class' => 'ckeditor',
                    'rows' => '15',
                    'placeholder' => 'Rédigez le contenu de votre page ici...',
                    'title' => 'Utilisez l\'éditeur pour formater votre contenu'
                ])
                ->setColumns(11)
                ->setNumOfRows(30)
        ];
    }

    public function createNewFormBuilder(\EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto $entityDto, \EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore $formOptions, \EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext $context): \Symfony\Component\Form\FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);

        // Remplacer le champ menu par un EntityType personnalisé
        $formBuilder->add('menu', EntityType::class, [
            'class' => Menu::class,
            'choice_label' => function (Menu $menu) {
                return $menu->getLabel() . ' (' . $menu->getSection() . ')';
            },
            'required' => true,
            'help' => 'Sélectionnez le menu pour lequel vous voulez créer le contenu',
            'query_builder' => function ($repository) {
                return $repository->createQueryBuilder('m')
                    ->orderBy('m.section', 'ASC')
                    ->addOrderBy('m.menuorder', 'ASC');
            }
        ]);

        // Écouteur pour mettre à jour dynamiquement les menus selon la section
        $formBuilder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $pageContent = $event->getData();
            $form = $event->getForm();

            if ($pageContent && $pageContent->getMenu() && $pageContent->getMenu()->getSection()) {
                $this->updateMenuField($form, $pageContent->getMenu()->getSection());
            }
        });

        $formBuilder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['section'])) {
                $this->updateMenuField($form, $data['section']);
            }
        });

        return $formBuilder;
    }

    public function createEditFormBuilder(\EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto $entityDto, \EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore $formOptions, \EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext $context): \Symfony\Component\Form\FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);

        // Supprimer les champs section et menu lors de la modification
        $formBuilder->remove('section');
        $formBuilder->remove('menu');

        return $formBuilder;
    }

    private function updateMenuField($form, string $section): void
    {
        if ($form->has('menu')) {
            $form->remove('menu');
            $form->add('menu', EntityType::class, [
                'class' => Menu::class,
                'choice_label' => function (Menu $menu) {
                    return $menu->getLabel() . ' (' . $menu->getSection() . ')';
                },
                'required' => true,
                'help' => 'Sélectionnez le menu pour lequel vous voulez créer/modifier le contenu',
                'query_builder' => function ($repository) use ($section) {
                    return $repository->createQueryBuilder('m')
                        ->where('m.section = :section')
                        ->setParameter('section', $section)
                        ->orderBy('m.menuorder', 'ASC');
                }
            ]);
        }
    }
}
