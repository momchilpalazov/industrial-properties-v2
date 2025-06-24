<?php

namespace App\Controller\Public;

use App\Repository\FaqRepository;
use App\Repository\FaqCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FaqController extends AbstractController
{
    public function __construct(
        private FaqRepository $faqRepository,
        private FaqCategoryRepository $faqCategoryRepository
    ) {}

    #[Route('/faq', name: 'app_faq_index')]
    public function index(Request $request): Response
    {
        $categorySlug = $request->query->get('category');
        $query = $request->query->get('q');
        $locale = $request->getLocale();

        $category = null;
        if ($categorySlug) {
            $category = $this->faqCategoryRepository->findBySlug($categorySlug);
        }

        $faqs = $category 
            ? $this->faqRepository->findByCategory($category, $locale)
            : $this->faqRepository->findActive($locale);

        $categories = $this->faqCategoryRepository->findVisible();

        return $this->render('faq/index.html.twig', [
            'faqs' => $faqs,
            'categories' => $categories,
            'currentCategory' => $category,
            'searchQuery' => $query
        ]);
    }
}