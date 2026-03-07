<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\User;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    private const SPAM_KEYWORDS = [
        'viagra',
        'cialis',
        'casino',
        'poker',
        'loan',
        'credit',
        'bitcoin',
        'cryptocurrency',
        'seo services',
        'buy now',
        'click here',
        'limited time',
        'earn money',
        'work from home',
        'investment opportunity',
        'no credit check',
        'free money',
        'congratulations',
        'winner',
        'prize',
        'inheritance',
        'western union',
        'bank transfer',
        'urgent',
        'act now',
        'prêt',
        'crédit rapide',
    ];

    private const SUSPICIOUS_DOMAINS = [
        'tempmail.com',
        'guerrillamail.com',
        'mailinator.com',
        '10minutemail.com',
        'throwaway.email',
        'yopmail.com',
        'trashmail.com',
    ];
    public function __construct(private UserPasswordHasherInterface $passwordHasher, private ContactRepository $contactRepository) {}
    #[Route('/contact', name: 'app_home_contact')]
    public function index(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $contact = new Contact();

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $honeypot = $form->get('website')->getData();
            if (!empty($honeypot)) {
                // Bot détecté (a rempli le champ invisible)
                $this->addFlash('front_error', 'Une erreur s\'est produite. Veuillez réessayer.');
                return $this->redirectToRoute('app_home_contact');
            }

            // 3. VÉRIFICATION EMAIL SUSPECT
            $email = $contact->getEmail();
            if ($this->isSuspiciousEmail(email: $email)) {
                $this->addFlash('front_error', 'Adresse email non autorisée. Veuillez utiliser une adresse email valide.');
                return $this->redirectToRoute('app_home_contact');
            }

            // 4. VÉRIFICATION CONTENU SPAM
            $message = $contact->getContent();

            if ($this->containsSpam($message)) {
                $this->addFlash('front_error', 'Votre message contient du contenu non autorisé.');
                return $this->redirectToRoute('app_home_contact');
            }

            $contact->setCreatedAt(new \DateTimeImmutable());
            $data = $request->request->all();
            $email = $data['contact']['email'];
            $user = $userRepository->findOneBy(['email' => $email]);

            /*if (!$user) {
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
            }*/

            $entityManager->persist($contact);
            $entityManager->flush();

            $this->addFlash('success', 'Votre message a été bien envoyer');
            return $this->redirectToRoute('home');
        }

        return $this->render('home/contact.html.twig', [
            'form' => $form,
        ]);
    }
    /**
     * Vérifie si l'email est suspect (VERSION RAPIDE - SANS DNS)
     */
    private function isSuspiciousEmail(string $email): bool
    {
        $domain = substr(strrchr($email, "@"), 1);

        // Vérifier domaines suspects
        foreach (self::SUSPICIOUS_DOMAINS as $suspiciousDomain) {
            if (str_contains($domain, $suspiciousDomain)) {
                return true;
            }
        }

        // Vérifications rapides supplémentaires
        // Email avec trop de chiffres (souvent spam)
        if (preg_match('/\d{5,}/', $email)) {
            return true;
        }

        // Email avec patterns suspects
        if (preg_match('/^(test|spam|fake|temp|admin)\d*@/i', $email)) {
            return true;
        }

        return false;
    }

    /**
     * Détecte le contenu spam (VERSION OPTIMISÉE)
     */
    private function containsSpam(string $text): bool
    {
        $textLower = strtolower($text);

        // Vérifier mots-clés (liste réduite pour performance)
        foreach (self::SPAM_KEYWORDS as $keyword) {
            if (str_contains($textLower, $keyword)) {
                return true;
            }
        }

        // Vérifier trop de liens (RAPIDE)
        if (substr_count($textLower, 'http') > 2) {
            return true;
        }

        // Vérifier MAJUSCULES excessives (souvent spam)
        $upperCount = preg_match_all('/[A-Z]/', $text);
        if ($upperCount > strlen($text) * 0.5) {
            return true;
        }

        return false;
    }
}
