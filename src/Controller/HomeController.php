<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->redirect('/home');
    }

    #[Route('/uploadImage', name: 'upload_image')]
    public function upload(Request $request): JsonResponse
    {
        $file = $request->files->get('upload');

        if ($file) {
            $filename = uniqid() . '.' . $file->guessExtension();
            $file->move($this->getParameter('kernel.project_dir') . '/public/uploads', $filename);

            return new JsonResponse([
                'url' => '/uploads/' . $filename
            ]);
        }

        return new JsonResponse(['error' => 'Aucun fichier reçu'], 400);
    }
}
