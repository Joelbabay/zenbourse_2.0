<?php
// src/Controller/SubscriptionController.php

namespace App\Controller;

use App\Entity\IntradayRequest;
use App\Entity\InvestisseurRequest;
use App\Entity\SpecialPage;
use App\Entity\User;
use App\Form\IntradayRequestType;
use App\Form\InvestisseurSubscriptionType;
use App\Repository\SpecialPageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SubscriptionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SpecialPageRepository $specialPageRepository
    ) {}

    #[Route('/subscribe/investisseur', name: 'home_investisseur_subscription')]
    public function subscribeInvestisseur(Request $request): Response
    {
        // Récupérer le contenu de la page spéciale
        $specialPage = $this->specialPageRepository->findOneBy([
            'code' => 'INVESTISSEUR_SUBSCRIPTION',
            'isActive' => true
        ]);

        // Fallback si pas de page configurée
        if (!$specialPage) {
            $specialPage = $this->createDefaultSpecialPage(
                'INVESTISSEUR_SUBSCRIPTION',
                'Demande d\'adhésion à la méthode Investisseur'
            );
        }

        return $this->handleSubscriptionRequest(
            $request,
            'investisseur',
            new InvestisseurRequest(),
            InvestisseurSubscriptionType::class,
            $specialPage,
            'subscription/investisseur-subscription.html.twig'
        );
    }

    #[Route('/subscribe/intraday', name: 'home_intraday_subscription')]
    public function subscribeIntraday(Request $request): Response
    {
        // Récupérer le contenu de la page spéciale
        $specialPage = $this->specialPageRepository->findOneBy([
            'code' => 'INTRADAY_SUBSCRIPTION',
            'isActive' => true
        ]);

        // Fallback si pas de page configurée
        if (!$specialPage) {
            $specialPage = $this->createDefaultSpecialPage(
                'INTRADAY_SUBSCRIPTION',
                'Demande d\'adhésion à la méthode Intraday'
            );
        }

        return $this->handleSubscriptionRequest(
            $request,
            'intraday',
            new IntradayRequest(),
            IntradayRequestType::class,
            $specialPage,
            'subscription/intraday-subscription.html.twig'
        );
    }
    private function handleSubscriptionRequest(
        Request $request,
        string $subscriptionType,
        object $subscriptionRequest,
        string $formType,
        SpecialPage $specialPage,
        string $template
    ): Response {
        /** @var User|null $user */
        $user = $this->getUser();
        $isIntraday = ($subscriptionType === 'intraday');

        // Vérifier si l'utilisateur a déjà accès
        if ($user && ($isIntraday ? $user->isIntraday() : $user->isInvestisseur())) {
            $this->addFlash('info', sprintf('Vous avez déjà accès à la méthode %s.', ucfirst($subscriptionType)));
            return $this->redirectToFirstSectionMenu($isIntraday ? 'INTRADAY' : 'INVESTISSEUR');
        }

        // Vérifier si une demande est déjà en cours
        if ($user && ($isIntraday ? $user->isInterestedInIntradayMethode() : $user->isInterestedInInvestorMethod())) {
            $this->addFlash('info', sprintf('Votre demande pour la méthode %s est en cours de traitement.', ucfirst($subscriptionType)));
            return $this->redirectToFirstSectionMenu('HOME');
        }

        // Créer le formulaire
        $form = $this->createForm($formType, null, [
            'existing_user' => (bool) $user,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Sauvegarder UNIQUEMENT la demande (pas l'utilisateur)
            $subscriptionRequest->setCivility($data->getCivility());
            $subscriptionRequest->setLastname($data->getLastname());
            $subscriptionRequest->setFirstname($data->getFirstname());
            $subscriptionRequest->setEmail($data->getEmail());
            $subscriptionRequest->setCreatedAt(new \DateTime());

            $this->entityManager->persist($subscriptionRequest);
            $this->entityManager->flush();

            // Message de succès
            $this->addFlash('success', sprintf(
                'Votre demande d\'adhésion à la méthode %s a été enregistrée. Nous vous contacterons prochainement.',
                ucfirst($subscriptionType)
            ));

            return $this->redirectToFirstSectionMenu('HOME');
        }

        return $this->render($template, [
            'form' => $form,
            'specialPage' => $specialPage,
        ]);
    }

    /**
     * Redirige vers le premier menu d'une section
     */
    private function redirectToFirstSectionMenu(string $section): Response
    {
        $firstMenu = $this->entityManager->getRepository(\App\Entity\Menu::class)->findOneBy(
            ['section' => $section, 'isActive' => true],
            ['menuorder' => 'ASC']
        );

        if ($firstMenu) {
            $routeName = match ($section) {
                'INVESTISSEUR' => 'app_investisseur_page',
                'INTRADAY' => 'app_intraday_page',
                'HOME' => 'app_home_page',
                default => 'home'
            };

            return $this->redirectToRoute($routeName, ['slug' => $firstMenu->getSlug()]);
        }

        // Fallback
        return $this->redirectToRoute('home');
    }

    /**
     * Crée une page spéciale par défaut
     */
    private function createDefaultSpecialPage(string $code, string $title): SpecialPage
    {
        $page = new SpecialPage();
        $page->setCode($code);
        $page->setTitle($title);
        $page->setUpdatedAt(new \DateTimeImmutable());

        return $page;
    }
}
