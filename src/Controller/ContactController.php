<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\Role;
use App\Entity\User;
use App\Form\ContactType;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}
    #[Route('/contact', name: 'app_home_contact')]
    public function index(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $contact = new Contact();

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $contact->setCreatedAt(new \DateTimeImmutable());
            $data = $request->request->all();
            $email = $data['contact']['email'];
            $user = $userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                // Crée un nouvel utilisateur prospect
                $user = new User();
                $user->setEmail($email);
                $user->setCivility($data['contact']['civility']);
                $user->setFirstname($data['contact']['firstname']);
                $user->setLastname($data['contact']['lastname']);
                $user->setStatut('PROSPECT');
                $user->setPassword($this->passwordHasher->hashPassword($user, 'zenbourse'));
                $user->setCreatedAt(new \DateTimeImmutable());

                //dd($user);
                $entityManager->persist($user);
            }

            $entityManager->persist($contact);
            $entityManager->flush();

            $this->addFlash('success', 'Votre message a été bien envoyer');
            return $this->redirectToRoute('home');
        }

        return $this->render('home/contact.html.twig', [
            'form' => $form,
        ]);
    }
}
