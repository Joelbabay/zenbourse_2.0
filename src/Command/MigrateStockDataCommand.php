<?php

namespace App\Command;

use App\Entity\StockExample;
use App\Entity\CandlestickPattern;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate-stock-data',
    description: 'Migre les données hardcodées vers les nouvelles entités',
)]
class MigrateStockDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Migration des données vers les nouvelles entités');

        // Migration des exemples d'actions
        $this->migrateStockExamples($io);

        // Migration des patterns de chandeliers
        $this->migrateCandlestickPatterns($io);

        $this->entityManager->flush();
        $io->success('Migration terminée avec succès !');

        return Command::SUCCESS;
    }

    private function migrateStockExamples(SymfonyStyle $io): void
    {
        $io->section('Migration des exemples d\'actions');

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
                    'title' => 'WITBE',
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
        ];

        foreach ($stockData as $category => $examples) {
            foreach ($examples as $slug => $data) {
                $stockExample = new StockExample();
                $stockExample->setTitle($data['title']);
                $stockExample->setTicker($data['ticker']);
                $stockExample->setFlag($data['flag']);
                $stockExample->setSlug($slug);
                $stockExample->setCategory($category);
                $stockExample->setImageJour($data['image_jour']);
                $stockExample->setImageSemaine($data['image_semaine']);
                $stockExample->setDescription($data['description']);

                $this->entityManager->persist($stockExample);
                $io->text("✓ {$data['title']} ({$category})");
            }
        }
    }

    private function migrateCandlestickPatterns(SymfonyStyle $io): void
    {
        $io->section('Migration des patterns de chandeliers japonais');

        $patterns = [
            'gap-de-continuation' => [
                'name' => 'Gap de continuation',
                'title' => 'Chandeliers japonais – le gap de continuation',
                'structure' => 'Gap de continuation haussier / baissier',
                'description' => 'Le GAP de continuation s\'inscrit dans une tendance validée. Il indique la poursuite de la tendance haussière / baissière.',
                'image_h' => 'images/investisseur/methode/chandelier-japonais/gaph.jpg',
                'image_b' => 'images/investisseur/methode/chandelier-japonais/gapb.jpg',
                'image_name_h' => 'Le gap haussier',
                'image_name_b' => 'Le gap baissier',
                'content_h' => '<p>La structure est formée de 2 chandeliers haussiers verts.</p><ul><li>Le premier est un grand chandelier haussier vert</li><li>Le second chandelier dont le cours d\'ouverture doit être supérieur au cours de clôture du chandelier précédent</li><li>Cette structure intervient généralement dans une tendance haussière après une hausse significative</li></ul>',
                'content_b' => '<p>La structure est formée de 2 chandeliers baissiers rouges.</p><ul><li>Le premier est un grand chandelier baissier rouge</li><li>Le second chandelier dont le cours d\'ouverture doit être inférieur au cours de clôture du chandelier précédent</li><li>Cette structure intervient généralement dans une tendance haussière après une hausse significative</li></ul>',
            ],
            'trois-soldats-blancs' => [
                'name' => 'Trois soldats blancs',
                'title' => 'Chandeliers japonais – Trois soldats blancs - Trois corbeaux noirs',
                'structure' => 'Trois soldats blancs / trois corbeaux noirs',
                'description' => 'Les trois soldats blancs comme les 3 corbeaux noirs sont des structures de continuation de tendance qui s\'inscrivent dans une tendance validée. <br><br> Ces structures indiquent la poursuite de la tendance haussière / baissière.',
                'image_h' => 'images/investisseur/methode/chandelier-japonais/3sbh.jpg',
                'image_b' => 'images/investisseur/methode/chandelier-japonais/3cnb.jpg',
                'image_name_h' => 'Trois soldats blancs',
                'image_name_b' => 'Trois corbeaux noirs',
                'content_h' => '<p>Structure en 3 chandeliers.</p><ul><li>La clôture de chaque chandelier vert doit s\'effectuer au-dessus du chandelier précédent.</li><li>L\'ouverture de chaque chandelier s\'effectuera de préférence à l\'intérieur de la partie supérieure du chandelier précédent</li></ul>',
                'content_b' => '<p>Structure en 3 chandeliers.</p><ul><li>La clôture de chaque chandelier rouge doit s\'effectuer au-dessous du chandelier précédent.</li><li>L\'ouverture de chaque chandelier s\'effectuera de préférence à l\'intérieur de la partie inférieure du chandelier précédent</li></ul>',
            ],
        ];

        foreach ($patterns as $slug => $data) {
            $pattern = new CandlestickPattern();
            $pattern->setName($data['name']);
            $pattern->setSlug($slug);
            $pattern->setTitle($data['title']);
            $pattern->setStructure($data['structure']);
            $pattern->setDescription($data['description']);
            $pattern->setImageH($data['image_h']);
            $pattern->setImageB($data['image_b']);
            $pattern->setImageNameH($data['image_name_h']);
            $pattern->setImageNameB($data['image_name_b']);
            $pattern->setContentH($data['content_h']);
            $pattern->setContentB($data['content_b']);

            $this->entityManager->persist($pattern);
            $io->text("✓ {$data['name']}");
        }
    }
}
