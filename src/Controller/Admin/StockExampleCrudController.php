<?php

namespace App\Controller\Admin;

use App\Entity\StockExample;
use App\Entity\Menu;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class StockExampleCrudController extends AbstractCrudController
{
    // 2. Déclarer une propriété pour stocker l'EntityManager
    private EntityManagerInterface $entityManager;

    // 3. Injecter l'EntityManager via le constructeur
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getEntityFqcn(): string
    {
        return StockExample::class;
    }

    // ... (configureCrud, configureActions et les autres méthodes restent identiques)
    // ... (Je les omets ici pour la clarté, ne les supprimez pas de votre fichier)

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Exemples de stocks : gestion de la bibliothèque')
            ->setPageTitle('new', 'Ajouter un exemple de stock')
            ->setPageTitle('edit', 'Modifier l\'exemple de stock')
            ->setDefaultSort(['category' => 'ASC', 'title' => 'ASC'])
            ->setSearchFields(['title', 'ticker', 'category'])
            ->setHelp('new', 'Ajoutez un nouvel exemple de stock pour enrichir la bibliothèque.')
            ->setHelp('edit', 'Modifiez les propriétés de cet exemple de stock.')
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        // Champs principaux organisés en colonnes
        yield TextField::new('title', 'Titre de l\'exemple')
            ->setHelp('Nom descriptif de l\'exemple de stock (ex: "Formation de bulle sur Apple")')
            ->setColumns(6)
            ->setRequired(true);

        yield TextField::new('ticker', 'Symbole boursier')
            ->setHelp('Code de 1 à 5 lettres du titre boursier (ex: AAPL, TSLA, MSFT)')
            ->setColumns(6)
            ->setRequired(true)
            ->setMaxLength(10);

        yield TextField::new('flag', 'Drapeau pays')
            ->setHelp('Code pays du titre (ex: US, FR, DE)')
            ->setColumns(6)
            ->setRequired(true)
            ->setMaxLength(5);

        yield ChoiceField::new('category', 'Catégorie')
            ->setHelp('Sélectionnez la catégorie correspondant au menu enfant de la bibliothèque')
            ->setChoices(fn() => $this->getCategoryChoices())
            ->setRequired(true)
            ->setColumns(6)
            ->formatValue(function ($value, $entity) {
                // $value contient le slug stocké en base de données (ex: 'bulles-type-1')
                if (!$value) {
                    return 'Non définie';
                }
                $choices = $this->getCategoryChoices();
                return $choices[$value] ?? ucfirst(str_replace('-', ' ', $value));
            });

        // Champs d'état et métadonnées (cachés sur l'index)
        if ($pageName !== Crud::PAGE_INDEX) {
            yield BooleanField::new('isActive', 'Actif')
                ->setHelp('Détermine si cet exemple est visible sur le site public')
                ->setColumns(6);

            yield TextField::new('slug', 'Slug URL')
                ->setHelp('Identifiant unique pour l\'URL (généré automatiquement)')
                ->setColumns(6)
                ->setDisabled(true);
        }

        // Champs d'audit (toujours visibles)
        if ($pageName === Crud::PAGE_INDEX) {
            yield BooleanField::new('isActive', 'État')
                ->setHelp('Si ce menu doit être visible sur le site public.')
                // Utilise un template personnalisé sur l'index pour contourner le problème de la requête PATCH
                ->setTemplatePath($pageName === Crud::PAGE_INDEX ? 'admin/fields/is_active_toggle.html.twig' : null)
                // Garde l'interrupteur classique sur les pages de création/modification
                ->renderAsSwitch($pageName !== Crud::PAGE_INDEX);
        }
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setLabel('Ajouter un exemple')
                    ->setIcon('fas fa-plus')
                    ->addCssClass('btn btn-success');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action
                    ->setIcon('fas fa-edit')
                    ->setLabel(false)
                    ->addCssClass('btn btn-link');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fas fa-trash')
                    ->setLabel(false)
                    ->addCssClass('btn btn-link text-danger');
            });
    }


    /**
     * Récupère les choix de catégories depuis les sous-menus du menu "Bibliothèque"
     * dans la section INVESTISSEUR
     */
    private function getCategoryChoices(): array
    {
        try {
            // 4. Utiliser la propriété $this->entityManager qui est maintenant définie et fiable
            $menuRepository = $this->entityManager->getRepository(Menu::class);

            // 1. D'abord, trouver le menu "Bibliothèque" dans la section INVESTISSEUR
            $bibliothequeMenu = $menuRepository->findOneBy([
                'section' => 'INVESTISSEUR',
                'label' => 'Bibliothèque',
                'isActive' => true
            ]);

            if (!$bibliothequeMenu) {
                // Cette erreur est informative et aide au débogage
                return ['aucune-categorie' => 'Menu "Bibliothèque" non trouvé'];
            }

            // 2. Récupérer tous les sous-menus de "Bibliothèque"
            // La recherche sur la propriété 'parent' avec l'objet $bibliothequeMenu fonctionne
            // car Doctrine sait qu'il doit chercher les entrées où parent_id = $bibliothequeMenu->getId()
            $sousMenus = $menuRepository->findBy([
                'parent' => $bibliothequeMenu,
                'isActive' => true
            ], ['menuorder' => 'ASC']);

            $choices = [];
            foreach ($sousMenus as $sousMenu) {
                // Utiliser le slug comme clé et le label comme valeur est une bonne pratique
                $choices[$sousMenu->getSlug()] = $sousMenu->getLabel();
            }

            if (empty($choices)) {
                return ['aucune-categorie' => 'Aucun sous-menu actif trouvé'];
            }

            // Inverser le tableau pour que le label soit affiché dans le select et le slug soit la valeur
            return array_flip($choices);
            //return $choices;
        } catch (\Exception $e) {
            error_log('Erreur critique dans getCategoryChoices: ' . $e->getMessage());
            // Retourner un message d'erreur clair dans l'interface si tout échoue
            return ['erreur' => 'Erreur de configuration'];
        }
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var StockExample $stockExample */
        $stockExample = $entityInstance;

        // Générer automatiquement le slug si vide
        if (empty($stockExample->getSlug())) {
            $slug = $this->generateSlug($stockExample->getTitle());
            $stockExample->setSlug($slug);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var StockExample $stockExample */
        $stockExample = $entityInstance;

        // Mettre à jour le timestamp
        $stockExample->setUpdatedAt(new \DateTimeImmutable());

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function toggleIsActive(AdminContext $context, EntityManagerInterface $entityManager): Response
    {
        /** @var StockExample|null $stockExample */
        $stockExample = $context->getEntity()->getInstance();
        if ($stockExample) {
            $stockExample->setIsActive(!$stockExample->isActive());
            $entityManager->flush();
            //$this->addFlash('success', 'Le statut de l\'exemple a bien été mis à jour.');
        } else {
            //$this->addFlash('error', 'Exemple de stock non trouvé.');
        }

        // On reconstruit l'URL de la page index pour la redirection
        $url = $this->container->get(AdminUrlGenerator::class)
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($context->getReferrer() ?? $url);
    }


    /**
     * Génère un slug à partir du titre
     */
    private function generateSlug(string $title): string
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug;
    }
}
