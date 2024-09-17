<?php

namespace App\Controller;

use App\Entity\IntradayRequest;
use App\Entity\InvestisseurRequest;
use App\Entity\User;
use App\Form\IntradayRequestType;
use App\Form\InvestisseurSubscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @method User getUser()
 */
class SubscriptionController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher,)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/subscribe/investisseur', name: 'home_investisseur_subscription')]
    public function subscribeInvestisseur(Request $request): Response
    {
        return $this->handleSubscriptionRequest(
            $request,
            'investisseur',
            new InvestisseurRequest(),
            'Votre demande d\'adhésion à la méthode investisseur a été soumise avec succès.',
            'investisseur_home'
        );
    }

    #[Route('/subscribe/intraday', name: 'home_intraday_subscription')]
    public function subscribeIntraday(Request $request): Response
    {
        return $this->handleSubscriptionRequest(
            $request,
            'intraday',
            new IntradayRequest(),
            'Votre demande d\'adhésion à la méthode intraday a été soumise avec succès.',
            'intraday_home',
            true
        );
    }
    private function handleSubscriptionRequest(
        Request $request,
        string $subscriptionType, // 'investisseur' ou 'intraday'
        object $subscriptionRequest, // Instance de InvestisseurRequest ou IntradayRequest
        string $successMessage, // Message de succès à afficher
        string $redirectRoute, // Route de redirection après succès
        bool $isIntraday = false // Booléen pour différencier les types d'abonnement
    ): Response {
        $user = $this->getUser();
        $formType = $isIntraday ? IntradayRequestType::class : InvestisseurSubscriptionType::class;

        if ($user && ($isIntraday ? $user->isIntraday() : $user->isInvestisseur())) {
            $this->addFlash('info', sprintf('Vous êtes déjà abonné à la méthode %s.', $subscriptionType));
            return $this->redirectToRoute($redirectRoute);
        }

        if ($user && ($isIntraday ? $user->isInterestedInIntradayMethode() : $user->isInterestedInInvestorMethod())) {
            $this->addFlash('info', sprintf('Votre demande sur la méthode %s est en cours de validation', ucfirst($subscriptionType)));
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm($formType, $subscriptionRequest, [
            'existing_user' => (bool) $user,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (!$user) {
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data->getEmail()]);
            }

            $subscriptionRequest->setCreatedAt(new \DateTime());

            if ($user) {
                if ($isIntraday) {
                    $user->setInterestedInIntradayMethode(true);
                } else {
                    $user->setInterestedInInvestorMethod(true);
                }

                if (!in_array($user->getStatut(), ['CLIENT', 'INVITE'])) {
                    $user->setStatut('PROSPECT');
                }
            } else {
                $user = new User();
                $user->setCivility($data->getCivility());
                $user->setEmail($data->getEmail());
                $user->setLastname($data->getLastname());
                $user->setFirstname($data->getFirstname());
                $user->setPassword($this->passwordHasher->hashPassword($user, 'zenbourse'));
                $user->setStatut('PROSPECT');
                $user->setCreatedAt(new \DateTimeImmutable());

                if ($isIntraday) {
                    $user->setInterestedInIntradayMethode(true);
                } else {
                    $user->setInterestedInInvestorMethod(true);
                }

                $this->entityManager->persist($user);
            }

            $this->entityManager->persist($subscriptionRequest);
            $this->entityManager->flush();

            $this->addFlash('success', $successMessage);

            return $this->redirectToRoute('home');
        }

        return $this->render('subscription/subscription.html.twig', [
            'form' => $form
        ]);
    }
}
