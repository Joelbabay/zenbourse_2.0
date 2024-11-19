<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/investisseur')]
#[IsGranted('ROLE_INVESTISSEUR')]
class InvestisseurController extends AbstractController
{
    private array $bullesType1Data = [
        'exp-world-hld' => [
            'title' => 'EXP WORLD HLD',
            'flag' => 'us',
            'ticker' => 'EXPI',
            'image_jour' => '/images/investisseur/bibliotheque/bulle-type-1/exp-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/bulle-type-1/exp-s.jpg',
            'description' => 'Description spécifique pour EXP WORLD HLD.',
        ],
        'magnite-inc' => [
            'title' => 'MAGNITE INC',
            'flag' => 'us',
            'ticker' => 'MGNI',
            'image_jour' => '/images/investisseur/bibliotheque/bulle-type-1/magnite-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/bulle-type-1/magnite-s.jpg',
            'description' => 'Description spécifique pour MAGNITE INC.',
        ],
        'organogenesis-hld' => [
            'title' => 'ORGANOGENESIS HLD',
            'flag' => 'us',
            'ticker' => 'ORGO',
            'image_jour' => '/images/investisseur/bibliotheque/bulle-type-1/organogenesis-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/bulle-type-1/organogenesis-s.jpg',
            'description' => 'Description spécifique pour ORGANOGENESIS HLD.',
        ],
        'pacific-biosciences' => [
            'title' => 'PACIFIC BIOSCIENCES OF CALIFORNIA',
            'flag' => 'us',
            'ticker' => 'PACB',
            'image_jour' => '/images/investisseur/bibliotheque/bulle-type-1/pacific-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/bulle-type-1/pacific-s.jpg',
            'description' => 'Description spécifique pour PACIFIC BIOSCIENCES.',
        ],
        'riot-platforms-inc' => [
            'title' => 'RIOT PLATFORMS INC',
            'flag' => 'us',
            'ticker' => 'RIOT',
            'image_jour' => '/images/investisseur/bibliotheque/bulle-type-1/riot-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/bulle-type-1/riot-s.jpg',
            'description' => 'Description spécifique pour RIOT PLATFORMS INC.',
        ],
    ];

    private array  $bullesType2Data = [
        'amc-networks' => [
            'title' => 'AMC NETWORKS INC',
            'flag' => 'us',
            'ticker' => 'AMCX',
            'image_jour' => '/images/investisseur/bibliotheque/bulle-type-2/amc-networks-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/bulle-type-2/amc-networks-s.jpg',
            'description' => 'Description spécifique pour AMC NETWORKS INC  -  AMCX.',
        ],
        'establishment' => [
            'title' => 'ESTABLISHMENT LABS HLD',
            'flag' => 'us',
            'ticker' => 'ESTA',
            'image_jour' => '/images/investisseur/bibliotheque/bulle-type-2/establishment-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/bulle-type-2/establishment-s.jpg',
            'description' => 'Description spécifique pour ESTABLISHMENT LABS HLD  -  ESTA.',
        ],
        'fastned' => [
            'title' => 'FASTNED',
            'flag' => 'nl',
            'ticker' => 'FAST',
            'image_jour' => '/images/investisseur/bibliotheque/bulle-type-2/fastned-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/bulle-type-2/fastned-s.jpg',
            'description' => 'Description spécifique pour FASTNED  -  FAST.',
        ],
        'futu' => [
            'title' => 'FUTU HOLDINGS LTD',
            'flag' => 'us',
            'ticker' => 'FUTU',
            'image_jour' => '/images/investisseur/bibliotheque/bulle-type-2/futu-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/bulle-type-2/futu-s.jpg',
            'description' => 'Description spécifique pour FUTU HOLDINGS LTD  -  FUTU.',
        ],
        'witbe' => [
            'title' => 'WITBE ',
            'flag' => 'fr',
            'ticker' => 'ALWIT',
            'image_jour' => '/images/investisseur/bibliotheque/bulle-type-2/witbe-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/bulle-type-2/witbe-s.jpg',
            'description' => 'Description spécifique pour WITBE  -  ALWIT.',
        ],
    ];

    private array $chandeliersJaponais = [
        'gap-de-continuation' => [
            'title' => 'Gap de continuation haussier / baissier',
            'content' => '
                <h2>Chandeliers japonais – le gap de continuation</h2>
                <div class="container-fluid bg-light mb-4 p-3">
                    <p> Le GAP de continuation s’inscrit dans une tendance validée. Il indique la poursuite de la tendance haussière / baissière. </p>   
				</div>
                <div class="row">
                    <div class="col-md-6 d-flex">
                        <div class="card p-4 d-flex flex-column justify-content-between border-0">
                            <h5>Le gap haussier</h5>
                            <a class="image-popup-no-margins" href="{{asset(\'images/investisseur/methode/chandelier-japonais/gaph.jpg\')}}">
                                <img src="{{asset(\'images/investisseur/methode/chandelier-japonais/gaph.jpg\')}}" class="img-fluid rounded mx-auto d-block" alt="La boîte de DARVAS">
                            </a>
                            <strong>Caractéristiques de la structure :</strong>
                            <p>La structure est formée de 2 chandeliers haussiers verts.</p>
                            <ul>
                                <li>Le premier est un grand chandelier haussier vert</li>
                                <li>Le second chandelier dont le cours d’ouverture doit être supérieur au cours de clôture du chandelier précédent</li>
                                <li>Cette structure intervient généralement dans une tendance haussière après une hausse significative</li>
                            </ul>
                            <strong>Invalidation de la structure :</strong>
                        </div>
                    </div>

                    <div class="col-md-6 d-flex">
                        <div class="card p-4 d-flex flex-column justify-content-between border-0">
                            <h5>Le gap baissier</h5>
                            <a class="image-popup-no-margins" href="{{ asset(\'images/investisseur/methode/chandelier-japonais/gapb.jpg\') }}">
                                <img src="{{asset(\'images/investisseur/methode/chandelier-japonais/gapb.jpg\')}}" class="img-fluid rounded mx-auto d-block" alt="La boîte de DARVAS">
                            </a>
                            <strong>Caractéristiques de la structure :</strong>
                            <p>La structure est formée de 2 chandeliers baissiers rouges.</p>
                            <ul>
                                <li>Le premier est un grand chandelier baissier rouge</li>
                                <li>Le second chandelier dont le cours d’ouverture doit être inférieur au cours de clôture du chandelier précédent</li>
                                <li>Cette structure intervient généralement dans une tendance haussière après une hausse significative</li>
                            </ul>
                            <strong>Invalidation de la structure :</strong>
                        </div>
                    </div>
                </div>
            ',
        ],
        'trois-soldats-blancs' => [
            'title' => 'Trois soldats blancs / trois corbeaux noirs',
            'content' => '
                <h2>Chandeliers japonais – le gap de continuation</h2>
                <div class="container-fluid bg-light mb-4 p-3">
                    <p> Le GAP de continuation s’inscrit dans une tendance validée. Il indique la poursuite de la tendance haussière / baissière. </p>   
				</div>
                <div class="row">
                    <div class="col-md-6 d-flex">
                        <div class="card p-4 d-flex flex-column justify-content-between border-0">
                            <h5>Le gap haussier</h5>
                            <a class="image-popup-no-margins" href="{{asset(\'images/investisseur/methode/chandelier-japonais/gaph.jpg\')}}">
                                <img src="{{asset(\'images/investisseur/methode/chandelier-japonais/gaph.jpg\')}}" class="img-fluid rounded mx-auto d-block" alt="La boîte de DARVAS">
                            </a>
                            <strong>Caractéristiques de la structure :</strong>
                            <p>La structure est formée de 2 chandeliers haussiers verts.</p>
                            <ul>
                                <li>Le premier est un grand chandelier haussier vert</li>
                                <li>Le second chandelier dont le cours d’ouverture doit être supérieur au cours de clôture du chandelier précédent</li>
                                <li>Cette structure intervient généralement dans une tendance haussière après une hausse significative</li>
                            </ul>
                            <strong>Invalidation de la structure :</strong>
                        </div>
                    </div>

                    <div class="col-md-6 d-flex">
                        <div class="card p-4 d-flex flex-column justify-content-between border-0">
                            <h5>Le gap baissier</h5>
                            <a class="image-popup-no-margins" href="{{ asset(\'images/investisseur/methode/chandelier-japonais/gapb.jpg\') }}">
                                <img src="{{asset(\'images/investisseur/methode/chandelier-japonais/gapb.jpg\')}}" class="img-fluid rounded mx-auto d-block" alt="La boîte de DARVAS">
                            </a>
                            <strong>Caractéristiques de la structure :</strong>
                            <p>La structure est formée de 2 chandeliers baissiers rouges.</p>
                            <ul>
                                <li>Le premier est un grand chandelier baissier rouge</li>
                                <li>Le second chandelier dont le cours d’ouverture doit être inférieur au cours de clôture du chandelier précédent</li>
                                <li>Cette structure intervient généralement dans une tendance haussière après une hausse significative</li>
                            </ul>
                            <strong>Invalidation de la structure :</strong>
                        </div>
                    </div>
                </div>
            ',
        ],
        'trois-méthodes' => [
            'title' => 'Trois méthodes haussières / baissières',
            'content' => '
                <h2>Chandeliers japonais – le gap de continuation</h2>
                <div class="container-fluid bg-light mb-4 p-3">
                    <p> Le GAP de continuation s’inscrit dans une tendance validée. Il indique la poursuite de la tendance haussière / baissière. </p>   
				</div>
                <div class="row">
                    <div class="col-md-6 d-flex">
                        <div class="card p-4 d-flex flex-column justify-content-between border-0">
                            <h5>Le gap haussier</h5>
                            <a class="image-popup-no-margins" href="{{asset(\'images/investisseur/methode/chandelier-japonais/gaph.jpg\')}}">
                                <img src="{{asset(\'images/investisseur/methode/chandelier-japonais/gaph.jpg\')}}" class="img-fluid rounded mx-auto d-block" alt="La boîte de DARVAS">
                            </a>
                            <strong>Caractéristiques de la structure :</strong>
                            <p>La structure est formée de 2 chandeliers haussiers verts.</p>
                            <ul>
                                <li>Le premier est un grand chandelier haussier vert</li>
                                <li>Le second chandelier dont le cours d’ouverture doit être supérieur au cours de clôture du chandelier précédent</li>
                                <li>Cette structure intervient généralement dans une tendance haussière après une hausse significative</li>
                            </ul>
                            <strong>Invalidation de la structure :</strong>
                        </div>
                    </div>

                    <div class="col-md-6 d-flex">
                        <div class="card p-4 d-flex flex-column justify-content-between border-0">
                            <h5>Le gap baissier</h5>
                            <a class="image-popup-no-margins" href="{{ asset(\'images/investisseur/methode/chandelier-japonais/gapb.jpg\') }}">
                                <img src="{{asset(\'images/investisseur/methode/chandelier-japonais/gapb.jpg\')}}" class="img-fluid rounded mx-auto d-block" alt="La boîte de DARVAS">
                            </a>
                            <strong>Caractéristiques de la structure :</strong>
                            <p>La structure est formée de 2 chandeliers baissiers rouges.</p>
                            <ul>
                                <li>Le premier est un grand chandelier baissier rouge</li>
                                <li>Le second chandelier dont le cours d’ouverture doit être inférieur au cours de clôture du chandelier précédent</li>
                                <li>Cette structure intervient généralement dans une tendance haussière après une hausse significative</li>
                            </ul>
                            <strong>Invalidation de la structure :</strong>
                        </div>
                    </div>
                </div>
            ',
        ],
        'porte-drapeau' => [
            'title' => 'Porte-drapeau haussier / inversé',
            'content' => ' ',
        ],
        'gapping-play-zone' => [
            'title' => 'Gapping play en zone haute / en zone basse',
            'content' => ' ',
        ],
        'trois-lignes-brisées' => [
            'title' => 'Trois lignes brisées haussières / baissières',
            'content' => ' ',
        ],
        'gapping-play' => [
            'title' => 'Gapping play haussier / baissier',
            'content' => ' ',
        ],
        'tasuki-gap' => [
            'title' => 'Tasuki gap haussier / baissier',
            'content' => ' ',
        ],

    ];

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

    #[Route('/la-methode', name: 'investisseur_methode')]
    public function investisseur_methode(): Response
    {
        return $this->render('investisseur/methode.html.twig');
    }

    #[Route('/la-methode/vagues-elliott', name: 'investisseur_methode_vagues_elliot')]
    public function investisseur_methode_vagues_elliot(): Response
    {
        return $this->render('investisseur/methode/methodes-vagues-elliot.html.twig');
    }

    #[Route('/la-methode/cycles-boursiers', name: 'investisseur_methode_cycles_boursiers')]
    public function investisseur_methode_cycles_boursiers(): Response
    {
        return $this->render('investisseur/methode/methodes-cycles-boursiers.html.twig');
    }

    #[Route('/la-methode/la-bulle', name: 'investisseur_methode_boites_bulles')]
    public function investisseur_methode_boites_bulles(): Response
    {
        return $this->render('investisseur/methode/methodes-bulles.html.twig');
    }

    #[Route('/la-methode/indicateurs', name: 'investisseur_methode_indicateurs')]
    public function investisseur_methode_indicateurs(): Response
    {
        return $this->render('investisseur/methode/methodes-indicateurs.html.twig', []);
    }

    #[Route('/la-methode/chandeliers-japonais', name: 'investisseur_methode_chandeliers_japonais')]
    public function investisseur_methode_chandeliers_japonais(): Response
    {
        return $this->render('investisseur/methode/methodes-chandeliers-japonais.html.twig', [
            'chandeliersJaponais' => $this->chandeliersJaponais,
        ]);
    }

    #[Route('/bibliotheque/chandeliers-japonais/{value}', name: 'investisseur_methode_chandeliers_japonais_value')]
    public function investisseur_methode_chandeliers_japonais_value(string $value, Request $request): Response
    {
        if (!array_key_exists($value, $this->chandeliersJaponais)) {
            throw $this->createNotFoundException('Cette valeur n\'existe pas.');
        }

        $data = $this->chandeliersJaponais[$value];
        $currentRoute = $request->get('_route');
        $currentValue = $request->get('value');

        return $this->render('investisseur/methode/methodes-chandeliers-japonais-values.html.twig', [
            'title' => $data['title'],
            //'content' => $data['content'],
            'chandeliersJaponais' => $this->chandeliersJaponais,
            'currentRoute' => $currentRoute,
            'currentValue' => $currentValue,
        ]);
    }

    #[Route('/bibliotheque', name: 'investisseur_bibliotheque')]
    public function bibliotheque(): Response
    {
        return $this->render('investisseur/bibliotheque.html.twig');
    }

    #[Route('/bibliotheque/bulles-type-1', name: 'investisseur_bibliotheque_bulles_type_1')]
    public function investisseur_bibliotheque_bulles_type_1(): Response
    {
        return $this->render('investisseur/bibliotheque/bibliotheque-bulles.html.twig', [
            'bullesTypeData' => $this->bullesType1Data,
        ]);
    }

    #[Route('/bibliotheque/bulles-type-1/{value}', name: 'investisseur_bibliotheque_bulles_type_1_value')]
    public function bullesType1(string $value, Request $request): Response
    {
        // Vérifie si la valeur sélectionnée existe dans les données
        if (!array_key_exists($value, $this->bullesType1Data)) {
            throw $this->createNotFoundException('Cette valeur n\'existe pas.');
        }

        $data = $this->bullesType1Data[$value];
        $currentRoute = $request->get('_route');
        $currentValue = $request->get('value');

        return $this->render('investisseur/bibliotheque/bibliotheque-bulles-value.html.twig', [
            'title' => $data['title'],
            'ticker' => $data['ticker'],
            'flag' => $data['flag'],
            'image_jour' => $data['image_jour'],
            'image_semaine' => $data['image_semaine'],
            'description' => $data['description'],
            'bullesTypeData' => $this->bullesType1Data,
            'currentRoute' => $currentRoute,
            'currentValue' => $currentValue,
        ]);
    }

    #[Route('/bibliotheque/bulles-type-2', name: 'investisseur_bibliotheque_bulles_type_2')]
    public function investisseur_bibliotheque_bulles_type_2(): Response
    {
        return $this->render('investisseur/bibliotheque/bibliotheque-bulles-type-2.html.twig', [
            'bullesType2Data' => $this->bullesType2Data,
        ]);
    }

    #[Route('/bibliotheque/bulles-type-2/{value}', name: 'investisseur_bibliotheque_bulles_type_2_value')]
    public function bullesType2(string $value, Request $request): Response
    {
        // Vérifie si la valeur sélectionnée existe dans les données
        if (!array_key_exists($value, $this->bullesType2Data)) {
            throw $this->createNotFoundException('Cette valeur n\'existe pas.');
        }

        $data = $this->bullesType2Data[$value];
        $currentRoute = $request->get('_route');
        $currentValue = $request->get('value');

        return $this->render('investisseur/bibliotheque/bibliotheque-bulles-value.html.twig', [
            'title' => $data['title'],
            'ticker' => $data['ticker'],
            'flag' => $data['flag'],
            'image_jour' => $data['image_jour'],
            'image_semaine' => $data['image_semaine'],
            'description' => $data['description'],
            'bullesTypeData' => $this->bullesType2Data,
            'currentRoute' => $currentRoute,
            'currentValue' => $currentValue,
        ]);
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