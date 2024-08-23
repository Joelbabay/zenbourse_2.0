<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
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
            ->setPageTitle(Crud::PAGE_INDEX, 'Messages')
            ->setEntityLabelInSingular('Message')
            ->setEntityLabelInPlural('Messages')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('lastname', 'Nom'),
            TextField::new('firstname', 'Prénom'),
            TextField::new('email', 'E-mail'),
            TextEditorField::new('content', 'Contenu'),
            DateTimeField::new('createdAt', 'Reçu le')->setFormat('dd/MM/YYYY')
        ];
    }
}
