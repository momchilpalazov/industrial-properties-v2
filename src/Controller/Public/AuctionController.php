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

#[Route('/auctions', name: 'app_auctions_')]
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

    #[Route('', name: 'index', methods: ['GET'])]
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
    
    /**
     * Показва детайлите на имот от категория "Търгове" (Auctions)
     */
    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $property = $this->propertyRepository->find($id);

        if (!$property || !$property->isActive()) {
            throw $this->createNotFoundException('Имотът не е намерен');
        }
        
        // Проверяваме дали имотът е от категория "Търгове"
        $auctionCategory = $this->propertyCategoryRepository->findOneBy(['name' => 'Търгове']);
        
        if (!$auctionCategory || $property->getCategory()->getId() !== $auctionCategory->getId()) {
            throw $this->createNotFoundException('Имотът не е от категория "Търгове"');
        }

        // Задаване на reCAPTCHA ключ, който да се използва в шаблона
        try {
            // Опитваме се да вземем ключ от параметрите
            $recaptchaSiteKey = $this->getParameter('recaptcha.site_key');
        } catch (\Exception $e) {
            // Ако възникне грешка, използваме тестов ключ от документацията на Google
            // Този ключ работи само за localhost
            $recaptchaSiteKey = '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI';
        }

        return $this->render('property/show.html.twig', [
            'property' => $property,
            'recaptcha_site_key' => $recaptchaSiteKey,
            'auction_mode' => true,
            'here_maps_api_key' => $this->getParameter('app.here_maps_api_key')
        ]);
    }
    
    /**
     * Redirects from the general property show page to the auction property show page
     * for properties that are in the auction category.
     */
    #[Route('/redirect/{id}', name: 'redirect_from_property')]
    public function redirectFromProperty(int $id): Response
    {
        $property = $this->propertyRepository->find($id);
        
        if (!$property) {
            throw $this->createNotFoundException('Имотът не е намерен');
        }
        
        // Проверяваме дали имотът е в категория "Търгове"
        $auctionCategory = $this->propertyCategoryRepository->findOneBy(['name' => 'Търгове']);
            
        if ($property->getCategory() && $property->getCategory()->getId() === $auctionCategory->getId()) {
            // Ако имотът е в категория "Търгове", пренасочваме към auction_show
            return $this->redirectToRoute('app_auctions_show', ['id' => $id]);
        }
        
        // В противен случай връщаме обратно към стандартното показване на имота
        return $this->redirectToRoute('app_property_show', ['id' => $id]);
    }
} 