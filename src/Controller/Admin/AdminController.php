<?php

namespace App\Controller\Admin;

use App\Repository\PropertyRepository;
use App\Repository\PropertyInquiryRepository;
use App\Repository\BlogPostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Process\Process;

#[Route('', name: 'admin_')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private PropertyRepository $propertyRepository,
        private PropertyInquiryRepository $inquiryRepository,
        private BlogPostRepository $blogPostRepository,
        private UserRepository $userRepository
    ) {}

    #[Route('/', name: 'dashboard')]
    public function dashboard(): Response
    {
        $queryBuilder = $this->inquiryRepository->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.isRead = :isRead')
            ->setParameter('isRead', false);

        return $this->render('admin/dashboard.html.twig', [
            'user' => $this->getUser(),
            'stats' => [
                'properties' => $this->propertyRepository->count([]),
                'inquiries' => $queryBuilder->getQuery()->getSingleScalarResult(),
                'posts' => $this->blogPostRepository->count([]),
                'users' => $this->userRepository->count([])
            ],
            'latest_properties' => $this->propertyRepository->findBy([], ['createdAt' => 'DESC'], 5),
            'latest_inquiries' => $this->inquiryRepository->findBy([], ['createdAt' => 'DESC'], 5)
        ]);
    }

    #[Route('/settings', name: 'settings')]
    public function settings(): Response
    {
        // TODO: Зареждане на настройките от базата данни
        $settings = [
            'site_name' => 'Industrial Properties',
            'admin_email' => 'admin@example.com',
            'items_per_page' => 10,
            'here_maps_api_key' => $_ENV['HERE_MAPS_API_KEY'] ?? ''
        ];

        return $this->render('admin/settings.html.twig', [
            'settings' => $settings
        ]);
    }

    #[Route('/settings/save', name: 'settings_save', methods: ['POST'])]
    public function saveSettings(Request $request): Response
    {
        // TODO: Запазване на настройките в базата данни
        $this->addFlash('success', 'Настройките бяха запазени успешно');
        return $this->redirectToRoute('admin_settings');
    }

    #[Route('/settings/api', name: 'settings_api', methods: ['POST'])]
    public function saveApiSettings(Request $request): Response
    {
        // TODO: Запазване на API настройките
        $this->addFlash('success', 'API настройките бяха запазени успешно');
        return $this->redirectToRoute('admin_settings');
    }

    #[Route('/profile', name: 'profile')]
    public function profile(): Response
    {
        return $this->render('admin/profile.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/cache/clear', name: 'cache_clear', methods: ['POST'])]
    public function clearCache(): Response
    {
        $process = new Process(['php', 'bin/console', 'cache:clear', '--env=prod']);
        $process->setWorkingDirectory($this->getParameter('kernel.project_dir'));
        $process->run();

        if (!$process->isSuccessful()) {
            $this->addFlash('error', 'Възникна грешка при изчистването на кеша: ' . $process->getErrorOutput());
        } else {
            $this->addFlash('success', 'Кешът беше изчистен успешно');
        }

        return $this->redirectToRoute('admin_settings');
    }
} 