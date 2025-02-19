<?php

namespace App\Controller\Public;

use App\Repository\FaqRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FaqController extends AbstractController
{
    public function __construct(
        private FaqRepository $faqRepository
    ) {}

    #[Route('/faq', name: 'app_faq_index')]
    public function index(Request $request): Response
    {
        $category = $request->query->get('category');
        $query = $request->query->get('q');
        $locale = $request->getLocale();

        $faqs = $category 
            ? $this->faqRepository->findByCategory($category, $locale)
            : $this->faqRepository->findActive($locale);

        return $this->render('faq/index.html.twig', [
            'faqs' => $faqs,
            'categories' => $this->faqRepository->getCategories(),
            'currentCategory' => $category,
            'searchQuery' => $query
        ]);
    }
} 