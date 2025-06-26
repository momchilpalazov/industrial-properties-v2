<?php

namespace App\Controller\Api;

use App\Service\AiChatbotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/chatbot', name: 'api_chatbot_')]
class ChatbotController extends AbstractController
{
    public function __construct(
        private AiChatbotService $chatbotService
    ) {}

    /**
     * Send message to AI chatbot
     */
    #[Route('/message', name: 'message', methods: ['POST'])]
    public function message(Request $request): JsonResponse
    {
        // Check if chatbot is configured
        if (!$this->chatbotService->isConfigured()) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => 'Chatbot service is not configured',
                    'code' => 'SERVICE_NOT_CONFIGURED'
                ]
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        // Get request data
        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['message'])) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => 'Message is required',
                    'code' => 'MISSING_MESSAGE'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        $message = $this->chatbotService->sanitizeInput($data['message']);
        $locale = $data['locale'] ?? 'en';
        $conversationId = $data['conversation_id'] ?? null;

        // Validate message length
        if (empty($message) || strlen($message) < 2) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => 'Message too short',
                    'code' => 'MESSAGE_TOO_SHORT'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Prepare context
            $context = [
                'conversation_id' => $conversationId,
                'conversation_history' => $data['conversation_history'] ?? [],
                'user_preferences' => $data['user_preferences'] ?? []
            ];

            // Process message through AI
            $response = $this->chatbotService->processMessage($message, $locale, $context);

            return new JsonResponse([
                'success' => $response['success'],
                'data' => $response['success'] ? [
                    'response' => $response['response'],
                    'conversation_id' => $response['conversation_id'],
                    'suggestions' => $response['suggestions'],
                    'properties_context' => $response['properties_context'],
                    'locale' => $response['locale']
                ] : null,
                'error' => !$response['success'] ? $response['error'] : null,
                'fallback_response' => $response['fallback_response'] ?? null,
                'meta' => [
                    'timestamp' => date('c'),
                    'model_used' => 'gpt-3.5-turbo',
                    'tokens_used' => $response['usage'] ?? null,
                    'ai_enhanced' => true
                ]
            ], $response['success'] ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => 'Chatbot service error',
                    'code' => 'CHATBOT_ERROR',
                    'details' => $this->getParameter('kernel.debug') ? $e->getMessage() : null
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get chatbot configuration and capabilities
     */
    #[Route('/config', name: 'config', methods: ['GET'])]
    public function config(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data' => [
                'is_available' => $this->chatbotService->isConfigured(),
                'supported_languages' => ['bg', 'en', 'de', 'ru'],
                'capabilities' => [
                    'property_search' => true,
                    'recommendations' => true,
                    'multilingual_support' => true,
                    'conversation_context' => true,
                    'property_details' => true,
                    'location_search' => true,
                    'price_filtering' => true
                ],
                'limitations' => [
                    'max_message_length' => 500,
                    'conversation_timeout' => '30 minutes',
                    'rate_limit' => '100 messages per hour',
                    'property_context_limit' => 5
                ],
                'example_queries' => [
                    'bg' => [
                        'Търся склад в София до 200,000 евро',
                        'Имате ли офис сгради в Пловдив?',
                        'Покажи ми най-новите индустриални имоти'
                    ],
                    'en' => [
                        'Looking for warehouse in Sofia under 200,000 EUR',
                        'Do you have office buildings in Plovdiv?',
                        'Show me the newest industrial properties'
                    ],
                    'de' => [
                        'Suche Lager in Sofia unter 200.000 EUR',
                        'Haben Sie Bürogebäude in Plovdiv?',
                        'Zeigen Sie mir die neuesten Industrieimmobilien'
                    ],
                    'ru' => [
                        'Ищу склад в Софии до 200,000 евро',
                        'У вас есть офисные здания в Пловдиве?',
                        'Покажите новейшие промышленные объекты'
                    ]
                ]
            ],
            'meta' => [
                'version' => '1.0',
                'powered_by' => 'OpenAI GPT-3.5-turbo',
                'integration_date' => date('c')
            ]
        ]);
    }

    /**
     * Get conversation suggestions based on context
     */
    #[Route('/suggestions', name: 'suggestions', methods: ['GET'])]
    public function suggestions(Request $request): JsonResponse
    {
        $locale = $request->query->get('locale', 'en');
        $context = $request->query->get('context', 'general');

        $suggestions = $this->getContextualSuggestions($context, $locale);

        return new JsonResponse([
            'success' => true,
            'data' => [
                'suggestions' => $suggestions,
                'context' => $context,
                'locale' => $locale
            ]
        ]);
    }

    /**
     * Health check for chatbot service
     */
    #[Route('/health', name: 'health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        $isConfigured = $this->chatbotService->isConfigured();
        
        $response = new JsonResponse([
            'success' => true,
            'data' => [
                'status' => $isConfigured ? 'healthy' : 'not_configured',
                'is_configured' => $isConfigured,
                'last_check' => date('c'),
                'dependencies' => [
                    'openai_client' => class_exists('OpenAI\Client'),
                    'ai_data_service' => true,
                    'api_key_present' => $isConfigured
                ]
            ]
        ], $isConfigured ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE);
        
        // Add CORS headers
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
        
        return $response;
    }

    /**
     * Start a new conversation session
     */
    #[Route('/conversation/start', name: 'conversation_start', methods: ['POST'])]
    public function startConversation(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $locale = $data['locale'] ?? 'en';
        $userPreferences = $data['preferences'] ?? [];

        $conversationId = uniqid('chat_' . time() . '_');
        
        $welcomeMessage = $this->getWelcomeMessage($locale);

        return new JsonResponse([
            'success' => true,
            'data' => [
                'conversation_id' => $conversationId,
                'welcome_message' => $welcomeMessage,
                'locale' => $locale,
                'initial_suggestions' => $this->getContextualSuggestions('welcome', $locale),
                'user_preferences' => $userPreferences
            ]
        ]);
    }

    /**
     * Get available AI providers
     */
    #[Route('/providers', name: 'providers', methods: ['GET'])]
    public function providers(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data' => [
                'current_provider' => $this->chatbotService->getCurrentProvider(),
                'available_providers' => $this->chatbotService->getAvailableProviders()
            ]
        ]);
    }

    /**
     * Set AI provider
     */
    #[Route('/provider', name: 'set_provider', methods: ['POST'])]
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

        $success = $this->chatbotService->setProvider($data['provider']);
        
        if (!$success) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => 'Invalid or unavailable provider',
                    'code' => 'INVALID_PROVIDER'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'success' => true,
            'data' => [
                'provider' => $this->chatbotService->getCurrentProvider(),
                'message' => 'Provider switched successfully'
            ]
        ]);
    }

    /**
     * Get contextual suggestions
     */
    private function getContextualSuggestions(string $context, string $locale): array
    {
        $suggestions = match($context) {
            'welcome' => match($locale) {
                'bg' => [
                    'Търся склад в София',
                    'Покажи ми офис сгради',
                    'Какви индустриални имоти имате?',
                    'Цени на складове в Пловдив'
                ],
                'de' => [
                    'Lager in Sofia suchen',
                    'Bürogebäude zeigen',
                    'Welche Industrieimmobilien haben Sie?',
                    'Lagerpreise in Plovdiv'
                ],
                'ru' => [
                    'Ищу склад в Софии',
                    'Покажите офисные здания',
                    'Какая у вас промышленная недвижимость?',
                    'Цены на склады в Пловдиве'
                ],
                default => [
                    'Looking for warehouse in Sofia',
                    'Show me office buildings',
                    'What industrial properties do you have?',
                    'Warehouse prices in Plovdiv'
                ]
            },
            'property_search' => match($locale) {
                'bg' => [
                    'Покажи ми повече детайли',
                    'Има ли подобни имоти?',
                    'Как мога да организирам оглед?',
                    'Какви са условията за наем?'
                ],
                'de' => [
                    'Mehr Details zeigen',
                    'Gibt es ähnliche Objekte?',
                    'Besichtigung vereinbaren',
                    'Mietbedingungen'
                ],
                'ru' => [
                    'Показать больше деталей',
                    'Есть ли похожие объекты?',
                    'Как организовать просмотр?',
                    'Условия аренды'
                ],
                default => [
                    'Show me more details',
                    'Are there similar properties?',
                    'How to schedule viewing?',
                    'Rental terms'
                ]
            },
            default => match($locale) {
                'bg' => ['Помощ с търсене', 'Контакти', 'Нови имоти'],
                'de' => ['Suchhilfe', 'Kontakt', 'Neue Immobilien'],
                'ru' => ['Помощь с поиском', 'Контакты', 'Новые объекты'],
                default => ['Search help', 'Contact us', 'New properties']
            }
        };

        return $suggestions;
    }

    /**
     * Get welcome message
     */
    private function getWelcomeMessage(string $locale): string
    {
        return match($locale) {
            'bg' => 'Здравейте! Аз съм вашият AI асистент за индустриални имоти. Как мога да ви помогна днес?',
            'de' => 'Hallo! Ich bin Ihr KI-Assistent für Industrieimmobilien. Wie kann ich Ihnen heute helfen?',
            'ru' => 'Привет! Я ваш ИИ-помощник по промышленной недвижимости. Как я могу помочь вам сегодня?',
            default => 'Hello! I\'m your AI assistant for industrial properties. How can I help you today?'
        };
    }
}
