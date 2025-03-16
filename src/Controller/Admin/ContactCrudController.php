<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContactCrudController extends AbstractCrudController
{
    private $entityManager;
    private $adminUrlGenerator;

    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator, private UrlGeneratorInterface $urlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }
    public static function getEntityFqcn(): string
    {
        return Contact::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Messages reçus')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détails du Message')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined()
            ->overrideTemplate('crud/index', 'admin/contact_list.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {

        return $actions
            ->update(Crud::PAGE_DETAIL, Action::INDEX, function (Action $action) {
                return $action->setLabel('Retour à la liste')->setIcon('fas fa-list');
            })
            ->add(Crud::PAGE_INDEX, Action::new('Show', 'Consulter le message', 'fas fa-eye')->linkToCrudAction('markAsRead'))

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

    // Fonction pour gérer les vues des message dans contact 
    // et ensuite faire la redirection dans ACTION::DETAIL
    public function markAsRead(AdminContext $context,)
    {
        $contact = $context->getEntity()->getInstance();

        if ($contact instanceof Contact && !$contact->isRead()) {
            $contact->setRead(true);
            $this->entityManager->flush();
        }

        $url = $this->container->get(AdminUrlGenerator::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($context->getEntity()->getPrimaryKeyValue())
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('civility', 'Civilité')->onlyOnIndex()
                ->formatValue(function ($value, $entity) {
                    return $value ?? ' ';
                }),
            FormField::addColumn(4),
            FormField::addFieldset('Informations')->setIcon('fa fa-info-circle'),
            TextField::new('lastname', 'Nom')
                ->formatValue(function ($value, $entity) {
                    return $value ?? ' ';
                }),
            TextField::new('firstname', 'Prénom')
                ->formatValue(function ($value, $entity) {
                    return $value ?? ' ';
                }),
            TextField::new('email', 'E-mail'),
            DateTimeField::new('createdAt', 'Reçu le')->setFormat('d F Y - H:i:s')
                ->formatValue(function ($value, $entity) {
                    $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::RELATIVE_MEDIUM, \IntlDateFormatter::SHORT);
                    return $value ? $formatter->format($value) : ' ';
                })->hideOnIndex(),
            FormField::addColumn(8),
            FormField::addPanel('Message')->setIcon('fa fa-comment'),
            TextareaField::new('content', 'Contenu')->setColumns(6),
            DateTimeField::new('createdAt', 'Reçu le')->setFormat('d F Y - H:i:s')
                ->formatValue(function ($value, $entity) {
                    $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::RELATIVE_MEDIUM, \IntlDateFormatter::SHORT);
                    return $value ? $formatter->format($value) : ' ';
                })->hideOnDetail(),
        ];
    }
}
