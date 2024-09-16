<?php

namespace App\Controller\Admin;

use App\Entity\IntradayRequest;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class IntradayRequestCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return IntradayRequest::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Personnes intéressées par la méthode Intraday')
            //->setEntityPermission('ROLE_ADMIN')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined();;
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
            TextField::new('lastname', 'Nom'),
            TextField::new('firstname', 'Prénom'),
            TextField::new('email', 'E-mail'),
            DateTimeField::new('createdAt', 'Date')->setFormat('dd/MM/YYYY')
                ->formatValue(function ($value, $entity) {
                    $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::RELATIVE_MEDIUM, \IntlDateFormatter::SHORT);
                    return $value ? $formatter->format($value) : ' ';
                }),
        ];
    }
}
