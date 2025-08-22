<?php

namespace App\Controller;

use App\Entity\Menu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        // Redirection vers le premier menu actif de la section HOME
        $firstHomeMenu = $this->entityManager->getRepository(Menu::class)->findOneBy(
            ['section' => 'HOME', 'isActive' => true],
            ['menuorder' => 'ASC']
        );

        if ($firstHomeMenu) {
            return $this->redirectToRoute('app_home_page', ['slug' => $firstHomeMenu->getSlug()]);
        }

        // Fallback si aucun menu HOME n'est trouvé
        return $this->redirectToRoute('app_home_page', ['slug' => 'accueil']);
    }

    #[Route('/upload-image', name: 'upload_image', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $uploadedFile = $request->files->get('upload');

        if (!$uploadedFile) {
            return new JsonResponse(['error' => ['message' => 'Aucun fichier envoyé.']], 400);
        }

        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
        $filename = uniqid() . '.' . $uploadedFile->guessExtension();
        $uploadedFile->move($uploadsDir, $filename);

        return new JsonResponse([
            'url' => '/uploads/' . $filename
        ]);
    }
}
