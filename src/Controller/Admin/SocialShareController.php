<?php

namespace App\Controller\Admin;

use App\Entity\Property;
use App\Entity\SocialPost;
use App\Service\FacebookService;
use App\Service\LinkedInService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/social')]
class SocialShareController extends AbstractController
{
    private FacebookService $facebookService;
    private LinkedInService $linkedInService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        FacebookService $facebookService,
        LinkedInService $linkedInService,
        EntityManagerInterface $entityManager
    ) {
        $this->facebookService = $facebookService;
        $this->linkedInService = $linkedInService;
        $this->entityManager = $entityManager;
    }

    #[Route('/share/{id}/{platform}', name: 'admin_social_share')]
    public function share(Request $request, Property $property, string $platform = 'facebook'): Response
    {
        try {
            $post = match($platform) {
                'facebook' => $this->facebookService->shareProperty($property),
                'linkedin' => $this->linkedInService->shareProperty($property),
                default => throw new \InvalidArgumentException('Невалидна социална платформа')
            };
            
            if ($post && $post->getStatus() === 'success') {
                $this->addFlash('success', "Имотът беше успешно споделен в " . ucfirst($platform));
            } else {
                $this->addFlash('error', "Възникна грешка при споделянето на имота в " . ucfirst($platform));
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Грешка: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_property_show', ['id' => $property->getId()]);
    }

    #[Route('/posts', name: 'admin_social_posts')]
    public function posts(): Response
    {
        $posts = $this->entityManager
            ->getRepository(SocialPost::class)
            ->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/social/posts.html.twig', [
            'posts' => $posts
        ]);
    }
} 