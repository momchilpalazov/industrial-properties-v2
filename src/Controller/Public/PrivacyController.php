<?php

declare(strict_types=1);

namespace App\Controller\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PrivacyController extends AbstractController
{
    #[Route('/privacy-policy', name: 'privacy_policy')]
    public function index(): Response
    {
        return $this->render('privacy/index.html.twig', [
            'controller_name' => 'PrivacyController',
        ]);
    }
} 