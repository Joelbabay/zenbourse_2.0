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
            if (!in_array($user->getStatut(), ['CLIENT', 'INVITE'])) {
                $user->setStatut('PROSPECT');
            }
            $downloadEntity = new Download();
            $downloadEntity->setCivility($user->getCivility());
            $downloadEntity->setLastname($user->getLastname());
            $downloadEntity->setFirstname($user->getFirstname());
            $downloadEntity->setEmail($user->getEmail());
            $downloadEntity->setCreatedAt(new \Datetime());
            $this->entityManager->persist($downloadEntity);
        }
        $this->entityManager->flush();
        return $this->redirectToRoute('home_download_page');
    }

    #[Route('/download', name: 'home_download_page')]
    public function requestDownload(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $downloadEntity = new Download();
        $form = $this->createForm(DownloadRequestType::class, $downloadEntity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $downloadEntity->setCreatedAt(new \DateTime());
            $data = $form->getData();

            $user = $userRepository->findOneBy(['email' => $data->getEmail()]);

            if ($user) {
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

                $entityManager->persist($user);
            }
            $user->setIsDownloadRequestSubmitted(true);
            $entityManager->persist($downloadEntity);
            $entityManager->flush();

            $filePath = $this->fileDirectory;

            if (file_exists($filePath)) {
                $response = new BinaryFileResponse($filePath);
                $response->setContentDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    basename($filePath)
                );
                return $response;
            } else {
                throw $this->createNotFoundException('The file does not exist');
            }
        }
        return $this->render('download/request_download.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
