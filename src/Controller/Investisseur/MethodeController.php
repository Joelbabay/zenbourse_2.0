<?php

namespace App\Controller\Investisseur;

use App\Service\CandlestickPatternService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/investisseur/la-methode')]
#[IsGranted('ROLE_INVESTISSEUR')]
class MethodeController extends AbstractController
{
    public function __construct(
        private CandlestickPatternService $candlestickPatternService
    ) {}

    #[Route('/vagues-elliott', name: 'investisseur_methode_vagues_elliot')]
    public function vaguesElliot(): Response
    {
        return $this->render('investisseur/methode/methodes-vagues-elliot.html.twig');
    }

    #[Route('/cycles-boursiers', name: 'investisseur_methode_cycles_boursiers')]
    public function cyclesBoursiers(): Response
    {
        return $this->render('investisseur/methode/methodes-cycles-boursiers.html.twig');
    }

    #[Route('/la-bulle', name: 'investisseur_methode_boites_bulles')]
    public function boitesBulles(): Response
    {
        return $this->render('investisseur/methode/methodes-bulles.html.twig');
    }

    #[Route('/indicateurs', name: 'investisseur_methode_indicateurs')]
    public function indicateurs(): Response
    {
        return $this->render('investisseur/methode/methodes-indicateurs.html.twig');
    }

    #[Route('/chandeliers-japonais', name: 'investisseur_methode_chandeliers_japonais')]
    public function chandeliersJaponais(): Response
    {
        $patterns = $this->candlestickPatternService->getAllPatterns();
        $formattedPatterns = $this->candlestickPatternService->formatPatternsForTemplate($patterns);

        return $this->render('investisseur/methode/methodes-chandeliers-japonais.html.twig', [
            'chandeliersJaponais' => $formattedPatterns,
        ]);
    }

    #[Route('/chandeliers-japonais/{slug}', name: 'investisseur_methode_chandeliers_japonais_detail')]
    public function chandeliersJaponaisDetail(string $slug, Request $request): Response
    {
        $pattern = $this->candlestickPatternService->getPatternBySlug($slug);

        if (!$pattern) {
            throw $this->createNotFoundException('Ce pattern n\'existe pas.');
        }

        $patterns = $this->candlestickPatternService->getAllPatterns();
        $formattedPatterns = $this->candlestickPatternService->formatPatternsForTemplate($patterns);
        $formattedPattern = $this->candlestickPatternService->formatPatternForTemplate($pattern);

        return $this->render('investisseur/methode/methodes-chandeliers-japonais-detail.html.twig', [
            'pattern' => $formattedPattern,
            'chandeliersJaponais' => $formattedPatterns,
            'currentRoute' => $request->get('_route'),
            'currentValue' => $slug,
        ]);
    }
}
