<?php

namespace App\Command;

use App\Entity\CandlestickPattern;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:import-candlestick-patterns',
    description: 'Importe les structures de chandeliers japonais dans la base de données.'
)]
class ImportCandlestickPatternsCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $data = [
            "gap-de-continuation" => [
                "structure" => "Gap de continuation haussier / baissier",
                "title" => "Chandeliers japonais – le gap de continuation",
                "image_h" => "images/investisseur/methode/chandelier-japonais/gaph.jpg",
                "image_b" => "images/investisseur/methode/chandelier-japonais/gapb.jpg",
                "image_name_h" => "Le gap haussier",
                "image_name_b" => "Le gap baissier",
                "description" => "Le GAP de continuation s'inscrit dans une tendance validée. Il indique la poursuite de la tendance haussière / baissière.",
                "content_h" => "<p>La structure est formée de 2 chandeliers haussiers verts.</p><ul><li>Le premier est un grand chandelier haussier vert</li><li>Le second chandelier dont le cours d'ouverture doit être supérieur au cours de clôture du chandelier précédent</li><li>Cette structure intervient généralement dans une tendance haussière après une hausse significative</li></ul>",
                "content_b" => "<p>La structure est formée de 2 chandeliers baissiers rouges.</p><ul><li>Le premier est un grand chandelier baissier rouge</li><li>Le second chandelier dont le cours d'ouverture doit être inférieur au cours de clôture du chandelier précédent</li><li>Cette structure intervient généralement dans une tendance haussière après une hausse significative</li></ul>",
            ],
            "trois-soldats-blancs" => [
                "structure" => "Trois soldats blancs / trois corbeaux noirs",
                "title" => "Chandeliers japonais – Trois soldats blancs - Trois corbeaux noirs",
                "image_h" => "images/investisseur/methode/chandelier-japonais/3sbh.jpg",
                "image_b" => "images/investisseur/methode/chandelier-japonais/3cnb.jpg",
                "image_name_h" => "Trois soldats blancs",
                "image_name_b" => "Trois corbeaux noirs",
                "description" => "Les trois soldats blancs comme les 3 corbeaux noirs sont des structures de continuation de tendance qui s'inscrivent dans une tendance validée. <br><br> Ces structures indiquent la poursuite de la tendance haussière / baissière.",
                "content_h" => "<p>Structure en 3 chandeliers.</p><ul><li>La clôture de chaque chandelier vert doit s'effectuer au-dessus du chandelier précédent.</li><li>L'ouverture de chaque chandelier s'effectuera de préférence à l'intérieur de la partie supérieure du chandelier précédent</li></ul>",
                "content_b" => "<p>Structure en 3 chandeliers.</p><ul><li>La clôture de chaque chandelier rouge doit s'effectuer au-dessous du chandelier précédent.</li><li>L'ouverture de chaque chandelier s'effectuera de préférence à l'intérieur de la partie inférieure du chandelier précédent</li></ul>",
            ],
            "trois-méthodes" => [
                "structure" => "Trois méthodes haussières / baissières",
                "title" => "Chandeliers japonais - Les trois méthodes",
                "image_h" => "images/investisseur/methode/chandelier-japonais/3mh.jpg",
                "image_b" => "images/investisseur/methode/chandelier-japonais/3mb.jpg",
                "image_name_h" => "Les trois méthodes ascendantes",
                "image_name_b" => "Les trois méthodes descendantes",
                "description" => "Les 3 méthodes est une figure de continuation de tendance qui s'inscrit dans une tendance validée. <br><br> Cette structure indique la poursuite de la tendance haussière / baissière.",
                "content_h" => "<p>La structure est formée de 5 chandeliers.</p><ul><li>Le premier est un grand chandelier haussier vert</li><li>Les 3 chandeliers suivants, baissiers rouges, doivent être contenus dans le range du premier chandelier. Chaque petit chandelier rouge doit clôturer plus bas que le précédent</li><li>Le dernier chandelier doit être un grand chandelier haussier vert, dont l'ouverture doit s'effectuer au-dessus de la clôture de la veille et clôturer au-dessus du plus haut du premier chandelier</li></ul>",
                "content_b" => "<p>La structure est formée de 5 chandeliers.</p><ul><li>Le premier est un grand chandelier baissier rouge</li><li>Les 3 chandeliers suivants haussiers verts, doivent être contenus dans le range du premier chandelier. Chaque petit chandelier vert doit clôturer plus haut que le précédent</li><li>Le dernier chandelier doit être un grand chandelier baissier rouge, dont l'ouverture doit s'effectuer au-dessous de la clôture de la veille et clôturer au-dessous du plus bas du premier chandelier</li></ul>",
            ],
            "porte-drapeau" => [
                "structure" => "Porte-drapeau haussier / inversé",
                "title" => "Chandeliers japonais – Le porte drapeau",
                "image_h" => "images/investisseur/methode/chandelier-japonais/pdh.jpg",
                "image_b" => "images/investisseur/methode/chandelier-japonais/pdb.jpg",
                "image_name_h" => "Porte drapeau haussier",
                "image_name_b" => "Porte drapeau baissier",
                "description" => "Le porte-drapeau est une figure de continuation de tendance qui s'inscrit dans une tendance validée. <br><br> Cette structure indique la poursuite de la tendance haussière / baissière.",
                "content_h" => "<p>La structure est formée de 5 chandeliers, variante de la structure des trois méthodes. Le porte-drapeau est plus puissant.</p><ul><li>Le premier est un grand chandelier haussier vert suivi de trois petits chandeliers rouges</li><li>Chaque petit chandelier rouge doit clôturer plus bas que le précédent. L'ouverture du second chandelier doit s'effectuer sur un Gap haussier</li><li>Le dernier chandelier doit être un grand chandelier haussier vert. Il doit ouvrir sur un Gap haussier et clôturer au-dessus du plus haut du deuxième chandelier</li></ul>",
                "content_b" => "<p>La structure est formée de 5 chandeliers, variante de la structure des trois méthodes. Le porte-drapeau est plus puissant.</p><ul><li>Le premier est un grand chandelier baissier rouge suivi de trois petits chandeliers verts</li><li>Chaque petit chandelier vert doit clôturer plus haut que le précédent. L'ouverture du second chandelier doit s'effectuer sur un Gap baissier</li><li>Le dernier chandelier doit être un grand chandelier haussier rouge. Il doit ouvrir sur un Gap baissier et clôturer au-dessous du plus bas du deuxième chandelier</li></ul>",
            ],
            "gapping-play-zone" => [
                "structure" => "Gapping play en zone haute / en zone basse",
                "title" => "Chandeliers japonais – Gapping play en zone haute / basse",
                "image_h" => "images/investisseur/methode/chandelier-japonais/gpzh.jpg",
                "image_b" => "images/investisseur/methode/chandelier-japonais/gpzb.jpg",
                "image_name_h" => "Gapping play en zone haute",
                "image_name_b" => "Gapping play en zone basse",
                "description" => "Le Gapping play en zone haute / basse est une figure de continuation de tendance qui s'inscrit dans une tendance validée. <br><br> Cette structure particulièrement puissante indique la poursuite de la tendance haussière / baissière.",
                "content_h" => "<p>La structure est formée de plusieurs chandeliers.</p><ul><li>Le premier est un grand chandelier haussier vert</li><li>La succession de petits chandeliers horizontaux verts ou rouges est contenu à l'intérieur du range du premier chandelier</li><li>Le dernier chandelier doit être un grand chandelier vert et ouvrir en gap haussier au-dessus de la clôture des chandeliers précédents</li></ul>",
                "content_b" => "<p>La structure est formée de plusieurs chandeliers.</p><ul><li>Le premier est un grand chandelier baissier rouge</li><li>La succession de petits chandeliers horizontaux rouges ou verts est contenu à l'intérieur du range du premier chandelier</li><li>Le dernier chandelier doit être un grand chandelier rouge et ouvrir en gap baissier au-dessous de la clôture des chandeliers précédents</li></ul>",
            ],
            "trois-lignes-brisées" => [
                "structure" => "Trois lignes brisées haussières / baissières",
                "title" => "Chandeliers japonais - Trois lignes brisées",
                "image_h" => "images/investisseur/methode/chandelier-japonais/3lbh.jpg",
                "image_b" => "images/investisseur/methode/chandelier-japonais/3lbb.jpg",
                "image_name_h" => "Trois lignes brisées haussières",
                "image_name_b" => "Trois lignes brisées baissières",
                "description" => "Les trois lignes brisées est une figure de continuation de tendance qui s'inscrit dans une tendance validée. <br><br> Cette structure indique la poursuite de la tendance haussière / baissière.",
                "content_h" => "<p>La structure est formée de 5 chandeliers.</p><ul><li>Succession de trois petits chandeliers verts dont la clôture s'effectue au-dessus du chandelier précédent</li><li>Le quatrième chandelier est un grand chandelier rouge qui englobe les 3 chandeliers verts. La clôture doit être inférieure au cours d'ouverture du premier chandelier vert</li></ul>",
                "content_b" => "<p>La structure est formée de 5 chandeliers.</p><ul><li>Succession de trois petits chandeliers rouges dont la clôture s'effectue au-dessus du chandelier précédent</li><li>Le quatrième chandelier est un grand chandelier vert qui englobe les 3 chandeliers rouges. La clôture doit être supérieure au cours d'ouverture du premier chandelier rouge</li></ul>",
            ],
            "gapping-play" => [
                "structure" => "Gapping play haussier / baissier",
                "title" => "Chandeliers japonais – le Gapping play",
                "image_h" => "images/investisseur/methode/chandelier-japonais/gph.png",
                "image_b" => "images/investisseur/methode/chandelier-japonais/gpb.png",
                "image_name_h" => "Trois lignes brisées haussières",
                "image_name_b" => "Trois lignes brisées baissières",
                "description" => "Les trois lignes brisées est une figure de continuation de tendance qui s'inscrit dans une tendance validée. <br><br> Cette structure indique la poursuite de la tendance haussière / baissière.",
                "content_h" => "<p>La structure est formée de 3 chandeliers.</p><ul><li>Les 2 premiers chandeliers verts forment une zone d'hésitation</li><li>Le dernier chandelier doit être un grand chandelier vert et doit ouvrir en gap haussier</li></ul><strong>Lorsque le premier chandelier est rouge, la structure est plus puissante</strong>",
                "content_b" => "<p>La structure est formée de 3 chandeliers.</p><ul><li>Les 2 premiers chandeliers rouges forment une zone d'hésitation</li><li>Le dernier chandelier doit être un grand chandelier rouge et doit ouvrir en gap baissier</li></ul><strong>Lorsque le premier chandelier est vert, la structure est plus puissante</strong>",
            ],
            "tasuki-gap" => [
                "structure" => "Tasuki gap haussier / baissier",
                "title" => "Chandeliers japonais – Le Tasuki gap",
                "image_h" => "images/investisseur/methode/chandelier-japonais/tgh.jpg",
                "image_b" => "images/investisseur/methode/chandelier-japonais/tgb.jpg",
                "image_name_h" => "Tasuki gap haussier",
                "image_name_b" => "Tasuki gap baissier",
                "description" => "Les Tasuki gap est une figure de continuation de tendance qui s'inscrit dans une tendance validée. <br><br> Cette structure indique la poursuite de la tendance haussière / baissière.",
                "content_h" => "<p>La structure est formée de 3 chandeliers.</p><ul><li>Le premier est un grand chandelier haussier vert</li><li>Le second chandelier également haussier doit ouvrir en gap</li><li>Le troisième chandelier baissier comble partiellement le gap en clôturant à l'intérieur du gap</li></ul>",
                "content_b" => "<p>La structure est formée de 3 chandeliers.</p><ul><li>Le premier est un grand chandelier baissier rouge</li><li>Le troisième chandelier haussier comble partiellement le gap en clôturant à l'intérieur du gap</li></ul>",
            ],
        ];

        $order = 1;
        foreach ($data as $slug => $d) {
            $pattern = $this->em->getRepository(CandlestickPattern::class)->findOneBy(['slug' => $slug]);
            if (!$pattern) {
                $pattern = new CandlestickPattern();
                $pattern->setSlug($slug);
            }
            $pattern->setStructure($d['structure']);
            $pattern->setTitle($d['title']);
            $pattern->setImageH($d['image_h']);
            $pattern->setImageB($d['image_b']);
            $pattern->setImageNameH($d['image_name_h']);
            $pattern->setImageNameB($d['image_name_b']);
            $pattern->setDescription($d['description']);
            $pattern->setContentH($d['content_h']);
            $pattern->setContentB($d['content_b']);
            $pattern->setIsActive(true);
            $pattern->setMenuOrder($order++);
            $this->em->persist($pattern);
        }
        $this->em->flush();
        $output->writeln('<info>Import terminé !</info>');
        return Command::SUCCESS;
    }
} 