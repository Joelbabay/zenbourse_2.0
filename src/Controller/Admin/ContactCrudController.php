<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ContactCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Contact::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Messages de Contact')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détails du Message')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined();;
    }

    public function configureActions(Actions $actions): Actions
    {

        return $actions
            ->update(Crud::PAGE_DETAIL, Action::INDEX, function (Action $action) {
                return $action->setLabel('Retour à la liste')->setIcon('fas fa-list');
            })
            ->add(Crud::PAGE_INDEX, Action::new('Show', 'Voir', 'fas fa-eye')->linkToCrudAction(Action::DETAIL))

            ->disable(Action::NEW, Action::EDIT)
            // Mise à jour de l'action de suppression pour utiliser une icône spécifique
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fas fa-trash')
                    ->setLabel(false)
                    ->addCssClass('btn btn-link text-danger');
            })
            ->add(Crud::PAGE_DETAIL, Action::new('reply', 'Répondre')
                ->linkToUrl(function (Contact $message) {
                    return 'mailto:' . $message->getEmail() . '?subject=Réponse à votre message';
                })
                ->setIcon('fas fa-reply'))

            ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
                return $action->setLabel('Supprimer')->setIcon('fas fa-trash');
            })
        ;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addColumn(4),
            FormField::addFieldset('Informations')->setIcon('fa fa-info-circle'),
            TextField::new('lastname', 'Nom'),
            TextField::new('firstname', 'Prénom'),
            TextField::new('email', 'E-mail'),
            DateTimeField::new('createdAt', 'Reçu le')->setFormat('dd/MM/YYYY'),
            FormField::addColumn(8),
            FormField::addPanel('Message')->setIcon('fa fa-comment'),
            TextEditorField::new('content', '')->hideOnIndex()->setColumns(6),
        ];
    }
}
