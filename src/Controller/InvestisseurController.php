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

    #[Route('/la-methode/chandeliers-japonais', name: 'investisseur_methode_chandeliers_japonais')]
    public function chandeliersJaponais(): Response
    {
        return $this->render('investisseur/methode/chandeliers-japonais.html.twig');
    }

    // Bibliothèque
    #[Route('/bibliotheque', name: 'investisseur_bibliotheque')]
    public function bibliotheque(): Response
    {
        return $this->render('investisseur/bibliotheque.html.twig');
    }

    #[Route('/bibliotheque/bulles-type-1', name: 'investisseur_bibliotheque_bulles-type-1')]
    public function bullesType1(): Response
    {
        return $this->render('investisseur/bibliotheque/bulles-type-1.html.twig');
    }

    #[Route('/bibliotheque/bulles-type-2', name: 'investisseur_bibliotheque_bulles-type-2')]
    public function bullesType2(): Response
    {
        return $this->render('investisseur/bibliotheque/bulles-type-2.html.twig');
    }

    #[Route('/bibliotheque/ramassage', name: 'investisseur_bibliotheque_ramassage')]
    public function ramassage(): Response
    {
        return $this->render('investisseur/bibliotheque/ramassage.html.twig');
    }

    #[Route('/bibliotheque/ramassage-pic', name: 'investisseur_bibliotheque_ramassage-pic')]
    public function ramassagePic(): Response
    {
        return $this->render('investisseur/bibliotheque/ramassage-pic.html.twig');
    }

    #[Route('/bibliotheque/pic-ramassage', name: 'investisseur_bibliotheque_pic-ramassage')]
    public function picRamassage(): Response
    {
        return $this->render('investisseur/bibliotheque/pic-ramassage.html.twig');
    }

    #[Route('/bibliotheque/pics-de-volumes', name: 'investisseur_bibliotheque_pics-de-volumes')]
    public function picsDeVolumes(): Response
    {
        return $this->render('investisseur/bibliotheque/pics-de-volumes.html.twig');
    }

    #[Route('/bibliotheque/volumes-faibles', name: 'investisseur_bibliotheque_volumes-faibles')]
    public function volumesFaibles(): Response
    {
        return $this->render('investisseur/bibliotheque/volumes-faibles.html.twig');
    }

    #[Route('/bibliotheque/introductions', name: 'investisseur_bibliotheque_introductions')]
    public function introductions(): Response
    {
        return $this->render('investisseur/bibliotheque/introductions.html.twig');
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
