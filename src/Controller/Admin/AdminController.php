<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('', name: 'admin_')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/settings', name: 'settings')]
    public function settings(): Response
    {
        return $this->render('admin/settings.html.twig', [
            'settings' => [] // TODO: Вземи настройките от базата
        ]);
    }

    #[Route('/profile', name: 'profile')]
    public function profile(): Response
    {
        return $this->render('admin/profile.html.twig', [
            'user' => $this->getUser()
        ]);
    }
} 