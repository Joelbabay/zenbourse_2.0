<?php

namespace App\Controller;

use App\Entity\Download;
use App\Entity\User;
use App\Form\DownloadRequestType;
use App\Repository\UserRepository;
use App\Service\StatutService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

class FileDownloadController extends AbstractController
{
    private string $filePath;

    public function __construct(
        private EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        private StatutService $statutService
    ) {
        $this->filePath = $parameterBag->get('download_directory') . '/file.pdf';
    }

    #[Route('/file/download', name: 'file_download')]
    public function download(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User || !$user->isDownloadRequestSubmitted()) {
            throw $this->createAccessDeniedException('Téléchargement non autorisé.');
        }

        return $this->downloadFile();
    }

    #[Route('/download', name: 'home_download_page')]
    public function requestDownload(
        Request $request,
        UserRepository $userRepository
    ): Response {
        $download = new Download();
        $form = $this->createForm(DownloadRequestType::class, $download);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $download->setCreatedAt(new \DateTimeImmutable());
            $email = $download->getEmail();

            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user instanceof User) {
                $firstTime = $this->statutService->handleDownload($user);
                $this->entityManager->persist($user);

                if (!$firstTime) {
                    // utilisateur déjà enregistré → pas de doublon
                    $this->entityManager->flush();
                    return $this->downloadFile();
                }

                // enrichissement depuis User
                $download
                    ->setCivility($user->getCivility())
                    ->setFirstname($user->getFirstname())
                    ->setLastname($user->getLastname());
            }

            $this->entityManager->persist($download);
            $this->entityManager->flush();

            return $this->redirectToRoute('download_thank_you');
        }

        return $this->render('download/request_download.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/download/merci', name: 'download_thank_you')]
    public function thankYou(): Response
    {
        $user = $this->getUser();


        return $this->render('download/thank_you.html.twig');
    }

    private function downloadFile(): Response
    {
        if (!file_exists($this->filePath)) {
            throw $this->createNotFoundException('Le fichier n’existe pas.');
        }

        return (new BinaryFileResponse($this->filePath))
            ->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                basename($this->filePath)
            );
    }
}
