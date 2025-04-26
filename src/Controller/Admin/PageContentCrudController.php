<?php

namespace App\Controller\Admin;

use App\Entity\PageContent;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PageContentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PageContent::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Contenus de page')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modification')
            ->setPageTitle(Crud::PAGE_NEW, 'Création')
            ->showEntityActionsInlined();
        //->overrideTemplates(['label/null' => 'admin/labels/null_label.html.twig']);
    }

    public function configureActions(Actions $actions): Actions
    {

        return $actions
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
            AssociationField::new('menu', 'Menu lié'),
            TextareaField::new('content', 'Contenu')->onlyOnForms()->setFormTypeOption('attr', ['class' => 'ckeditor'])->setColumns(8)->setNumOfRows(30),
        ];
    }
}
