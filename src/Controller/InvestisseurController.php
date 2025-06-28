<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/investisseur')]
#[IsGranted('ROLE_INVESTISSEUR')]
class InvestisseurController extends AbstractController
{
    #[Route('/', name: 'investisseur_home')]
    public function index(): Response
    {
        return $this->render('investisseur/index.html.twig', [
            'controller_name' => 'InvestisseurController',
        ]);
    }

    #[Route('/presentation', name: 'investisseur_presentation')]
    public function presentation(): Response
    {
        return $this->render('investisseur/presentation.html.twig');
    }

    #[Route('/la-methode', name: 'investisseur_la-methode')]
    public function investisseur_methode(): Response
    {
        return $this->render('investisseur/methode.html.twig');
    }

    // Sous-sections de la méthode
    #[Route('/la-methode/vagues-elliott', name: 'investisseur_la-methode_vague-d-elliot')]
    public function vaguesElliot(): Response
    {
        return $this->render('investisseur/methode/vagues-elliott.html.twig');
    }

    #[Route('/la-methode/cycles-boursiers', name: 'investisseur_la-methode_cycles-boursiers')]
    public function cyclesBoursiers(): Response
    {
        return $this->render('investisseur/methode/cycles-boursiers.html.twig');
    }

    #[Route('/la-methode/la-bulle', name: 'investisseur_la-methode_la-bulle')]
    public function laBulle(): Response
    {
        return $this->render('investisseur/methode/la-bulle.html.twig');
    }

    #[Route('/la-methode/indicateurs', name: 'investisseur_la-methode_indicateurs')]
    public function indicateurs(): Response
    {
        return $this->render('investisseur/methode/indicateurs.html.twig');
    }

    #[Route('/outils', name: 'investisseur_outils')]
    public function outils(): Response
    {
        return $this->render('investisseur/outils.html.twig');
    }

    #[Route('/gestion', name: 'investisseur_gestion')]
    public function gestion(): Response
    {
        return $this->render('investisseur/gestion.html.twig');
    }

    #[Route('/introduction', name: 'investisseur_introduction')]
    public function introduction(): Response
    {
        return $this->render('investisseur/introduction.html.twig');
    }
}
