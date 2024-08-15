<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', []);
    }

    #[Route('/methodes', name: 'home_methodes')]
    public function methodes(): Response
    {
        return $this->render('home/methodes.html.twig');
    }

    #[Route('/le-perdant', name: 'home_perdant')]
    public function lePerdant(): Response
    {
        return $this->render('home/le_perdant.html.twig');
    }

    #[Route('/citation', name: 'home_citation')]
    public function citation(): Response
    {
        return $this->render('home/citation.html.twig');
    }

    #[Route('/bien-debuter', name: 'home_bien_debuter')]
    public function bienDebuter(): Response
    {
        return $this->render('home/bien_debuter.html.twig');
    }

    #[Route('/performance', name: 'home_performance')]
    public function performance(): Response
    {
        return $this->render('home/performance.html.twig');
    }
}
