<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/intraday')]
class IntradayController extends AbstractController
{
    #[Route('/', name: 'intraday')]
    public function index(): Response
    {
        return $this->redirectToRoute('intraday_presentation');
    }

    #[Route('/presentation', name: 'intraday_presentation')]
    public function intraday_presentation(): Response
    {
        return $this->render('intraday/index.html.twig', [
            'controller_name' => 'IntradayController',
        ]);
    }

    #[Route('/methode', name: 'intraday_methode')]
    public function intraday_methode(): Response
    {
        return $this->render('intraday/index.html.twig', [
            'controller_name' => 'IntradayController',
        ]);
    }

    #[Route('/bibliotheque', name: 'intraday_bibliotheque')]
    public function intraday_bibliotheque(): Response
    {
        return $this->render('intraday/index.html.twig', [
            'controller_name' => 'IntradayController',
        ]);
    }

    #[Route('/outils', name: 'intraday_outils')]
    public function intraday_outils(): Response
    {
        return $this->render('intraday/index.html.twig', [
            'controller_name' => 'IntradayController',
        ]);
    }

    #[Route('/gestions', name: 'intraday_gestion')]
    public function intraday_gestion(): Response
    {
        return $this->render('intraday/index.html.twig', [
            'controller_name' => 'IntradayController',
        ]);
    }
}
