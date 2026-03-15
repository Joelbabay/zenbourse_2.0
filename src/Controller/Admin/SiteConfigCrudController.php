<?php
// src/Controller/Admin/SiteConfigCrudController.php

namespace App\Controller\Admin;

use App\Entity\SiteConfig;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SiteConfigCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SiteConfig::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Textes du site')
            ->showEntityActionsInlined()
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('label', 'Nom'),
            TextareaField::new('value', 'Texte')
                ->setFormTypeOption('attr', ['class' => 'ckeditor'])

                ->setHelp('Texte affiché sur le site'),
        ];
    }
}
