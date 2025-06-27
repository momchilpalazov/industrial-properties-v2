<?php

namespace App\Controller\Admin;

use App\Service\DeepSeekTranslationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TranslationController extends AbstractController
{
    #[Route('/admin/translate', name: 'admin_translate', methods: ['POST'])]
    public function translate(Request $request, DeepSeekTranslationService $translationService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Invalid JSON data'
            ], 400);
        }

        $text = $data['text'] ?? '';
        $sourceLang = $data['source'] ?? 'bg';
        $targetLang = $data['target'] ?? 'en';
        $context = $data['context'] ?? 'real_estate';

        if (empty($text)) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Text is required'
            ], 400);
        }

        try {
            $translatedText = $translationService->translate($text, $sourceLang, $targetLang, $context);

            return new JsonResponse([
                'success' => true,
                'translatedText' => $translatedText
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
