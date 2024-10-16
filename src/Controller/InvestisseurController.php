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
    #[Route('/accueil', name: 'investisseur_home')]
    public function index(): Response
    {
        return $this->render('investisseur/index.html.twig', [
            'controller_name' => 'InvestisseurController',
        ]);
    }

    #[Route('/presentation', name: 'investisseur_presentation')]
    public function presentation(): Response
    {
        return $this->render('investisseur/index.html.twig');
    }

    #[Route('/la-methode', name: 'investisseur_methode')]
    public function investisseur_methode(): Response
    {
        return $this->render('investisseur/index.html.twig');
    }

    #[Route('/la-methode/vagues-elliot', name: 'investisseur_methode_vagues_elliot')]
    public function investisseur_methode_vagues_elliot(): Response
    {
        return $this->render('investisseur/methode/methodes-vagues-elliot.html.twig');
    }

    #[Route('/la-methode/cycles-boursiers', name: 'investisseur_methode_cycles_boursiers')]
    public function investisseur_methode_cycles_boursiers(): Response
    {
        return $this->render('investisseur/methode/methodes-cycles-boursiers.html.twig');
    }

    #[Route('/la-methode/boites-bulles', name: 'investisseur_methode_boites_bulles')]
    public function investisseur_methode_boites_bulles(): Response
    {
        return $this->render('investisseur/methode/methodes-boites-bulles.html.twig');
    }

    #[Route('/la-methode/indicateurs', name: 'investisseur_methode_indicateurs')]
    public function investisseur_methode_indicateurs(): Response
    {
        return $this->render('investisseur/methode/methodes-indicateurs.html.twig', []);
    }

    #[Route('/bibliotheque', name: 'investisseur_bibliotheque')]
    public function bibliotheque(): Response
    {
        return $this->render('investisseur/bibliotheque.html.twig');
    }

    #[Route('/bibliotheque/bulles', name: 'investisseur_bibliotheque_bulles')]
    public function investisseur_bibliotheque_bulles(): Response
    {
        return $this->render('investisseur/bibliotheque/bibliothequePicVolume.html.twig', []);
    }

    #[Route('/bibliotheque/bulles-range', name: 'investisseur_bibliotheque_bulles_range')]
    public function investisseur_bibliotheque_bulles_range(): Response
    {
        return $this->render('investisseur/bibliotheque/bibliothequePicVolume.html.twig', []);
    }

    #[Route('/bibliotheque/pics-de-volume', name: 'investisseur_bibliotheque_pics_volumes')]
    public function investisseur_bibliotheque_pics_volumes(): Response
    {
        return $this->render('investisseur/bibliotheque/bibliothequePicVolume.html.twig', []);
    }
    #[Route('/bibliotheque/ramassage', name: 'investisseur_bibliotheque_ramasssage')]
    public function investisseur_bibliotheque_ramasssage(): Response
    {
        return $this->render('investisseur/bibliotheque/bibliotheque-ramassage.html.twig', []);
    }

    #[Route('/bibliotheque/ramassage-pic', name: 'investisseur_bibliotheque_ramasssage_pic')]
    public function investisseur_bibliotheque_ramasssage_pic(): Response
    {
        return $this->render('investisseur/bibliotheque/bibliotheque-ramassage-pic.html.twig', []);
    }

    #[Route('/bibliotheque/pic-ramassage', name: 'investisseur_bibliotheque_pic_ramassage')]
    public function investisseur_bibliotheque_pic_ramassage(): Response
    {
        return $this->render('investisseur/bibliotheque/bibliotheque-pic-ramassage.html.twig', []);
    }

    #[Route('/bibliotheque/volumes-faibles', name: 'investisseur_bibliotheque_volumes_faibles')]
    public function investisseur_bibliotheque_volumes_faibles(): Response
    {
        return $this->render('investisseur/bibliotheque/bibliotheque-volumes-faibles.html.twig', []);
    }

    #[Route('/bibliotheque/introduction', name: 'investisseur_bibliotheque_introduction')]
    public function investisseur_bibliotheque_introduction(): Response
    {
        return $this->render('investisseur/bibliotheque/bibliotheque-introduction.html.twig', []);
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

    #[Route('/methode-pic', name: 'investisseur_methode_pic')]
    public function methodePic(): Response
    {
        return $this->render('investisseur/introduction.html.twig');
    }

    #[Route('/methode-ramassage', name: 'investisseur_methode_ramassage')]
    public function investisseur_methode_ramassage(): Response
    {
        return $this->render('investisseur/introduction.html.twig');
    }

    #[Route('/methode-intro', name: 'investisseur_methode_intro')]
    public function investisseur_methode_intro(): Response
    {
        return $this->render('investisseur/introduction.html.twig');
    }
}