<?php

namespace App\Command;

use App\Entity\Menu;
use App\Entity\PageContent;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-test-content',
    description: 'Génère du contenu de test pour les pages HOME sans contenu',
)]
class GenerateTestContentCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private MenuRepository $menuRepository;

    public function __construct(EntityManagerInterface $entityManager, MenuRepository $menuRepository)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->menuRepository = $menuRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Génération de contenu de test pour les pages HOME');

        // Récupérer tous les menus HOME
        $homeMenus = $this->menuRepository->findBy(['section' => 'HOME'], ['menuorder' => 'ASC']);

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($homeMenus as $menu) {
            // Vérifier si le menu a déjà du contenu
            $existingContent = $this->entityManager->getRepository(PageContent::class)->findOneBy(['menu' => $menu]);

            if ($existingContent) {
                $io->text(sprintf('⏭️  Page "%s" a déjà du contenu - ignorée', $menu->getLabel()));
                $skippedCount++;
                continue;
            }

            // Créer du contenu de test
            $pageContent = new PageContent();
            $pageContent->setMenu($menu);
            $pageContent->setTitle($this->generateTitle($menu->getLabel()));
            $pageContent->setContent($this->generateContent($menu->getLabel()));

            $this->entityManager->persist($pageContent);
            $createdCount++;

            $io->text(sprintf('✅ Contenu créé pour "%s"', $menu->getLabel()));
        }

        $this->entityManager->flush();

        $io->success([
            sprintf('%d pages avec contenu créé', $createdCount),
            sprintf('%d pages ignorées (déjà du contenu)', $skippedCount),
            'Contenu de test généré avec succès !'
        ]);

        return Command::SUCCESS;
    }

    private function generateTitle(string $menuLabel): string
    {
        return match ($menuLabel) {
            'test menu 4' => 'Page de Test 4',
            'Méthodes' => 'Nos Méthodes d\'Investissement',
            'Le Perdant' => 'Le Perdant - Stratégie de Trading',
            'Contact' => 'Contactez-nous',
            'Citation' => 'Citations Inspirantes',
            'Bien Débuter' => 'Comment Bien Débuter en Trading',
            'Performance' => 'Nos Performances',
            default => $menuLabel
        };
    }

    private function generateContent(string $menuLabel): string
    {
        return match ($menuLabel) {
            'test menu 4' => $this->getTestMenu4Content(),
            'Méthodes' => $this->getMethodesContent(),
            'Le Perdant' => $this->getPerdantContent(),
            'Contact' => $this->getContactContent(),
            'Citation' => $this->getCitationContent(),
            'Bien Débuter' => $this->getBienDebuterContent(),
            'Performance' => $this->getPerformanceContent(),
            default => $this->getDefaultContent($menuLabel)
        };
    }

    private function getTestMenu4Content(): string
    {
        return '<h2>Page de Test 4</h2>
<p>Cette page est utilisée pour tester le système de menus et de contenu dynamique.</p>
<p>Vous pouvez modifier ce contenu via l\'administration pour personnaliser votre site.</p>
<div class="alert alert-info">
    <strong>Note :</strong> Ce contenu est généré automatiquement et peut être modifié.
</div>';
    }

    private function getMethodesContent(): string
    {
        return '<h2>Nos Méthodes d\'Investissement</h2>
<p>Découvrez nos méthodes éprouvées pour réussir en bourse :</p>

<h3>1. Analyse Technique</h3>
<p>Nous utilisons des outils d\'analyse technique avancés pour identifier les tendances et les points d\'entrée optimaux.</p>

<h3>2. Gestion des Risques</h3>
<p>Notre approche met l\'accent sur la protection du capital avec des stop-loss et des règles de position sizing strictes.</p>

<h3>3. Psychologie du Trading</h3>
<p>La discipline émotionnelle est cruciale. Nous vous aidons à développer la mentalité d\'un trader professionnel.</p>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Analyse Chartiste</h5>
                <p class="card-text">Étude des patterns et figures techniques pour anticiper les mouvements.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Indicateurs Techniques</h5>
                <p class="card-text">Utilisation d\'indicateurs fiables pour confirmer les signaux.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Gestion de Portefeuille</h5>
                <p class="card-text">Diversification et allocation optimale des actifs.</p>
            </div>
        </div>
    </div>
</div>';
    }

    private function getPerdantContent(): string
    {
        return '<h2>Le Perdant - Stratégie de Trading</h2>
<p>La stratégie "Le Perdant" est une approche unique développée par notre équipe d\'experts.</p>

<h3>Principe Fondamental</h3>
<p>Cette méthode se base sur l\'observation que les marchés ont tendance à se retourner après des mouvements extrêmes. Nous identifions les actions qui ont subi des baisses importantes et nous positionnons pour une reprise.</p>

<h3>Critères de Sélection</h3>
<ul>
    <li>Actions ayant chuté de plus de 30% sur une période donnée</li>
    <li>Volume d\'échange élevé indiquant un intérêt renouvelé</li>
    <li>Signaux techniques de retournement</li>
    <li>Analyse fondamentale montrant une valeur sous-évaluée</li>
</ul>

<h3>Gestion des Risques</h3>
<p>Même avec cette stratégie, nous maintenons des règles strictes de gestion des risques :</p>
<ul>
    <li>Stop-loss à 5-7% maximum</li>
    <li>Position sizing limité à 2-3% du portefeuille par trade</li>
    <li>Diversification sur plusieurs positions</li>
</ul>

<div class="alert alert-warning">
    <strong>Attention :</strong> Le trading comporte des risques. Cette stratégie ne garantit pas des profits.
</div>';
    }

    private function getContactContent(): string
    {
        return '<h2>Contactez-nous</h2>
<p>Nous sommes là pour vous accompagner dans votre parcours d\'investissement.</p>

<div class="row">
    <div class="col-md-6">
        <h3>Informations de Contact</h3>
        <ul class="list-unstyled">
            <li><strong>Email :</strong> contact@zenbourse.com</li>
            <li><strong>Téléphone :</strong> +33 1 23 45 67 89</li>
            <li><strong>Adresse :</strong> 123 Rue de la Bourse, 75001 Paris</li>
        </ul>
        
        <h4>Horaires d\'ouverture</h4>
        <p>Lundi - Vendredi : 9h00 - 18h00<br>
        Samedi : 9h00 - 12h00<br>
        Dimanche : Fermé</p>
    </div>
    
    <div class="col-md-6">
        <h3>Formulaire de Contact</h3>
        <form>
            <div class="mb-3">
                <label for="nom" class="form-label">Nom complet</label>
                <input type="text" class="form-control" id="nom" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" required>
            </div>
            <div class="mb-3">
                <label for="sujet" class="form-label">Sujet</label>
                <select class="form-select" id="sujet">
                    <option>Question générale</option>
                    <option>Support technique</option>
                    <option>Demande de formation</option>
                    <option>Autre</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Envoyer</button>
        </form>
    </div>
</div>';
    }

    private function getCitationContent(): string
    {
        return '<h2>Citations Inspirantes</h2>
<p>Des paroles sages de grands investisseurs pour vous motiver dans votre parcours.</p>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <blockquote class="blockquote">
                    <p>"Le marché peut rester irrationnel plus longtemps que vous ne pouvez rester solvable."</p>
                    <footer class="blockquote-footer">John Maynard Keynes</footer>
                </blockquote>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <blockquote class="blockquote">
                    <p>"Le temps est votre ami, l\'impulsivité votre ennemi."</p>
                    <footer class="blockquote-footer">Warren Buffett</footer>
                </blockquote>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <blockquote class="blockquote">
                    <p>"La diversification est une protection contre l\'ignorance."</p>
                    <footer class="blockquote-footer">Warren Buffett</footer>
                </blockquote>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <blockquote class="blockquote">
                    <p>"Le succès en investissement est lié à la discipline et à la patience."</p>
                    <footer class="blockquote-footer">Peter Lynch</footer>
                </blockquote>
            </div>
        </div>
    </div>
</div>

<h3>Citations sur la Psychologie</h3>
<div class="alert alert-info">
    <p><strong>"La peur et la cupidité sont les deux émotions qui dominent les marchés."</strong> - Benjamin Graham</p>
</div>

<p>Ces citations nous rappellent l\'importance de la discipline, de la patience et de la gestion émotionnelle dans le trading et l\'investissement.</p>';
    }

    private function getBienDebuterContent(): string
    {
        return '<h2>Comment Bien Débuter en Trading</h2>
<p>Débuter en trading peut sembler intimidant, mais avec la bonne approche, vous pouvez réussir.</p>

<h3>Étape 1 : Éducation</h3>
<p>Avant de commencer à trader, il est essentiel de comprendre les bases :</p>
<ul>
    <li>Apprendre les concepts fondamentaux de l\'analyse technique</li>
    <li>Comprendre les différents types d\'ordres</li>
    <li>Étudier la gestion des risques</li>
    <li>Maîtriser la psychologie du trading</li>
</ul>

<h3>Étape 2 : Plan de Trading</h3>
<p>Développez un plan de trading solide :</p>
<ul>
    <li>Définissez vos objectifs financiers</li>
    <li>Établissez votre tolérance au risque</li>
    <li>Choisissez vos instruments de trading</li>
    <li>Créez vos règles d\'entrée et de sortie</li>
</ul>

<h3>Étape 3 : Compte de Démonstration</h3>
<p>Pratiquez d\'abord sur un compte de démonstration :</p>
<ul>
    <li>Testez vos stratégies sans risque</li>
    <li>Apprenez à utiliser votre plateforme</li>
    <li>Développez votre discipline</li>
    <li>Analysez vos performances</li>
</ul>

<h3>Étape 4 : Trading en Temps Réel</h3>
<p>Quand vous êtes prêt, commencez petit :</p>
<ul>
    <li>Commencez avec de petites positions</li>
    <li>Respectez strictement votre plan</li>
    <li>Tenir un journal de trading</li>
    <li>Analyser et améliorer continuellement</li>
</ul>

<div class="alert alert-success">
    <h4>Conseils Importants</h4>
    <ul>
        <li>Ne jamais risquer plus de 1-2% de votre capital par trade</li>
        <li>Utilisez toujours des stop-loss</li>
        <li>Ne laissez jamais les émotions dicter vos décisions</li>
        <li>Continuez à apprendre et à vous améliorer</li>
    </ul>
</div>';
    }

    private function getPerformanceContent(): string
    {
        return '<h2>Nos Performances</h2>
<p>Découvrez les résultats de nos stratégies et méthodes d\'investissement.</p>

<h3>Performance Annuelle</h3>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Année</th>
                <th>Performance</th>
                <th>Benchmark (S&P 500)</th>
                <th>Surperformance</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>2024</td>
                <td class="text-success">+15.2%</td>
                <td>+8.5%</td>
                <td class="text-success">+6.7%</td>
            </tr>
            <tr>
                <td>2023</td>
                <td class="text-success">+12.8%</td>
                <td>+24.2%</td>
                <td class="text-danger">-11.4%</td>
            </tr>
            <tr>
                <td>2022</td>
                <td class="text-success">+5.3%</td>
                <td>-18.1%</td>
                <td class="text-success">+23.4%</td>
            </tr>
            <tr>
                <td>2021</td>
                <td class="text-success">+18.7%</td>
                <td>+28.7%</td>
                <td class="text-danger">-10.0%</td>
            </tr>
        </tbody>
    </table>
</div>

<h3>Statistiques Clés</h3>
<div class="row">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Taux de Succès</h5>
                <h2 class="text-success">68%</h2>
                <p class="card-text">Des trades gagnants</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Ratio Gain/Perte</h5>
                <h2 class="text-success">1.8</h2>
                <p class="card-text">Pour chaque euro perdu</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Drawdown Max</h5>
                <h2 class="text-warning">-8.5%</h2>
                <p class="card-text">Perte maximale</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Trades/Mois</h5>
                <h2 class="text-info">12</h2>
                <p class="card-text">En moyenne</p>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info mt-4">
    <strong>Note :</strong> Les performances passées ne garantissent pas les résultats futurs. Le trading comporte des risques de perte.
</div>';
    }

    private function getDefaultContent(string $menuLabel): string
    {
        return sprintf('<h2>%s</h2>
<p>Contenu de test pour la page "%s".</p>
<p>Ce contenu peut être modifié via l\'administration du site.</p>', $menuLabel, $menuLabel);
    }
}
