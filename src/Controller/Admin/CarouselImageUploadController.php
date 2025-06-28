<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/carousel')]
class CarouselImageUploadController extends AbstractController
{
    #[Route('/upload', name: 'admin_carousel_upload', methods: ['POST'])]
    public function upload(Request $request, SluggerInterface $slugger): JsonResponse
    {
        $uploadedFile = $request->files->get('image');

        if (!$uploadedFile) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Aucun fichier envoyé'
            ], 400);
        }

        // Vérifier le type de fichier
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($uploadedFile->getMimeType(), $allowedTypes)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WebP.'
            ], 400);
        }

        // Vérifier la taille (max 5MB)
        if ($uploadedFile->getSize() > 5 * 1024 * 1024) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Fichier trop volumineux. Taille maximum : 5MB.'
            ], 400);
        }

        try {
            // Créer le dossier de destination s'il n'existe pas
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/images/carousel';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Générer un nom de fichier unique
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $extension = $uploadedFile->guessExtension();
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

            // Déplacer le fichier
            $uploadedFile->move($uploadDir, $newFilename);

            // Retourner le chemin relatif pour la base de données
            $relativePath = '/images/carousel/' . $newFilename;

            return new JsonResponse([
                'success' => true,
                'path' => $relativePath,
                'filename' => $newFilename,
                'message' => 'Image uploadée avec succès'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de l\'upload : ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/list-images', name: 'admin_carousel_list_images', methods: ['GET'])]
    public function listImages(): JsonResponse
    {
        $carouselDir = $this->getParameter('kernel.project_dir') . '/public/images/carousel';
        $images = [];

        if (is_dir($carouselDir)) {
            $files = scandir($carouselDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && is_file($carouselDir . '/' . $file)) {
                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $images[] = [
                            'filename' => $file,
                            'path' => '/images/carousel/' . $file,
                            'size' => filesize($carouselDir . '/' . $file),
                            'modified' => filemtime($carouselDir . '/' . $file)
                        ];
                    }
                }
            }
        }

        // Trier par date de modification (plus récent en premier)
        usort($images, function ($a, $b) {
            return $b['modified'] - $a['modified'];
        });

        return new JsonResponse([
            'success' => true,
            'images' => $images
        ]);
    }

    #[Route('/delete-image', name: 'admin_carousel_delete_image', methods: ['POST'])]
    public function deleteImage(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $filename = $data['filename'] ?? null;

        if (!$filename) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Nom de fichier manquant'
            ], 400);
        }

        // Sécuriser le nom de fichier
        $filename = basename($filename);
        $filePath = $this->getParameter('kernel.project_dir') . '/public/images/carousel/' . $filename;

        if (!file_exists($filePath)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Fichier non trouvé'
            ], 404);
        }

        try {
            unlink($filePath);
            return new JsonResponse([
                'success' => true,
                'message' => 'Image supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], 500);
        }
    }
}
