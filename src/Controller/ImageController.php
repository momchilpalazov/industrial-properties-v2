<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class ImageController extends AbstractController
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    #[Route('/img/{type}/{subtype}/{filename}', name: 'app_image', requirements: ['type' => 'about|properties|blog', 'subtype' => 'team|company', 'filename' => '.+'])]
    public function serveImage(string $type, string $subtype, string $filename): Response
    {
        $imagePath = $this->getParameter('kernel.project_dir') . '/img/' . $type . '/' . $subtype . '/' . $filename;
        
        // Логваме пътя за дебъгване
        $this->logger->info('Опит за достъп до изображение', [
            'path' => $imagePath,
            'exists' => file_exists($imagePath),
            'readable' => is_readable($imagePath),
            'permissions' => decoct(fileperms($imagePath) & 0777),
            'owner' => function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($imagePath))['name'] : fileowner($imagePath)
        ]);

        if (!file_exists($imagePath)) {
            $this->logger->error('Файлът не съществува', ['path' => $imagePath]);
            throw new NotFoundHttpException('Image not found: ' . $imagePath);
        }

        if (!is_readable($imagePath)) {
            $this->logger->error('Файлът не може да бъде прочетен', [
                'path' => $imagePath,
                'permissions' => decoct(fileperms($imagePath) & 0777)
            ]);
            throw new NotFoundHttpException('Image is not readable: ' . $imagePath);
        }

        try {
            $response = new BinaryFileResponse($imagePath);
            $response->headers->set('Content-Type', mime_content_type($imagePath));
            return $response;
        } catch (\Exception $e) {
            $this->logger->error('Грешка при четене на файла', [
                'path' => $imagePath,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
} 