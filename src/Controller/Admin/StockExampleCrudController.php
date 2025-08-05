<?php

namespace App\Controller\Admin;

use App\Entity\StockExample;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class StockExampleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return StockExample::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Pages de la bibliothèque : création, modification, suppression.')
            ->setPageTitle('new', 'Ajouter un exemple de stock')
            ->setPageTitle('edit', 'Modifier l\'exemple de stock')
            ->setDefaultSort(['category' => 'ASC', 'title' => 'ASC'])
            ->setSearchFields(['title', 'ticker', 'category', 'description'])
            ->setHelp('new', 'Ajoutez un nouvel exemple de stock pour la bibliothèque.')
            ->setHelp('edit', 'Modifiez les propriétés de cet exemple de stock.');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title', 'Titre')
            ->setHelp('Titre de l\'exemple de stock');

        yield TextField::new('ticker', 'Ticker')
            ->setHelp('Symbole boursier (ex: AAPL, TSLA)');

        yield TextField::new('category', 'Catégorie')
            ->setHelp('Catégorie de l\'exemple (ex: bulles-type-1, volumes-faibles)');

        yield TextField::new('slug', 'Slug')
            ->setHelp('Identifiant unique pour l\'URL')
            ->hideOnForm();

        yield TextareaField::new('description', 'Description')
            ->setHelp('Description détaillée de l\'exemple')
            ->setRequired(false);

        yield TextareaField::new('introduction', 'Introduction de catégorie')
            ->setHelp('Introduction de la catégorie (utilisée pour le premier stock de chaque catégorie)')
            ->setRequired(false);

        yield UrlField::new('imageJour', 'Image Jour')
            ->setHelp('URL de l\'image pour le graphique journalier')
            ->setRequired(false);

        yield UrlField::new('imageSemaine', 'Image Semaine')
            ->setHelp('URL de l\'image pour le graphique hebdomadaire')
            ->setRequired(false);

        if ($pageName === Crud::PAGE_INDEX) {
            yield ImageField::new('imageJour', 'Image Jour')
                ->setBasePath('')
                ->setHelp('Prévisualisation de l\'image journalière')
                ->setColumns(2);
        }
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::new('inlineEdit', 'Édition Inline')
                ->linkToRoute('admin_stock_example_inline_edit')
                ->setIcon('fas fa-edit')
                ->addCssClass('btn btn-primary'))
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Ajouter un exemple');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setLabel('Modifier');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setLabel('Supprimer');
            });
    }
}
