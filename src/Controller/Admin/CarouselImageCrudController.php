<?php

namespace App\Controller\Admin;

use App\Entity\CarouselImage;
use App\Repository\CarouselImageRepository;
use App\Service\CarouselService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class CarouselImageCrudController extends AbstractCrudController
{
    public function __construct(
        private CarouselService $carouselService
    ) {}

    public static function getEntityFqcn(): string
    {
        return CarouselImage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Image du carrousel')
            ->setEntityLabelInPlural('Images du carrousel')
            ->setPageTitle('index', 'Gestion des images du carrousel')
            ->setPageTitle('new', 'Ajouter une image au carrousel')
            ->setPageTitle('edit', 'Modifier l\'image du carrousel')
            ->setDefaultSort(['position' => 'ASC'])
            ->setSearchFields(['title', 'altText'])
            ->setHelp('new', 'Ajoutez une nouvelle image au carrousel de la page d\'accueil.')
            ->setHelp('edit', 'Modifiez les propriétés de cette image du carrousel.')
            ->overrideTemplate('crud/index', 'admin/carousel_image_list.html.twig')
            ->overrideTemplate('crud/new', 'admin/carousel_image_upload_form.html.twig')
            ->overrideTemplate('crud/edit', 'admin/carousel_image_edit.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title', 'Titre')
            ->setHelp('Titre descriptif de l\'image (pour l\'administration)');

        if ($pageName === Crud::PAGE_INDEX) {
            // Dans la liste, afficher l'image en prévisualisation
            yield ImageField::new('imagePath', 'Image')
                ->setBasePath('')
                ->setUploadDir('public/')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setFormTypeOption('upload_dir', 'public/')
                ->setFormTypeOption('upload_filename', '[randomhash].[extension]')
                ->setHelp('Prévisualisation de l\'image du carrousel')
                ->setColumns(2);
        } else {
            // Dans les formulaires, garder le champ URL
            yield UrlField::new('imagePath', 'Chemin de l\'image')
                ->setHelp('URL complète de l\'image (ex: /images/home/banniere/pub1.jpg)')
                ->setRequired(true);
        }

        yield TextareaField::new('altText', 'Texte alternatif')
            ->setHelp('Description de l\'image pour l\'accessibilité')
            ->setRequired(false);

        yield IntegerField::new('position', 'Position')
            ->setHelp('Ordre d\'affichage dans le carrousel (1 = première image). Si vous entrez une position déjà occupée, les autres images seront automatiquement décalées.')
            ->setRequired(true);

        yield BooleanField::new('isActive', 'Actif')
            ->setHelp('Cochez pour afficher cette image dans le carrousel')
            ->renderAsSwitch(true);

        if ($pageName === Crud::PAGE_INDEX) {
            yield DateTimeField::new('createdAt', 'Créé le')
                ->setFormat('dd/MM/Y HH:mm')
                ->hideOnForm();

            yield DateTimeField::new('updatedAt', 'Modifié le')
                ->setFormat('dd/MM/Y HH:mm')
                ->hideOnForm();
        }
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Ajouter une image');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setLabel('Modifier');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setLabel('Supprimer');
            });
    }

    public function persistEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var CarouselImage $carouselImage */
        $carouselImage = $entityInstance;

        // Si la position n'est pas définie, la définir automatiquement
        if ($carouselImage->getPosition() === null) {
            $nextPosition = $this->carouselService->getNextPosition();
            $carouselImage->setPosition($nextPosition);
        } else {
            // Gérer le décalage des autres images si nécessaire
            $this->carouselService->handleImagePosition($carouselImage, $carouselImage->getPosition());
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var CarouselImage $carouselImage */
        $carouselImage = $entityInstance;

        // Si la position n'est pas définie, la définir automatiquement
        if ($carouselImage->getPosition() === null) {
            $nextPosition = $this->carouselService->getNextPosition();
            $carouselImage->setPosition($nextPosition);
        } else {
            // Gérer le décalage des autres images si nécessaire
            $this->carouselService->handleImagePosition($carouselImage, $carouselImage->getPosition());
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
