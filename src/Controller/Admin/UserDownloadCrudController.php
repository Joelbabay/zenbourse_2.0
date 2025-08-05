<?php

namespace App\Controller\Admin;

use App\Entity\Download;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @method Download getDownload()
 */
class UserDownloadCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Download::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Personnes ayant téléchargé la liste des valeurs 2020')
            ->setEntityLabelInSingular('Liste des Utilisateurs Ayant Téléchargé le Fichier')
            ->setEntityLabelInPlural('Liste des Utilisateurs Ayant Téléchargé le Fichier')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setDateFormat("EEE, MMM d, ''yy")
            ->setTimeFormat("h:mm a")
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT)
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
            TextField::new('civility', 'Civilité')->onlyOnIndex()
                ->formatValue(function ($value, $entity) {
                    return $value ?? ' ';
                }),
            TextField::new('lastname', 'Non'),
            TextField::new('firstname', 'Prénom'),
            TextField::new('email', 'E-mail'),
            DateTimeField::new('createdAt', 'Date de téléchargement')->setFormat('d F Y - H:i:s')
                ->formatValue(function ($value, $entity) {
                    $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::RELATIVE_MEDIUM, \IntlDateFormatter::SHORT);
                    return $value ? $formatter->format($value) : ' ';
                }),
        ];
    }
}
