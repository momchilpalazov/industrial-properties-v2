<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'admin_')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function dashboard(): Response
    {
        // Вземаме обобщена статистика
        $stats = [
            'properties' => [
                'total' => 0, // TODO: Вземи от PropertyRepository
                'active' => 0,
                'featured' => 0
            ],
            'blog_posts' => [
                'total' => 0, // TODO: Вземи от BlogRepository
                'published' => 0,
                'draft' => 0
            ],
            'inquiries' => [
                'total' => 0, // TODO: Вземи от InquiryRepository
                'new' => 0,
                'answered' => 0
            ],
            'users' => [
                'total' => 0, // TODO: Вземи от UserRepository
                'active' => 0
            ]
        ];

        // Вземаме последните активности
        $recentActivity = [
            'properties' => [], // TODO: Последно добавени/редактирани имоти
            'inquiries' => [], // TODO: Последни запитвания
            'blog_posts' => [] // TODO: Последно публикувани постове
        ];

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'recent_activity' => $recentActivity
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