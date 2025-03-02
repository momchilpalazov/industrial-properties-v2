<?php

namespace App\Controller\Admin;

use App\Entity\Property;
use App\Entity\PropertyInquiry;
use App\Entity\PropertyView;
use App\Repository\PropertyInquiryRepository;
use App\Repository\PropertyRepository;
use App\Repository\PropertyViewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/statistics')]
class DashboardStatisticsController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private PropertyRepository $propertyRepository;
    private PropertyInquiryRepository $inquiryRepository;
    private PropertyViewRepository $viewRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        PropertyRepository $propertyRepository,
        PropertyInquiryRepository $inquiryRepository,
        PropertyViewRepository $viewRepository
    ) {
        $this->entityManager = $entityManager;
        $this->propertyRepository = $propertyRepository;
        $this->inquiryRepository = $inquiryRepository;
        $this->viewRepository = $viewRepository;
    }

    #[Route('', name: 'admin_statistics')]
    public function index(): Response
    {
        try {
            // Общ брой имоти по статус
            $propertiesByStatus = $this->propertyRepository->countByStatus();

            // Запитвания по месеци
            $inquiriesByMonth = $this->inquiryRepository->countByMonth();

            // Топ 5 локации
            $topLocations = $this->inquiryRepository->findTopLocations(5);

            // Имоти с най-много разглеждания
            try {
                $mostViewedProperties = $this->viewRepository->findMostViewedProperties(5);
            } catch (\Exception $e) {
                $mostViewedProperties = [];
            }

            // Средно време за продажба
            $avgSaleTime = $this->propertyRepository->calculateAverageSaleTime();

            // Средно време за отговор на запитване
            $avgResponseTime = $this->inquiryRepository->calculateAverageResponseTime();

            return $this->render('admin/statistics/index.html.twig', [
                'propertiesByStatus' => $propertiesByStatus ?? [],
                'inquiriesByMonth' => $inquiriesByMonth ?? [],
                'topLocations' => $topLocations ?? [],
                'mostViewedProperties' => $mostViewedProperties ?? [],
                'avgSaleTime' => $avgSaleTime,
                'avgResponseTime' => $avgResponseTime
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Възникна грешка при зареждане на статистиките: ' . $e->getMessage());
            return $this->redirectToRoute('admin_dashboard');
        }
    }
} 