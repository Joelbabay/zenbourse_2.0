<?php

namespace App\Controller;

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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @method User getUser()
 */
class FileDownloadController extends AbstractController
{
    private $fileDirectory;
    private $passwordHasher;
    public function __construct(
        private EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        UserPasswordHasherInterface $passwordHasher,
        private StatutService $statutService
    ) {
        $this->fileDirectory = $parameterBag->get('download_directory') . '/file.pdf';
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/file/download', name: 'file_download')]
    public function index(): Response
    {
        $user = $this->getUser();
        if ($user) {
            if (!$user->isDownloadRequestSubmitted()) {
                $user->setIsDownloadRequestSubmitted(true);
            }
            $this->entityManager->flush();
        }

        $filePath = $this->fileDirectory;

        if (file_exists($filePath)) {
            $response = new BinaryFileResponse($filePath);
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                basename($filePath)
            );

            //dd($filePath);
            return $response;
        } else {
            throw $this->createNotFoundException('The file does not exist');
        }
    }

    #[Route('/download', name: 'home_download_page')]
    public function requestDownload(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DownloadRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $user = $userRepository->findOneBy(['email' => $data->getEmail()]);

            $this->statutService->statutService($data->getEmail(), $data);

            return $this->redirectToRoute('file_download');
        }
        return $this->render('download/request_download.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
