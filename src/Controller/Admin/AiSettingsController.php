<?php

namespace App\Controller\Admin;

use App\Service\AiChatbotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/ai', name: 'admin_ai_')]
#[IsGranted('ROLE_ADMIN')]
class AiSettingsController extends AbstractController
{
    public function __construct(
        private AiChatbotService $chatbotService
    ) {}

    /**
     * AI Settings management page
     */
    #[Route('/settings', name: 'settings', methods: ['GET'])]
    public function settings(): Response
    {
        $currentProvider = $this->chatbotService->getCurrentProvider();
        $availableProviders = $this->chatbotService->getAvailableProviders();

        return $this->render('admin/ai_settings/index.html.twig', [
            'current_provider' => $currentProvider,
            'available_providers' => $availableProviders,
            'chatbot_configured' => $this->chatbotService->isConfigured()
        ]);
    }

    /**
     * Get current AI provider via API
     */
    #[Route('/api/provider', name: 'api_get_provider', methods: ['GET'])]
    public function getProvider(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data' => [
                'current_provider' => $this->chatbotService->getCurrentProvider(),
                'available_providers' => $this->chatbotService->getAvailableProviders(),
                'is_configured' => $this->chatbotService->isConfigured()
            ]
        ]);
    }

    /**
     * Set AI provider via API
     */
    #[Route('/api/provider', name: 'api_set_provider', methods: ['POST'])]
    public function setProvider(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['provider'])) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => 'Provider is required',
                    'code' => 'MISSING_PROVIDER'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        $provider = $data['provider'];
        $availableProviders = $this->chatbotService->getAvailableProviders();
        
        if (!array_key_exists($provider, $availableProviders)) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => 'Invalid provider: ' . $provider,
                    'code' => 'INVALID_PROVIDER',
                    'available_providers' => array_keys($availableProviders)
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        $success = $this->chatbotService->setProvider($provider);
        
        if (!$success) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => 'Failed to set provider',
                    'code' => 'PROVIDER_SET_FAILED'
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([
            'success' => true,
            'data' => [
                'provider' => $this->chatbotService->getCurrentProvider(),
                'message' => 'AI provider switched successfully',
                'provider_info' => $availableProviders[$provider]
            ]
        ]);
    }

    /**
     * Test AI provider connection
     */
    #[Route('/api/test-provider', name: 'api_test_provider', methods: ['POST'])]
    public function testProvider(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $provider = $data['provider'] ?? $this->chatbotService->getCurrentProvider();

        try {
            // Test with a simple message
            $testMessage = "Hello, this is a test message.";
            $response = $this->chatbotService->processMessage($testMessage, 'en', []);

            if ($response['success']) {
                return new JsonResponse([
                    'success' => true,
                    'data' => [
                        'provider' => $provider,
                        'status' => 'working',
                        'test_response' => substr($response['response'], 0, 100) . '...',
                        'response_time' => $response['response_time'] ?? null
                    ]
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'error' => [
                        'message' => 'Provider test failed',
                        'code' => 'PROVIDER_TEST_FAILED',
                        'details' => $response['error'] ?? 'Unknown error'
                    ]
                ]);
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => 'Provider test exception',
                    'code' => 'PROVIDER_TEST_EXCEPTION',
                    'details' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get AI usage statistics
     */
    #[Route('/api/stats', name: 'api_stats', methods: ['GET'])]
    public function getStats(): JsonResponse
    {
        // This would typically come from a database or cache
        // For now, return mock data
        return new JsonResponse([
            'success' => true,
            'data' => [
                'total_conversations' => 0, // Would be from DB
                'total_messages' => 0, // Would be from DB
                'current_provider' => $this->chatbotService->getCurrentProvider(),
                'provider_uptime' => [
                    'openai' => '99.9%',
                    'deepseek' => '99.5%'
                ],
                'last_24h' => [
                    'conversations' => 0,
                    'messages' => 0,
                    'avg_response_time' => '0.5s'
                ]
            ]
        ]);
    }
}
