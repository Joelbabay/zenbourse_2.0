<?php

namespace App\Command;

use App\Entity\StockExample;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-stock-data',
    description: 'Met à jour les données des exemples de stocks avec les drapeaux, images et descriptions complètes',
)]
class UpdateStockDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Mise à jour des données des exemples de stocks');

        // Données complètes des stocks
        $stockData = [
            'bulles-type-1' => [
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
            ],
            'bulles-type-2' => [
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
            ],
            'ramassage' => [
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
            ],
            'ramassage-pic' => [
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
            ],
            'pic-ramassage' => [
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
            ],
            'pic-volumes' => [
                'gaotu-techedu' => [
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
            ],
            'volumes-faibles' => [
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
            ],
            'introductions-recentes' => [
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
            ],
        ];

        $updatedCount = 0;
        $createdCount = 0;

        foreach ($stockData as $category => $stocks) {
            $io->section("Catégorie : $category");
            
            foreach ($stocks as $slug => $data) {
                $stockExample = $this->entityManager->getRepository(StockExample::class)->findOneBy(['slug' => $slug]);
                
                if ($stockExample) {
                    // Mise à jour de l'existant
                    $stockExample->setTitle($data['title']);
                    $stockExample->setFlag($data['flag']);
                    $stockExample->setTicker($data['ticker']);
                    $stockExample->setImageJour($data['image_jour']);
                    $stockExample->setImageSemaine($data['image_semaine']);
                    $stockExample->setDescription($data['description']);
                    $stockExample->setCategory($category);
                    
                    $updatedCount++;
                    $io->text(sprintf('✓ Mis à jour : %s (%s)', $data['title'], $data['ticker']));
                } else {
                    // Création d'un nouveau
                    $stockExample = new StockExample();
                    $stockExample->setTitle($data['title']);
                    $stockExample->setFlag($data['flag']);
                    $stockExample->setTicker($data['ticker']);
                    $stockExample->setSlug($slug);
                    $stockExample->setCategory($category);
                    $stockExample->setImageJour($data['image_jour']);
                    $stockExample->setImageSemaine($data['image_semaine']);
                    $stockExample->setDescription($data['description']);
                    $stockExample->setIsActive(true);
                    
                    $this->entityManager->persist($stockExample);
                    $createdCount++;
                    $io->text(sprintf('✓ Créé : %s (%s)', $data['title'], $data['ticker']));
                }
            }
        }

        $this->entityManager->flush();

        $io->success([
            sprintf('%d exemples de stocks mis à jour', $updatedCount),
            sprintf('%d nouveaux exemples de stocks créés', $createdCount),
            'Toutes les données ont été mises à jour avec succès !'
        ]);

        return Command::SUCCESS;
    }
} 