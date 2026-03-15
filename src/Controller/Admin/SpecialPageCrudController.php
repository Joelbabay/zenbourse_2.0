<?php
// src/Controller/Admin/SpecialPageCrudController.php

namespace App\Controller\Admin;

use App\Entity\SpecialPage;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

class SpecialPageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SpecialPage::class;
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

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Pages spéciales avec formulaires')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier la page')
            ->setEntityLabelInSingular('Page spéciale')
            ->setEntityLabelInPlural('Pages spéciales')
            ->showEntityActionsInlined()
            ->setDefaultSort(['code' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            ChoiceField::new('code', 'Code page')
                ->setChoices([
                    'Adhésion Intraday' => 'INTRADAY_SUBSCRIPTION',
                    'Adhésion Investisseur' => 'INVESTISSEUR_SUBSCRIPTION',
                    'Contact' => 'CONTACT',
                    'Téléchargement fichier' => 'DOWNLOAD_FILE',
                ])
                ->setRequired(true)
                ->setHelp('Identifiant technique de la page (ne peut pas être modifié après création)')
                ->setFormTypeOption('disabled', $pageName === Crud::PAGE_EDIT),

            TextField::new('title', 'Titre de la page')
                ->setRequired(true),

            TextareaField::new('content', 'Contenu')
                ->setFormTypeOption('attr', ['class' => 'ckeditor'])
                ->setHelp('Texte affiché AU-DESSUS du formulaire')
                ->hideOnIndex(),

            BooleanField::new('isActive', 'Active')
                ->renderAsSwitch(),

            DateTimeField::new('updatedAt', 'Dernière modification')
                ->hideOnForm(),
        ];
    }
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof SpecialPage) {
            $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof SpecialPage) {
            $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
