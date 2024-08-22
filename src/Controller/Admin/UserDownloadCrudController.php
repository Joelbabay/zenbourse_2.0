<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository as OrmEntityRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @method User getUser()
 */
class UserDownloadCrudController extends AbstractCrudController
{
    private $userRepository;
    private $entityRepository;

    public function __construct(OrmEntityRepository $entityRepository, UserRepository $userRepository, private UserPasswordHasherInterface $passwordHasher, private RequestStack $requestStack)
    {
        $this->userRepository = $userRepository;
        $this->entityRepository = $entityRepository;
    }
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des Utilisateurs Ayant Téléchargé le Fichier')
            ->setEntityLabelInSingular('Liste des Utilisateurs Ayant Téléchargé le Fichier')
            ->setEntityLabelInPlural('Liste des Utilisateurs Ayant Téléchargé le Fichier')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setDateFormat("EEE, MMM d, ''yy")
            ->setTimeFormat("h:mm a")
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::DELETE, Action::EDIT);
    }

    public function createIndexQueryBuilder(\EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto $searchDto, \EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto $entityDto, \EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection $fields, \EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection $filters): QueryBuilder
    {
        $qb = $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // les utilisateurs en fonction d'une condition "downloadRequestSubmitted = true"
        $qb->andWhere('entity.downloadRequestSubmitted = :downloadRequestSubmitted')
            ->setParameter('downloadRequestSubmitted', true);

        return $qb;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('lastname', 'Non'),
            TextField::new('firstname', 'Prénom'),
            TextField::new('email'),
            DateTimeField::new('createdAt', 'Date de téléchargement'),
        ];
    }
}
