<?php

namespace App\Controller\Public;

use App\Entity\Property;
use App\Entity\PropertyCategory;
use App\Form\PromotionFilterType;
use App\Repository\PromotionRepository;
use App\Repository\PropertyRepository;
use App\Repository\PropertyCategoryRepository;
use App\Repository\PropertyTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

class AuctionController extends AbstractController
{
    private PromotionRepository $promotionRepository;
    private PropertyRepository $propertyRepository;
    private PropertyCategoryRepository $propertyCategoryRepository;
    private PropertyTypeRepository $propertyTypeRepository;
    private PaginatorInterface $paginator;

    public function __construct(
        PromotionRepository $promotionRepository,
        PropertyRepository $propertyRepository,
        PropertyCategoryRepository $propertyCategoryRepository,
        PropertyTypeRepository $propertyTypeRepository,
        PaginatorInterface $paginator
    ) {
        $this->promotionRepository = $promotionRepository;
        $this->propertyRepository = $propertyRepository;
        $this->propertyCategoryRepository = $propertyCategoryRepository;
        $this->propertyTypeRepository = $propertyTypeRepository;
        $this->paginator = $paginator;
    }

    #[Route('/auctions', name: 'app_auctions', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // Създаваме формата за филтриране
        $form = $this->createForm(PromotionFilterType::class);
        $form->handleRequest($request);

        // Намираме категорията "Търгове"
        $auctionCategory = $this->propertyCategoryRepository->findOneBy(['name' => 'Търгове']);
        
        if (!$auctionCategory) {
            // Ако категорията не съществува, показваме празен списък
            $properties = [];
        } else {
            // Вземаме имотите от категория "Търгове"
            $propertiesQuery = $this->propertyRepository->createQueryBuilder('p')
                ->andWhere('p.category = :category')
                ->andWhere('p.isActive = :active')
                ->setParameter('category', $auctionCategory)
                ->setParameter('active', true)
                ->leftJoin('p.type', 't')
                ->orderBy('t.category', 'ASC')
                ->addOrderBy('t.position', 'ASC')
                ->getQuery();
                
            // Пагинация на резултатите
            $properties = $this->paginator->paginate(
                $propertiesQuery,
                $request->query->getInt('page', 1),
                9
            );
        }

        return $this->render('auction/index.html.twig', [
            'properties' => $properties,
            'form' => $form->createView(),
            'category' => $auctionCategory,
            'here_maps_api_key' => $this->getParameter('app.here_maps_api_key'),
            'here_maps_default_lat' => $this->getParameter('app.here_maps_default_lat'),
            'here_maps_default_lng' => $this->getParameter('app.here_maps_default_lng'),
            'here_maps_default_zoom' => $this->getParameter('app.here_maps_default_zoom')
        ]);
    }
} 