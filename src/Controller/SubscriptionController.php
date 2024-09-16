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
        $user = $this->getUser();
        $investisseurRequest = new InvestisseurRequest();

        if ($user && $user->isInvestisseur()) {

            $this->addFlash('info', 'Vous êtes déjà abonné à la méthode investisseur.');
            return $this->redirectToRoute('investisseur_home');
        }

        if ($user && $user->isInterestedInInvestorMethod()) {
            $this->addFlash('info', 'Votre demande est en cours de validation');
            return $this->redirectToRoute('home');
        }

        if ($user) {
            $form = $this->createForm(InvestisseurSubscriptionType::class, $investisseurRequest, [
                'existing_user' => true,
            ]);
        } else {
            $user = new User();
            $form = $this->createForm(InvestisseurSubscriptionType::class, $investisseurRequest, [
                'existing_user' => false,
            ]);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $investisseurRequest->setCreatedAt(new \DateTime());
            if ($user->getId()) {
                $user->setInterestedInInvestorMethod(true);
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
                $user->setInterestedInInvestorMethod(true);
                $this->entityManager->persist($user);
            }

            $this->entityManager->persist($investisseurRequest);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre demande d\'adhésion a été soumise avec succès.');

            return $this->redirectToRoute('home');
        }


        return $this->render('subscription/subscription.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/subscribe/intraday', name: 'home_intraday_subscription')]
    public function subscribeIntraday(Request $request): Response
    {
        $user = $this->getUser();
        $intradayRequest = new IntradayRequest();

        if ($user && $user->isIntraday()) {
            $this->addFlash('info', 'Vous êtes déjà abonné à la méthode intraday.');
            return $this->redirectToRoute('intraday_home');
        }

        if ($user && $user->isInterestedInIntradayMethode()) {
            $this->addFlash('info', 'Votre demande sur la méthode Intraday est en cours de validation');
            return $this->redirectToRoute('home');
        }

        if ($user) {
            $form = $this->createForm(IntradayRequestType::class, $intradayRequest, [
                'existing_user' => true,
            ]);
        } else {
            $user = new User();
            $form = $this->createForm(IntradayRequestType::class, $intradayRequest, [
                'existing_user' => false,
            ]);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $intradayRequest->setCreatedAt(new \DateTime());
            if ($user->getId()) {
                $user->setInterestedInIntradayMethode(true);
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
                $user->setInterestedInIntradayMethode(true);
                $this->entityManager->persist($user);
            }

            $this->entityManager->persist($intradayRequest);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre demande d\'adhésion a été soumise avec succès.');

            return $this->redirectToRoute('home');
        }


        return $this->render('subscription/subscription.html.twig', [
            'form' => $form
        ]);
    }
}
