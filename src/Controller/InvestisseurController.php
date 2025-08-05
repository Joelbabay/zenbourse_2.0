<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/investisseur')]
class InvestisseurController extends AbstractController
{
    private function checkInvestorAccess(): ?Response
    {
        $user = $this->getUser();
        if (!$user || (!$user->isInvestisseur() && !$user->hasValidTemporaryInvestorAccess())) {
            $this->addFlash('danger', 'Vous n\'avez pas accès à la méthode Investisseur');
            return $this->redirectToRoute('home');
        }
        return null;
    }

    #[Route('/outils', name: 'investisseur_outils')]
    public function outils(): Response
    {
        if ($resp = $this->checkInvestorAccess()) {
            return $resp;
        }
        return $this->render('investisseur/outils.html.twig');
    }

    #[Route('/gestion', name: 'investisseur_gestion')]
    public function gestion(): Response
    {
        if ($resp = $this->checkInvestorAccess()) {
            return $resp;
        }
        return $this->render('investisseur/gestion.html.twig');
    }
}
