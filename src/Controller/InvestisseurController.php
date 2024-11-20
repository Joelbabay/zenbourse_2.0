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
            'structure' => 'Gap de continuation haussier / baissier',
            'title' => 'Chandeliers japonais – le gap de continuation',
            'image_h' => 'images/investisseur/methode/chandelier-japonais/gaph.jpg',
            'image_b' => 'images/investisseur/methode/chandelier-japonais/gapb.jpg',
            'image_name_h' => 'Le gap haussier',
            'image_name_b' => 'Le gap baissier',
            'description' => 'Le GAP de continuation s’inscrit dans une tendance validée. Il indique la poursuite de la tendance haussière / baissière.',
            'content_h' => '
                <p>La structure est formée de 2 chandeliers haussiers verts.</p>
                <ul>
                    <li>Le premier est un grand chandelier haussier vert</li>
                    <li>Le second chandelier dont le cours d’ouverture doit être supérieur au cours de clôture du chandelier précédent</li>
                    <li>Cette structure intervient généralement dans une tendance haussière après une hausse significative</li>
                </ul>
            ',
            'content_b' => '
                <p>La structure est formée de 2 chandeliers baissiers rouges.</p>
                <ul>
                    <li>Le premier est un grand chandelier baissier rouge</li>
                    <li>Le second chandelier dont le cours d’ouverture doit être inférieur au cours de clôture du chandelier précédent</li>
                    <li>Cette structure intervient généralement dans une tendance haussière après une hausse significative</li>
                </ul>
            ',
        ],
        'trois-soldats-blancs' => [
            'structure' => 'Trois soldats blancs / trois corbeaux noirs',
            'title' => 'Chandeliers japonais – Trois soldats blancs - Trois corbeaux noirs',
            'image_h' => 'images/investisseur/methode/chandelier-japonais/3sbh.jpg',
            'image_b' => 'images/investisseur/methode/chandelier-japonais/3cnb.jpg',
            'image_name_h' => 'Trois soldats blancs',
            'image_name_b' => 'Trois corbeaux noirs',
            'description' => 'Les trois soldats blancs comme les 3 corbeaux noirs sont des structures de continuation de tendance qui s’inscrivent dans une tendance validée. <br><br> Ces structures indiquent la poursuite de la tendance haussière / baissière.',
            'content_h' => '
                <p>Structure en 3 chandeliers.</p>
                <ul>
                    <li>La clôture de chaque chandelier vert doit s’effectuer au-dessus du chandelier précédent.</li>
                    <li>L’ouverture de chaque chandelier s’effectuera de préférence à l’intérieur de la partie supérieure du chandelier précédent</li>
                </ul>
            ',
            'content_b' => '
                <p>Structure en 3 chandeliers.</p>
                <ul>
                    <li>La clôture de chaque chandelier rouge doit s’effectuer au-dessous du chandelier précédent.</li>
                    <li>L’ouverture de chaque chandelier s’effectuera de préférence à l’intérieur de la partie inférieure du chandelier précédent</li>
                </ul>
            ',
        ],
        'trois-méthodes' => [
            'structure' => 'Trois méthodes haussières / baissières',
            'title' => 'Chandeliers japonais - Les trois méthodes',
            'image_h' => 'images/investisseur/methode/chandelier-japonais/3mh.jpg',
            'image_b' => 'images/investisseur/methode/chandelier-japonais/3mb.jpg',
            'image_name_h' => 'Les trois méthodes ascendantes',
            'image_name_b' => 'Les trois méthodes descendantes',
            'description' => 'Les 3 méthodes est une figure de continuation de tendance qui s’inscrit dans une tendance validée. <br><br> Cette structure indique la poursuite de la tendance haussière / baissière.',
            'content_h' => '
                <p>La structure est formée de 5 chandeliers.</p>
                <ul>
                    <li>Le premier est un grand chandelier haussier vert</li>
                    <li>Les 3 chandeliers suivants, baissiers rouges, doivent être contenus dans le range du premier chandelier. Chaque petit chandelier rouge doit clôturer plus bas que le précédent</li>
                    <li>Le dernier chandelier doit être un grand chandelier haussier vert, dont l’ouverture doit s’effectuer au-dessus de la clôture de la veille et clôturer au-dessus du plus haut du premier chandelier</li>
                </ul>
            ',
            'content_b' => '
                <p>La structure est formée de 5 chandeliers.</p>
                <ul>
                    <li>Le premier est un grand chandelier baissier rouge</li>
                    <li>Les 3 chandeliers suivants haussiers verts, doivent être contenus dans le range du premier chandelier. Chaque petit chandelier vert doit clôturer plus haut que le précédent</li>
                    <li>Le dernier chandelier doit être un grand chandelier baissier rouge, dont l’ouverture doit s’effectuer au-dessous de la clôture de la veille et clôturer au-dessous du plus bas du premier chandelier</li>
                </ul>
            ',
        ],
        'porte-drapeau' => [
            'structure' => 'Porte-drapeau haussier / inversé',
            'title' => 'Chandeliers japonais – Le porte drapeau',
            'image_h' => 'images/investisseur/methode/chandelier-japonais/pdh.jpg',
            'image_b' => 'images/investisseur/methode/chandelier-japonais/pdb.jpg',
            'image_name_h' => 'Porte drapeau haussier',
            'image_name_b' => 'Porte drapeau baissier',
            'description' => 'Le porte-drapeau est une figure de continuation de tendance qui s’inscrit dans une tendance validée. <br><br> Cette structure indique la poursuite de la tendance haussière / baissière.',
            'content_h' => '
                <p>La structure est formée de 5 chandeliers, variante de la structure des trois méthodes. Le porte-drapeau est plus puissant.</p>
                <ul>
                    <li>Le premier est un grand chandelier haussier vert suivi de trois petits chandeliers rouges</li>
                    <li>Chaque petit chandelier rouge doit clôturer plus bas que le précédent. L’ouverture du second chandelier doit s’effectuer sur un Gap haussier</li>
                    <li>Le dernier chandelier doit être un grand chandelier haussier vert. Il doit ouvrir sur un Gap haussier et clôturer au-dessus du plus haut du deuxième chandelier</li>
                </ul>
            ',
            'content_b' => '
                <p>La structure est formée de 5 chandeliers, variante de la structure des trois méthodes. Le porte-drapeau est plus puissant.</p>
                <ul>
                    <li>Le premier est un grand chandelier baissier rouge suivi de trois petits chandeliers verts</li>
                    <li>Chaque petit chandelier vert doit clôturer plus haut que le précédent. L’ouverture du second chandelier doit s’effectuer sur un Gap baissier</li>
                    <li>Le dernier chandelier doit être un grand chandelier haussier rouge. Il doit ouvrir sur un Gap baissier et clôturer au-dessous du plus bas du deuxième chandelier</li>
                </ul>
            ',
        ],
        'gapping-play-zone' => [
            'structure' => 'Gapping play en zone haute / en zone basse',
            'title' => 'Chandeliers japonais – Gapping play en zone haute / basse',
            'image_h' => 'images/investisseur/methode/chandelier-japonais/gpzh.jpg',
            'image_b' => 'images/investisseur/methode/chandelier-japonais/gpzb.jpg',
            'image_name_h' => 'Gapping play en zone haute',
            'image_name_b' => 'Gapping play en zone basse',
            'description' => 'Le Gapping play en zone haute / basse est une figure de continuation de tendance qui s’inscrit dans une tendance validée. <br><br> Cette structure particulièrement puissante indique la poursuite de la tendance haussière / baissière.',
            'content_h' => '
                <p>La structure est formée de plusieurs chandeliers.</p>
                <ul>
                    <li>Le premier est un grand chandelier haussier vert</li>
                    <li>La succession de petits chandeliers horizontaux verts ou rouges est contenu à l’intérieur du range du premier chandelier</li>
                    <li>Le dernier chandelier doit être un grand chandelier vert et ouvrir en gap haussier au-dessus de la clôture des chandeliers précédents</li>
                </ul>
            ',
            'content_b' => '
                <p>La structure est formée de plusieurs chandeliers.</p>
                <ul>
                    <li>Le premier est un grand chandelier baissier rouge</li>
                    <li>La succession de petits chandeliers horizontaux rouges ou verts est contenu à l’intérieur du range du premier chandelier</li>
                    <li>Le dernier chandelier doit être un grand chandelier rouge et ouvrir en gap baissier au-dessous de la clôture des chandeliers précédents</li>
                </ul>
            ',
        ],
        'trois-lignes-brisées' => [
            'structure' => 'Trois lignes brisées haussières / baissières',
            'title' => 'Chandeliers japonais - Trois lignes brisées',
            'image_h' => 'images/investisseur/methode/chandelier-japonais/3lbh.jpg',
            'image_b' => 'images/investisseur/methode/chandelier-japonais/3lbb.jpg',
            'image_name_h' => 'Trois lignes brisées haussières',
            'image_name_b' => 'Trois lignes brisées baissières',
            'description' => 'Les trois lignes brisées est une figure de continuation de tendance qui s’inscrit dans une tendance validée. <br><br> Cette structure indique la poursuite de la tendance haussière / baissière.',
            'content_h' => '
                <p>La structure est formée de 5 chandeliers.</p>
                <ul>
                    <li>Succession de trois petits chandeliers verts dont la clôture s’effectue au-dessus du chandelier précédent</li>
                    <li>Le quatrième chandelier est un grand chandelier rouge qui englobe les 3 chandeliers verts. La clôture doit être inférieure au cours d’ouverture du premier chandelier vert</li>
                </ul>
            ',
            'content_b' => '
                <p>La structure est formée de 5 chandeliers.</p>
                <ul>
                    <li>Succession de trois petits chandeliers rouges dont la clôture s’effectue au-dessus du chandelier précédent</li>
                    <li>Le quatrième chandelier est un grand chandelier vert qui englobe les 3 chandeliers rouges. La clôture doit être supérieure au cours d’ouverture du premier chandelier rouge</li>
                </ul>
            ',
        ],
        'gapping-play' => [
            'structure' => 'Gapping play haussier / baissier',
            'title' => 'Chandeliers japonais – le Gapping play',
            'image_h' => 'images/investisseur/methode/chandelier-japonais/gph.png',
            'image_b' => 'images/investisseur/methode/chandelier-japonais/gpb.png',
            'image_name_h' => 'Trois lignes brisées haussières',
            'image_name_b' => 'Trois lignes brisées baissières',
            'description' => 'Les trois lignes brisées est une figure de continuation de tendance qui s’inscrit dans une tendance validée. <br><br> Cette structure indique la poursuite de la tendance haussière / baissière.',
            'content_h' => '
                <p>La structure est formée de 3 chandeliers.</p>
                <ul>
                    <li>Les 2 premiers chandeliers verts forment une zone d’hésitation</li>
                    <li>Le dernier chandelier doit être un grand chandelier vert et doit ouvrir en gap haussier</li>
                </ul>
                <strong>Lorsque le premier chandelier est rouge, la structure est plus puissante</strong>
            ',
            'content_b' => '
                <p>La structure est formée de 3 chandeliers.</p>
                <ul>
                    <li>Les 2 premiers chandeliers rouges forment une zone d’hésitation</li>
                    <li>Le dernier chandelier doit être un grand chandelier rouge et doit ouvrir en gap baissier</li>
                </ul>
                <strong>Lorsque le premier chandelier est vert, la structure est plus puissante</strong>
            ',
        ],
        'tasuki-gap' => [
            'structure' => 'Tasuki gap haussier / baissier',
            'title' => 'Chandeliers japonais – Le Tasuki gap',
            'image_h' => 'images/investisseur/methode/chandelier-japonais/tgh.jpg',
            'image_b' => 'images/investisseur/methode/chandelier-japonais/tgb.jpg',
            'image_name_h' => 'Tasuki gap haussier',
            'image_name_b' => 'Tasuki gap baissier',
            'description' => 'Les Tasuki gap est une figure de continuation de tendance qui s’inscrit dans une tendance validée. <br><br> Cette structure indique la poursuite de la tendance haussière / baissière.',
            'content_h' => '
                <p>La structure est formée de 3 chandeliers.</p>
                <ul>
                    <li>Le premier est un grand chandelier haussier vert</li>
                    <li>Le second chandelier également haussier doit ouvrir en gap</li>
                    <li>Le troisième chandelier baissier comble partiellement le gap en clôturant à l’intérieur du gap</li>
                </ul>
            ',
            'content_b' => '
                <p>La structure est formée de 3 chandeliers.</p>
                <ul>
                    <li>Le premier est un grand chandelier baissier rouge</li>
                    <li>Le troisième chandelier haussier comble partiellement le gap en clôturant à l’intérieur du gap</li>
                </ul>
            ',
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

    #[Route('/la-methode/chandeliers-japonais/{value}', name: 'investisseur_methode_chandeliers_japonais_value')]
    public function investisseur_methode_chandeliers_japonais_value(string $value, Request $request): Response
    {
        if (!array_key_exists($value, $this->chandeliersJaponais)) {
            throw $this->createNotFoundException('Cette valeur n\'existe pas.');
        }

        $data = $this->chandeliersJaponais[$value];
        $currentRoute = $request->get('_route');
        $currentValue = $request->get('value');

        return $this->render('investisseur/methode/methodes-chandeliers-japonais-values.html.twig', [
            'structure' => $data['structure'],
            'title' => $data['title'],
            'image_h' => $data['image_h'],
            'image_b' => $data['image_b'],
            'image_name_h' => $data['image_name_h'],
            'image_name_b' => $data['image_name_b'],
            'description' => $data['description'],
            'content_h' => $data['content_h'],
            'content_b' => $data['content_b'],
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
