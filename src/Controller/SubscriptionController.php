<?php

namespace App\Controller;

use App\Entity\User;
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

        if ($user && $user->isInvestisseur()) {

            $this->addFlash('info', 'Vous êtes déjà abonné à la méthode investisseur.');
            return $this->redirectToRoute('investisseur_home');
        }

        if ($user && $user->isInterestedInInvestorMethod()) {
            $this->addFlash('info', 'Votre demande est en cours de validation');
            return $this->redirectToRoute('home');
        }

        if ($user) {
            $form = $this->createForm(InvestisseurSubscriptionType::class, $user, [
                'existing_user' => true,
            ]);
        } else {
            $user = new User();
            $form = $this->createForm(InvestisseurSubscriptionType::class, $user, [
                'existing_user' => false,
            ]);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$user->getId()) {
                $user->setCreatedAt(new \DateTime());
                $user->setPassword($this->passwordHasher->hashPassword($user, 'zenbourse'));
                if (!in_array($user->getStatut(), ['CLIENT', 'INVITE'])) {
                    $user->setStatut('PROSPECT');
                }
                $user->setInterestedInInvestorMethod(true);
                $this->entityManager->persist($user);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Votre demande d\'adhésion a été soumise avec succès.');

            return $this->redirectToRoute('home');
        }


        return $this->render('subscription/subscription.html.twig', [
            'form' => $form
        ]);
    }
}
