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
        'prophase' => [
            'title' => 'PROPHASE LABS INC',
            'flag' => 'us',
            'ticker' => 'PRPH',
            'image_jour' => '/images/investisseur/bibliotheque/bulle-type-1/prophase-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/bulle-type-1/prophase-s.jpg',
            'description' => 'Description spécifique pour PROPHASE LABS INC',
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
            'description' => 'Description spécifique pour AMC NETWORKS INC.',
        ],
        'establishment' => [
            'title' => 'ESTABLISHMENT LABS HLD',
            'flag' => 'us',
            'ticker' => 'ESTA',
            'image_jour' => '/images/investisseur/bibliotheque/bulle-type-2/establishment-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/bulle-type-2/establishment-s.jpg',
            'description' => 'Description spécifique pour ESTABLISHMENT LABS HLD.',
        ],
        'fastned' => [
            'title' => 'FASTNED',
            'flag' => 'nl',
            'ticker' => 'FAST',
            'image_jour' => '/images/investisseur/bibliotheque/bulle-type-2/fastned-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/bulle-type-2/fastned-s.jpg',
            'description' => 'Description spécifique pour FASTNED.',
        ],
        'futu' => [
            'title' => 'FUTU HOLDINGS LTD',
            'flag' => 'us',
            'ticker' => 'FUTU',
            'image_jour' => '/images/investisseur/bibliotheque/bulle-type-2/futu-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/bulle-type-2/futu-s.jpg',
            'description' => 'Description spécifique pour FUTU HOLDINGS LTD.',
        ],
        'witbe' => [
            'title' => 'WITBE ',
            'flag' => 'fr',
            'ticker' => 'ALWIT',
            'image_jour' => '/images/investisseur/bibliotheque/bulle-type-2/witbe-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/bulle-type-2/witbe-s.jpg',
            'description' => 'Description spécifique pour WITBE.',
        ],
    ];

    private array  $ramassageData = [
        'beyond' => [
            'title' => 'BEYOND INC',
            'flag' => 'us',
            'ticker' => 'BYON',
            'image_jour' => '/images/investisseur/bibliotheque/ramassage/beyond-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/ramassage/beyond-s.jpg',
            'description' => 'Description spécifique pour BEYOND INC',
        ],
        'jumia' => [
            'title' => 'JUMIA TECHNOLOGIES',
            'flag' => 'us',
            'ticker' => 'JMIA',
            'image_jour' => '/images/investisseur/bibliotheque/ramassage/jumia-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/ramassage/jumia-s.jpg',
            'description' => 'Description spécifique pour JUMIA TECHNOLOGIES',
        ],
        'microstrategy' => [
            'title' => 'MICROSTRATEGY INC',
            'flag' => 'us',
            'ticker' => 'MSTR',
            'image_jour' => '/images/investisseur/bibliotheque/ramassage/microstrategy-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/ramassage/microstrategy-s.jpg',
            'description' => 'Description spécifique pour MICROSTRATEGY INC',
        ],
        'nio' => [
            'title' => 'NIO INC',
            'flag' => 'us',
            'ticker' => 'NIO',
            'image_jour' => '/images/investisseur/bibliotheque/ramassage/nio-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/ramassage/nio-s.jpg',
            'description' => 'Description spécifique pour NIO INC',
        ],
        'weebit-nano' => [
            'title' => 'WEEBIT NANO LTD',
            'flag' => 'au',
            'ticker' => 'WBT',
            'image_jour' => '/images/investisseur/bibliotheque/ramassage/weebit-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/ramassage/weebit-s.jpg',
            'description' => 'Description spécifique pour WEEBIT NANO LTD',
        ],
    ];

    private array  $ramassagePicData = [
        'big-sporting' => [
            'title' => 'BIG 5 SPORTING GOODS',
            'flag' => 'us',
            'ticker' => 'BGFV',
            'image_jour' => '/images/investisseur/bibliotheque/ramassage-pic/big5-sporting-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/ramassage-pic/big5-sporting-s.jpg',
            'description' => 'Description spécifique pour BIG 5 SPORTING GOODS',
        ],
        'himax' => [
            'title' => 'HIMAX TECHNOLOGIES INC',
            'flag' => 'us',
            'ticker' => 'CELH',
            'image_jour' => '/images/investisseur/bibliotheque/ramassage-pic/himax-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/ramassage-pic/himax-s.jpg',
            'description' => 'Description spécifique pour HIMAX TECHNOLOGIES INC',
        ],
        'lendingclub' => [
            'title' => 'LENDINGCLUB CORP',
            'flag' => 'us',
            'ticker' => 'LC',
            'image_jour' => '/images/investisseur/bibliotheque/ramassage-pic/lendingclub-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/ramassage-pic/lendingclub-s.jpg',
            'description' => 'Description spécifique pour LENDINGCLUB CORP',
        ],
        'plug-power' => [
            'title' => 'PLUG-POWER INC',
            'flag' => 'us',
            'ticker' => 'PLUG',
            'image_jour' => '/images/investisseur/bibliotheque/ramassage-pic/plug-power-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/ramassage-pic/plug-power-s.jpg',
            'description' => 'Description spécifique pour PLUG-POWER INC',
        ],
        'up-fintech' => [
            'title' => 'UP FINTECH HLD',
            'flag' => 'au',
            'ticker' => 'TIGR',
            'image_jour' => '/images/investisseur/bibliotheque/ramassage-pic/up-fintech-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/ramassage-pic/up-fintech-s.jpg',
            'description' => 'Description spécifique pour UP FINTECH HLD',
        ],
    ];

    private array  $picRamassageData = [
        'altimmune' => [
            'title' => 'ALTIMMUNE INC',
            'flag' => 'us',
            'ticker' => 'ALT',
            'image_jour' => '/images/investisseur/bibliotheque/pic-ramassage/altimmune-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/pic-ramassage/altimmune-s.jpg',
            'description' => 'Description spécifique pour ALTIMMUNE INC',
        ],
        'celsius' => [
            'title' => 'CELSIUS HLD',
            'flag' => 'us',
            'ticker' => 'CELH',
            'image_jour' => '/images/investisseur/bibliotheque/pic-ramassage/celsius-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/pic-ramassage/celsius-s.jpg',
            'description' => 'Description spécifique pour CELSIUS HLD',
        ],
        'digital-turbine' => [
            'title' => 'DIGITAL TURBINE INC',
            'flag' => 'us',
            'ticker' => 'APPS',
            'image_jour' => '/images/investisseur/bibliotheque/pic-ramassage/digital-turbine-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/pic-ramassage/digital-turbine-s.jpg',
            'description' => 'Description spécifique pour DIGITAL TURBINE INC',
        ],
        'novavax' => [
            'title' => 'NOVAVAX INC',
            'flag' => 'us',
            'ticker' => 'NVAX',
            'image_jour' => '/images/investisseur/bibliotheque/pic-ramassage/novavax-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/pic-ramassage/novavax-s.jpg',
            'description' => 'Description spécifique pour NOVAVAX INC',
        ],
        'westport' => [
            'title' => 'WESTPORT FUEL SYSTEMS INC',
            'flag' => 'us',
            'ticker' => 'WPRT',
            'image_jour' => '/images/investisseur/bibliotheque/pic-ramassage/westport-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/pic-ramassage/westport-s.jpg',
            'description' => 'Description spécifique pour WESTPORT FUEL SYSTEMS INC',
        ],
    ];

    private array  $picVolume = [
        'gaotu-techedu ' => [
            'title' => 'GAOTU TECHEDU INC',
            'flag' => 'us',
            'ticker' => 'GOTU',
            'image_jour' => '/images/investisseur/bibliotheque/pic-volumes/gaotu-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/pic-volumes/gaotu-s.jpg',
            'description' => 'Description spécifique pour GAOTU TECHEDU INC',
        ],
        'greeland-technologies' => [
            'title' => 'GREENLAND TECHNOLOGIES INC',
            'flag' => 'us',
            'ticker' => 'GTEC',
            'image_jour' => '/images/investisseur/bibliotheque/pic-volumes/greeland-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/pic-volumes/greeland-s.jpg',
            'description' => 'Description spécifique pour GREENLAND TECHNOLOGIES INC',
        ],
        'lexicon-pharmaceuticals' => [
            'title' => 'LEXICON PHAMACEUTICALS INC',
            'flag' => 'us',
            'ticker' => 'LXRX',
            'image_jour' => '/images/investisseur/bibliotheque/pic-volumes/lexicon-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/pic-volumes/lexicon-s.jpg',
            'description' => 'Description spécifique pour LEXICON PHAMACEUTICALS INC',
        ],
        'raibow-rare' => [
            'title' => 'RAINBOW RARE EARTHS LTD',
            'flag' => 'us',
            'ticker' => 'RBW',
            'image_jour' => '/images/investisseur/bibliotheque/pic-volumes/rainbow-rare-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/pic-volumes/rainbow-rare-s.jpg',
            'description' => 'Description spécifique pour RAINBOW RARE EARTHS LTD',
        ],
        'superior-industries' => [
            'title' => 'SUPERIOR INDUSTRIES INTL',
            'flag' => 'us',
            'ticker' => 'SUP',
            'image_jour' => '/images/investisseur/bibliotheque/pic-volumes/superior-industries-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/pic-volumes/superior-indusries-s.jpg',
            'description' => 'Description spécifique pour SUPERIOR INDUSTRIES INTL',
        ],
    ];

    private array  $volumesFaibles = [
        '22nd-century' => [
            'title' => '22ND CENTURY GROUP INC',
            'flag' => 'us',
            'ticker' => 'XXIII',
            'image_jour' => '/images/investisseur/bibliotheque/volumes-faibles/22nd-century-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/volumes-faibles/22nd-century-s.jpg',
            'description' => 'Description spécifique pour 22ND CENTURY GROUP INC',
        ],
        'alumexx' => [
            'title' => 'ALUMEXX',
            'flag' => 'nl',
            'ticker' => 'ALX',
            'image_jour' => '/images/investisseur/bibliotheque/volumes-faibles/alumexx-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/volumes-faibles/alumexx-s.jpg',
            'description' => 'Description spécifique pour ALUMEXX',
        ],
        'cliq-digital' => [
            'title' => 'CLIQ DIGITAL AGNA',
            'flag' => 'us',
            'ticker' => 'CLIQ',
            'image_jour' => '/images/investisseur/bibliotheque/volumes-faibles/cliq-digital-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/volumes-faibles/cliq-digital-s.jpg',
            'description' => 'Description spécifique pour CLIQ DIGITAL AGNA',
        ],
        'groupe-ldlc' => [
            'title' => 'GROUPE LDLC',
            'flag' => 'fr',
            'ticker' => 'ALLDL',
            'image_jour' => '/images/investisseur/bibliotheque/volumes-faibles/groupe-ldlc-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/volumes-faibles/groupe-ldlc-s.jpg',
            'description' => 'Description spécifique pour GROUPE LDLC',
        ],
        'guillemot' => [
            'title' => 'GUILLEMOT',
            'flag' => 'fr',
            'ticker' => 'GUI',
            'image_jour' => '/images/investisseur/bibliotheque/volumes-faibles/guillemot-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/volumes-faibles/guillemot-s.jpg',
            'description' => 'Description spécifique pour GUILLEMOT',
        ],
    ];

    private array  $intoduction = [
        'bit-digital' => [
            'title' => 'BIT DIGITAL',
            'flag' => 'us',
            'ticker' => 'BTBT',
            'image_jour' => '/images/investisseur/bibliotheque/introduction-recente/bit-digital-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/introduction-recente/bit-digital-s.jpg',
            'description' => 'Description spécifique pour BIT DIGITAL',
        ],
        'bitfarms' => [
            'title' => 'BITFARMS LTD',
            'flag' => 'us',
            'ticker' => 'BITF',
            'image_jour' => '/images/investisseur/bibliotheque/introduction-recente/bitfarms-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/introduction-recente/bitfarms-s.jpg',
            'description' => 'Description spécifique pour BITFARMS LTD',
        ],
        'fastly' => [
            'title' => 'FASTLY INC',
            'flag' => 'us',
            'ticker' => 'FSLY',
            'image_jour' => '/images/investisseur/bibliotheque/introduction-recente/fastly-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/introduction-recente/fastly-s.jpg',
            'description' => 'Description spécifique pour FASTLY INC',
        ],
        'mind-medicine' => [
            'title' => 'MIND MIDICINE INC',
            'flag' => 'us',
            'ticker' => 'MNMD',
            'image_jour' => '/images/investisseur/bibliotheque/introduction-recente/mind-medicine-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/introduction-recente/mind-medicine-s.jpg',
            'description' => 'Description spécifique pour MIND MIDICINE INC',
        ],
        'peloton-interactive' => [
            'title' => 'PELOTON INTERACTIVE INC',
            'flag' => 'us',
            'ticker' => 'PTON',
            'image_jour' => '/images/investisseur/bibliotheque/introduction-recente/peleton-j.jpg',
            'image_semaine' => '/images/investisseur/bibliotheque/introduction-recente/peleton-s.jpg',
            'description' => 'Description spécifique pour PELOTON INTERACTIVE INC',
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
        $valueTitle = 'Bulles type 1';
        return $this->render('investisseur/bibliotheque/bibliotheque-bulles.html.twig', [
            'bullesTypeData' => $this->bullesType1Data,
            'valueTitle' => $valueTitle
        ]);
    }

    #[Route('/bibliotheque/bulles-type-1/{value}', name: 'investisseur_bibliotheque_bulles_type_1_value')]
    public function bullesType1(string $value, Request $request): Response
    {
        // Vérifie si la valeur sélectionnée existe dans les données
        if (!array_key_exists($value, $this->bullesType1Data)) {
            throw $this->createNotFoundException('Cette valeur n\'existe pas.');
        }

        $valueTitle = 'Bulles type 1';
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
            'valueTitle' => $valueTitle
        ]);
    }

    #[Route('/bibliotheque/bulles-type-2', name: 'investisseur_bibliotheque_bulles_type_2')]
    public function investisseur_bibliotheque_bulles_type_2(): Response
    {
        $valueTitle = 'Bulles type 2';
        return $this->render('investisseur/bibliotheque/bibliotheque-bulles-type-2.html.twig', [
            'bullesType2Data' => $this->bullesType2Data,
            'valueTitle' => $valueTitle
        ]);
    }

    #[Route('/bibliotheque/bulles-type-2/{value}', name: 'investisseur_bibliotheque_bulles_type_2_value')]
    public function bullesType2(string $value, Request $request): Response
    {
        // Vérifie si la valeur sélectionnée existe dans les données
        if (!array_key_exists($value, $this->bullesType2Data)) {
            throw $this->createNotFoundException('Cette valeur n\'existe pas.');
        }

        $valueTitle = 'Bulles type 2';
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
            'valueTitle' => $valueTitle
        ]);
    }

    #[Route('/bibliotheque/ramassage', name: 'investisseur_bibliotheque_ramasssage_1')]
    public function investisseur_bibliotheque_ramasssage(): Response
    {
        $valueTitle = 'Ramassage';
        return $this->render('investisseur/bibliotheque/bibliotheque-ramassage.html.twig', [
            'ramassageData' => $this->ramassageData,
            'valueTitle' => $valueTitle
        ]);
    }

    #[Route('/bibliotheque/ramassage/{value}', name: 'investisseur_bibliotheque_ramasssage_1_value')]
    public function ramassage_value(string $value, Request $request): Response
    {
        // Vérifie si la valeur sélectionnée existe dans les données
        if (!array_key_exists($value, $this->ramassageData)) {
            throw $this->createNotFoundException('Cette valeur n\'existe pas.');
        }
        $valueTitle = 'Ramassage';
        $data = $this->ramassageData[$value];
        $currentRoute = $request->get('_route');
        $currentValue = $request->get('value');

        return $this->render('investisseur/bibliotheque/bibliotheque-bulles-value.html.twig', [
            'title' => $data['title'],
            'ticker' => $data['ticker'],
            'flag' => $data['flag'],
            'image_jour' => $data['image_jour'],
            'image_semaine' => $data['image_semaine'],
            'description' => $data['description'],
            'bullesTypeData' => $this->ramassageData,
            'currentRoute' => $currentRoute,
            'currentValue' => $currentValue,
            'valueTitle' => $valueTitle,
        ]);
    }

    #[Route('/bibliotheque/ramassage-pic', name: 'investisseur_bibliotheque_ramasssage_pic')]
    public function investisseur_bibliotheque_ramasssage_pic(): Response
    {
        $valueTitle = 'Ramasage + Pic';
        return $this->render('investisseur/bibliotheque/bibliotheque-ramassage-pic.html.twig', [
            'ramassagePicData' => $this->ramassagePicData,
            'valueTitle' => $valueTitle,
        ]);
    }

    #[Route('/bibliotheque/ramassage-pic/{value}', name: 'investisseur_bibliotheque_ramasssage_pic_value')]
    public function ramassage_pic_value(string $value, Request $request): Response
    {
        // Vérifie si la valeur sélectionnée existe dans les données
        if (!array_key_exists($value, $this->ramassagePicData)) {
            throw $this->createNotFoundException('Cette valeur n\'existe pas.');
        }

        $valueTitle = 'Ramasage + Pic';
        $data = $this->ramassagePicData[$value];
        $currentRoute = $request->get('_route');
        $currentValue = $request->get('value');

        return $this->render('investisseur/bibliotheque/bibliotheque-bulles-value.html.twig', [
            'title' => $data['title'],
            'ticker' => $data['ticker'],
            'flag' => $data['flag'],
            'image_jour' => $data['image_jour'],
            'image_semaine' => $data['image_semaine'],
            'description' => $data['description'],
            'bullesTypeData' => $this->ramassagePicData,
            'currentRoute' => $currentRoute,
            'currentValue' => $currentValue,
            'valueTitle' => $valueTitle,
        ]);
    }

    #[Route('/bibliotheque/pic-ramassage', name: 'investisseur_bibliotheque_pic_ramassage')]
    public function investisseur_bibliotheque_pic_ramassage(): Response
    {
        $valueTitle = 'Pic + Ramassage';
        return $this->render('investisseur/bibliotheque/bibliotheque-pic-ramassage.html.twig', [
            'picRamassageData' => $this->picRamassageData,
            'valueTitle' => $valueTitle,
        ]);
    }

    #[Route('/bibliotheque/pic-ramassage/{value}', name: 'investisseur_bibliotheque_pic_ramassage_value')]
    public function pic_ramassage_value(string $value, Request $request): Response
    {
        // Vérifie si la valeur sélectionnée existe dans les données
        if (!array_key_exists($value, $this->picRamassageData)) {
            throw $this->createNotFoundException('Cette valeur n\'existe pas.');
        }

        $valueTitle = 'Pic + Ramassage';
        $data = $this->picRamassageData[$value];
        $currentRoute = $request->get('_route');
        $currentValue = $request->get('value');

        return $this->render('investisseur/bibliotheque/bibliotheque-bulles-value.html.twig', [
            'title' => $data['title'],
            'ticker' => $data['ticker'],
            'flag' => $data['flag'],
            'image_jour' => $data['image_jour'],
            'image_semaine' => $data['image_semaine'],
            'description' => $data['description'],
            'bullesTypeData' => $this->picRamassageData,
            'currentRoute' => $currentRoute,
            'currentValue' => $currentValue,
            'valueTitle' => $valueTitle,
        ]);
    }

    #[Route('/bibliotheque/pics-de-volumes', name: 'investisseur_bibliotheque_pics_volumes')]
    public function investisseur_bibliotheque_pics_volumes(): Response
    {
        $valueTitle = 'Pics de volumes';
        return $this->render('investisseur/bibliotheque/bibliothequePicVolume.html.twig', [
            'picVolume' => $this->picVolume,
            'valueTitle' => $valueTitle,
        ]);
    }

    #[Route('/bibliotheque/pics-de-volumes/{value}', name: 'investisseur_bibliotheque_pics_volumes_values')]
    public function pic_volumes_value(string $value, Request $request): Response
    {
        // Vérifie si la valeur sélectionnée existe dans les données
        if (!array_key_exists($value, $this->picVolume)) {
            throw $this->createNotFoundException('Cette valeur n\'existe pas.');
        }

        $valueTitle = 'Pics de volumes';
        $data = $this->picVolume[$value];
        $currentRoute = $request->get('_route');
        $currentValue = $request->get('value');

        return $this->render('investisseur/bibliotheque/bibliotheque-bulles-value.html.twig', [
            'title' => $data['title'],
            'ticker' => $data['ticker'],
            'flag' => $data['flag'],
            'image_jour' => $data['image_jour'],
            'image_semaine' => $data['image_semaine'],
            'description' => $data['description'],
            'bullesTypeData' => $this->picVolume,
            'currentRoute' => $currentRoute,
            'currentValue' => $currentValue,
            'valueTitle' => $valueTitle,
        ]);
    }

    #[Route('/bibliotheque/volumes-faibles', name: 'investisseur_bibliotheque_volumes_faibles')]
    public function investisseur_bibliotheque_volumes_faibles(): Response
    {
        $valueTitle = 'Volumes faibles';
        return $this->render('investisseur/bibliotheque/bibliotheque-volumes-faibles.html.twig', [
            'volumesFaibles' => $this->volumesFaibles,
            'valueTitle' => $valueTitle,
        ]);
    }

    #[Route('/bibliotheque/volumes-faibles/{value}', name: 'investisseur_bibliotheque_volumes_faibles_values')]
    public function volumes_faible_value(string $value, Request $request): Response
    {
        // Vérifie si la valeur sélectionnée existe dans les données
        if (!array_key_exists($value, $this->volumesFaibles)) {
            throw $this->createNotFoundException('Cette valeur n\'existe pas.');
        }

        $valueTitle = 'Volumes faibles';
        $data = $this->volumesFaibles[$value];
        $currentRoute = $request->get('_route');
        $currentValue = $request->get('value');

        return $this->render('investisseur/bibliotheque/bibliotheque-bulles-value.html.twig', [
            'title' => $data['title'],
            'ticker' => $data['ticker'],
            'flag' => $data['flag'],
            'image_jour' => $data['image_jour'],
            'image_semaine' => $data['image_semaine'],
            'description' => $data['description'],
            'bullesTypeData' => $this->volumesFaibles,
            'currentRoute' => $currentRoute,
            'currentValue' => $currentValue,
            'valueTitle' => $valueTitle,
        ]);
    }

    #[Route('/bibliotheque/introductions-recentes', name: 'investisseur_bibliotheque_introduction')]
    public function investisseur_bibliotheque_introduction(): Response
    {
        $valueTitle = 'Introductions';
        return $this->render('investisseur/bibliotheque/bibliotheque-introduction.html.twig', [
            'intoduction' => $this->intoduction,
            'valueTitle' => $valueTitle,
        ]);
        return $this->render('investisseur/bibliotheque/bibliotheque-introduction.html.twig', []);
    }

    #[Route('/bibliotheque/introductions-recentes/{value}', name: 'investisseur_bibliotheque_introduction_values')]
    public function introduction_value(string $value, Request $request): Response
    {
        // Vérifie si la valeur sélectionnée existe dans les données
        if (!array_key_exists($value, $this->intoduction)) {
            throw $this->createNotFoundException('Cette valeur n\'existe pas.');
        }

        $valueTitle = 'Introductions';
        $data = $this->intoduction[$value];
        $currentRoute = $request->get('_route');
        $currentValue = $request->get('value');

        return $this->render('investisseur/bibliotheque/bibliotheque-bulles-value.html.twig', [
            'title' => $data['title'],
            'ticker' => $data['ticker'],
            'flag' => $data['flag'],
            'image_jour' => $data['image_jour'],
            'image_semaine' => $data['image_semaine'],
            'description' => $data['description'],
            'bullesTypeData' => $this->intoduction,
            'currentRoute' => $currentRoute,
            'currentValue' => $currentValue,
            'valueTitle' => $valueTitle,
        ]);
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
