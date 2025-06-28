<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test-admin', name: 'test_admin')]
    public function testAdmin(): Response
    {
        return $this->render('admin/test.html.twig', [
            'message' => 'Test page working!'
        ]);
    }
}
