<?php

namespace App\Controller\Public;

use App\Repository\PromotionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PromotionController extends AbstractController
{
    private PromotionRepository $promotionRepository;

    public function __construct(PromotionRepository $promotionRepository)
    {
        $this->promotionRepository = $promotionRepository;
    }

    #[Route('/promotions', name: 'app_promotions', methods: ['GET'])]
    public function index(): Response
    {
        // Вземаме само активните и платени промоции
        $promotions = $this->promotionRepository->findActivePromotions();

        return $this->render('promotion/index.html.twig', [
            'promotions' => $promotions,
        ]);
    }
} 