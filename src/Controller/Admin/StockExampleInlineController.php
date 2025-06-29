<?php

namespace App\Controller\Admin;

use App\Entity\StockExample;
use App\Repository\StockExampleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/stock-example')]
class StockExampleInlineController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StockExampleRepository $stockExampleRepository
    ) {}

    #[Route('/update', name: 'admin_stock_example_update', methods: ['POST'])]
    public function update(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data || !isset($data['id']) || !isset($data['field']) || !isset($data['content'])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Données manquantes'
                ]);
            }

            $stockExample = $this->stockExampleRepository->find($data['id']);

            if (!$stockExample) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Stock example non trouvé'
                ]);
            }

            // Mettre à jour le champ spécifié
            $field = $data['field'];
            $content = $data['content'];

            switch ($field) {
                case 'description':
                    $stockExample->setDescription($content);
                    break;
                case 'title':
                    $stockExample->setTitle($content);
                    break;
                case 'category':
                    $stockExample->setCategory($content);
                    break;
                case 'ticker':
                    $stockExample->setTicker($content);
                    break;
                default:
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Champ non autorisé'
                    ]);
            }

            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Modification sauvegardée avec succès',
                'data' => [
                    'id' => $stockExample->getId(),
                    'field' => $field,
                    'content' => $content
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde: ' . $e->getMessage()
            ]);
        }
    }

    #[Route('/inline-edit', name: 'admin_stock_example_inline_edit')]
    public function inlineEdit(Request $request): Response
    {
        $selectedCategory = $request->query->get('category');

        // Récupérer toutes les catégories disponibles
        $categories = $this->stockExampleRepository->findDistinctCategories();

        // Si une catégorie est sélectionnée, filtrer les stocks
        if ($selectedCategory) {
            $stocks = $this->stockExampleRepository->findByCategory($selectedCategory);
        } else {
            // Par défaut, afficher tous les stocks groupés par catégorie
            $stocks = $this->stockExampleRepository->findAll();
        }

        return $this->render('admin/stock_examples_inline_edit.html.twig', [
            'stocks' => $stocks,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory
        ]);
    }
}
