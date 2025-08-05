<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangeLocalPasswordType;
use App\Form\UserProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/mon-compte', name: 'app_user_')]
final class UserController extends AbstractController
{
    #[Route('/profile', name: 'profile')]
    public function account(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        UserRepository $userRepository
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non connecté');
        }

        // Formulaire pour les informations personnelles
        $profileForm = $this->createForm(UserProfileType::class, $user);
        $profileForm->handleRequest($request);

        // Formulaire pour le changement de mot de passe
        $passwordForm = $this->createForm(ChangeLocalPasswordType::class);
        $passwordForm->handleRequest($request);

        // Traitement du formulaire des informations personnelles
        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Vos informations ont été mises à jour avec succès.');
            return $this->redirectToRoute('app_user_profile');
        }

        // Traitement du formulaire de changement de mot de passe
        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $currentPassword = $passwordForm->get('currentPassword')->getData();
            $newPassword = $passwordForm->get('newPassword')->getData();

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $passwordForm->get('currentPassword')->addError(new \Symfony\Component\Form\FormError('Mot de passe actuel incorrect.'));
            } else {
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                $em->flush();
                $this->addFlash('success', 'Mot de passe changé avec succès.');
                return $this->redirectToRoute('app_user_profile');
            }
        }

        // Déterminer quelle modal afficher en cas d'erreur
        $showProfileModal = false;
        $showPasswordModal = false;

        if ($profileForm->isSubmitted() && !$profileForm->isValid()) {
            $showProfileModal = true;
        }

        if ($passwordForm->isSubmitted() && !$passwordForm->isValid()) {
            $showPasswordModal = true;
        }

        return $this->render('user/account.html.twig', [
            'user' => $user,
            'profileForm' => $profileForm->createView(),
            'passwordForm' => $passwordForm->createView(),
            'showProfileModal' => $showProfileModal,
            'showPasswordModal' => $showPasswordModal,
        ]);
    }
}
