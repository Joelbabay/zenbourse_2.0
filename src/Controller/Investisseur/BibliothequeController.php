<?php

namespace App\Controller\Investisseur;

use App\Service\StockExampleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/investisseur/bibliotheque')]
#[IsGranted('ROLE_INVESTISSEUR')]
class BibliothequeController extends AbstractController
{
    public function __construct(
        private StockExampleService $stockExampleService
    ) {}

    private function checkInvestorAccess(): ?Response
    {
        $user = $this->getUser();
        if (!$user || (!$user->isInvestisseur() && !$user->hasValidTemporaryInvestorAccess())) {
            $this->addFlash('danger', 'Vous n\'avez pas accès à la méthode Investisseur.');
            return $this->redirectToRoute('home');
        }
        return null;
    }

    #[Route('', name: 'investisseur_bibliotheque')]
    public function index(): Response
    {
        if ($resp = $this->checkInvestorAccess()) {
            return $resp;
        }
        return $this->render('investisseur/bibliotheque.html.twig');
    }

    #[Route('/{category}', name: 'investisseur_bibliotheque_category')]
    public function category(string $category): Response
    {
        $examples = $this->stockExampleService->getExamplesByCategory($category);
        $valueTitle = $this->stockExampleService->getCategoryTitle($category);
        $formattedExamples = $this->stockExampleService->formatExamplesForTemplate($examples);
        $introduction = $this->stockExampleService->getCategoryIntroduction($category);

        return $this->render('investisseur/bibliotheque/bibliotheque-category.html.twig', [
            'examples' => $formattedExamples,
            'valueTitle' => $valueTitle,
            'category' => $category,
            'introduction' => $introduction
        ]);
    }

    #[Route('/{category}/{slug}', name: 'investisseur_bibliotheque_detail')]
    public function detail(string $category, string $slug, Request $request): Response
    {
        $example = $this->stockExampleService->getExampleBySlug($slug);

        if (!$example) {
            throw $this->createNotFoundException('Cet exemple n\'existe pas.');
        }

        $examples = $this->stockExampleService->getExamplesByCategory($category);
        $valueTitle = $this->stockExampleService->getCategoryTitle($category);
        $formattedExamples = $this->stockExampleService->formatExamplesForTemplate($examples);
        $formattedExample = $this->stockExampleService->formatExampleForTemplate($example);

        return $this->render('investisseur/bibliotheque/bibliotheque-detail.html.twig', [
            'example' => $formattedExample,
            'examples' => $formattedExamples,
            'currentRoute' => $request->get('_route'),
            'currentValue' => $slug,
            'valueTitle' => $valueTitle,
            'category' => $category
        ]);
    }
}
