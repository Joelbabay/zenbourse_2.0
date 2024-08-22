<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository as OrmEntityRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @method User getUser()
 */
class UserCrudController extends AbstractCrudController
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
            ->setPageTitle(Crud::PAGE_INDEX, 'Utilisateurs')
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('lastname', 'Nom'),
            TextField::new('firstname', 'Prénom'),
            TextField::new('email', 'E-mail'),
            TextField::new('phone', 'Téléphone'),
            TextField::new('password')->onlyOnForms(),
            TextField::new('city', 'Ville'),
            BooleanField::new('isInvestisseur', 'Investisseur')->setFormTypeOptions([
                'mapped' => true,
                'attr' => [
                    'class' => 'js-investisseur-checkbox'
                ]
            ]),
            BooleanField::new('isIntraday', 'Intraday')->setFormTypeOptions([
                'mapped' => true,
                'attr' => [
                    'class' => 'js-intraday-checkbox'
                ]
            ]),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $user = $entityInstance;
        $plainPassword = $user->getPassword();
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);

        if (!$entityInstance instanceof User) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        $formData = $request->request->all();

        $roles[] = 'ROLE_USER';
        if (!empty($formData['User']['isInvestisseur'])) {
            $roles[] = 'ROLE_INVESTISSEUR';
        }
        if (!empty($formData['User']['isIntraday'])) {
            $roles[] = 'ROLE_INTRADAY';
        }
        $user->setRoles($roles);

        $user->setPassword($hashedPassword);

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {

        if (!$entityInstance instanceof User) {
            return;
        }

        $user = $entityInstance;

        $roles = $user->getRoles();

        if (!$user->isInvestisseur()) {
            $user->setIsIntraday(false);
        }

        if ($user->isInvestisseur()) {
            $user->setInterestedInInvestorMethod(false);
            if (!in_array('ROLE_INVESTISSEUR', $roles)) {
                $roles[] = 'ROLE_INVESTISSEUR';
            }
        } else {
            $roles = array_diff($roles, ['ROLE_INVESTISSEUR']);
        }
        if ($user->isIntraday()) {
            if (!in_array('ROLE_INTRADAY', $roles)) {
                $roles[] = 'ROLE_INTRADAY';
            }
        } else {
            $roles = array_diff($roles, ['ROLE_INTRADAY']);
        }
        $entityInstance->setRoles(array_unique(array_values($roles)));

        // Encoder le mot de passe s'il a été modifié
        $originalUser = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);

        if ($originalUser['password'] !== $user->getPassword()) {
            $encodedPassword = $this->passwordHasher->hashPassword(
                $entityInstance,
                $user->getPassword()
            );
            $entityInstance->setPassword($encodedPassword);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
