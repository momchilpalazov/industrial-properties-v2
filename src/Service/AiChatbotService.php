<?php

namespace App\Service;

use App\Service\AiDataService;
use OpenAI\Client;
use OpenAI\Factory;
use Symfony\Component\HttpFoundation\Request;

class AiChatbotService
{
    private const MAX_CONTEXT_PROPERTIES = 5;
    private const MAX_TOKENS = 1000;
    private const MODEL = 'gpt-3.5-turbo';

    private ?Client $openAi = null;

    public function __construct(
        private AiDataService $aiDataService,
        private string $openAiApiKey
    ) {
        // Only initialize if API key is provided and not placeholder
        if ($this->isConfigured()) {
            try {
                $factory = new Factory();
                $this->openAi = $factory->withApiKey($this->openAiApiKey)->make();
            } catch (\Exception $e) {
                // Log error but continue - service will work in fallback mode
                error_log("OpenAI client initialization failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Process chat message and return AI response
     */
    public function processMessage(string $message, string $locale = 'en', array $context = []): array
    {
        try {
            // Get relevant properties based on the message
            $relevantProperties = $this->findRelevantProperties($message, $locale);
            
            // Build system prompt with property context
            $systemPrompt = $this->buildSystemPrompt($locale, $relevantProperties);
            
            // Build conversation messages
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $message]
            ];

            // Add conversation history if provided
            if (!empty($context['conversation_history'])) {
                array_splice($messages, -1, 0, $context['conversation_history']);
            }

            // Call OpenAI API
            $response = $this->openAi->chat()->create([
                'model' => self::MODEL,
                'messages' => $messages,
                'max_tokens' => self::MAX_TOKENS,
                'temperature' => 0.7,
                'top_p' => 0.9,
                'frequency_penalty' => 0.0,
                'presence_penalty' => 0.6,
            ]);

            $aiResponse = $response->choices[0]->message->content;

            return [
                'success' => true,
                'response' => $aiResponse,
                'properties_context' => $relevantProperties,
                'conversation_id' => $context['conversation_id'] ?? uniqid('chat_'),
                'locale' => $locale,
                'usage' => [
                    'prompt_tokens' => $response->usage->promptTokens,
                    'completion_tokens' => $response->usage->completionTokens,
                    'total_tokens' => $response->usage->totalTokens,
                ],
                'suggestions' => $this->generateFollowUpSuggestions($message, $locale),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => [
                    'message' => 'AI service temporarily unavailable',
                    'code' => 'AI_SERVICE_ERROR',
                    'details' => $e->getMessage()
                ],
                'fallback_response' => $this->getFallbackResponse($message, $locale)
            ];
        }
    }

    /**
     * Find properties relevant to the user's message
     */
    private function findRelevantProperties(string $message, string $locale): array
    {
        // Parse the message to extract search criteria
        $criteria = $this->parseMessageToCriteria($message);
        
        // Search for relevant properties
        $searchResults = $this->aiDataService->searchPropertiesForAi($criteria, $locale);
        
        // Limit to most relevant properties
        return array_slice($searchResults['results'], 0, self::MAX_CONTEXT_PROPERTIES);
    }

    /**
     * Build system prompt with current property context
     */
    private function buildSystemPrompt(string $locale, array $properties): string
    {
        $languageInstructions = $this->getLanguageInstructions($locale);
        
        $systemPrompt = "You are an AI assistant for Industrial Properties Europe, a leading platform for industrial real estate in Bulgaria and the Balkans.

{$languageInstructions}

Your role:
- Help users find industrial properties (warehouses, manufacturing facilities, office buildings, logistics centers)
- Provide property recommendations based on user needs
- Answer questions about locations, prices, and property features
- Be helpful, professional, and knowledgeable about industrial real estate

IMPORTANT GUIDELINES:
- Always respond in {$locale} language
- Be specific about property details when available
- If you don't have exact information, guide users to contact the company
- Suggest relevant properties from the current database
- Keep responses concise but informative (max 150 words)
- Include property IDs when recommending specific properties

";

        if (!empty($properties)) {
            $systemPrompt .= "CURRENT AVAILABLE PROPERTIES:\n";
            foreach ($properties as $property) {
                $systemPrompt .= $this->formatPropertyForPrompt($property, $locale);
            }
            $systemPrompt .= "\n";
        }

        $systemPrompt .= "COMPANY INFO:
- Website: Industrial Properties Europe
- Specialization: Industrial real estate in Bulgaria
- Languages: Bulgarian, English, German, Russian
- Contact for detailed inquiries and property visits

Always end responses with a helpful question or suggestion for next steps.";

        return $systemPrompt;
    }

    /**
     * Get language-specific instructions
     */
    private function getLanguageInstructions(string $locale): string
    {
        return match($locale) {
            'bg' => 'Отговаряй на български език. Бъди професионален и полезен.',
            'de' => 'Antworte auf Deutsch. Sei professionell und hilfsbereit.',
            'ru' => 'Отвечай на русском языке. Будь профессиональным и полезным.',
            default => 'Respond in English. Be professional and helpful.'
        };
    }

    /**
     * Format property information for AI prompt
     */
    private function formatPropertyForPrompt(array $property, string $locale): string
    {
        $title = $property['content']['title'] ?? 'N/A';
        $location = $property['content']['location'] ?? 'N/A';
        $type = $property['characteristics']['type'] ?? 'N/A';
        $area = $property['characteristics']['area'] ?? 'N/A';
        $price = $property['characteristics']['price'] ?? 'N/A';
        $id = $property['id'] ?? 'N/A';

        return "Property ID {$id}: {$title} in {$location}
Type: {$type}, Area: {$area} m², Price: {$price} EUR
URL: {$property['url']}

";
    }

    /**
     * Parse user message to search criteria
     */
    private function parseMessageToCriteria(string $message): array
    {
        $criteria = [];
        $messageLower = strtolower($message);

        // Location detection
        $locations = ['sofia' => 'софия', 'plovdiv' => 'пловдив', 'varna' => 'варна', 'burgas' => 'бургас'];
        foreach ($locations as $en => $bg) {
            if (str_contains($messageLower, $en) || str_contains($messageLower, $bg)) {
                $criteria['location'] = $en;
                break;
            }
        }

        // Property type detection
        $types = [
            'warehouse' => ['warehouse', 'склад', 'storage', 'lager'],
            'office' => ['office', 'офис', 'büro'],
            'industrial' => ['industrial', 'индустриален', 'manufacturing', 'производство', 'industrie'],
            'logistics' => ['logistics', 'логистичен', 'distribution', 'дистрибуция', 'logistik']
        ];

        foreach ($types as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($messageLower, $keyword)) {
                    $criteria['type'] = $type;
                    break 2;
                }
            }
        }

        // Price detection
        if (preg_match('/(\d+)k?[\s]*[-до][\s]*(\d+)k?/', $message, $matches)) {
            $criteria['min_price'] = (int) $matches[1] * (str_contains($matches[1], 'k') ? 1000 : 1);
            $criteria['max_price'] = (int) $matches[2] * (str_contains($matches[2], 'k') ? 1000 : 1);
        } elseif (preg_match('/под[\s]*(\d+)k?|under[\s]*(\d+)k?|max[\s]*(\d+)k?/', $messageLower, $matches)) {
            $price = (int) ($matches[1] ?? $matches[2] ?? $matches[3]);
            $criteria['max_price'] = $price * (str_contains($matches[0], 'k') ? 1000 : 1);
        }

        // Area detection
        if (preg_match('/(\d+)[\s]*[-до][\s]*(\d+)[\s]*(m²|кв|sqm)/', $messageLower, $matches)) {
            $criteria['min_area'] = (int) $matches[1];
            $criteria['max_area'] = (int) $matches[2];
        }

        return $criteria;
    }

    /**
     * Generate follow-up suggestions
     */
    private function generateFollowUpSuggestions(string $message, string $locale): array
    {
        $suggestions = match($locale) {
            'bg' => [
                'Покажи ми повече детайли за този имот',
                'Има ли подобни имоти в други градове?',
                'Как мога да организирам оглед?',
                'Какви са възможностите за финансиране?'
            ],
            'de' => [
                'Zeigen Sie mir mehr Details zu dieser Immobilie',
                'Gibt es ähnliche Objekte in anderen Städten?',
                'Wie kann ich eine Besichtigung organisieren?',
                'Welche Finanzierungsmöglichkeiten gibt es?'
            ],
            'ru' => [
                'Покажите больше деталей об этой недвижимости',
                'Есть ли похожие объекты в других городах?',
                'Как организовать просмотр?',
                'Какие возможности финансирования?'
            ],
            default => [
                'Show me more details about this property',
                'Are there similar properties in other cities?',
                'How can I schedule a viewing?',
                'What financing options are available?'
            ]
        };

        // Return 2-3 random suggestions
        shuffle($suggestions);
        return array_slice($suggestions, 0, 3);
    }

    /**
     * Get fallback response when AI is unavailable
     */
    private function getFallbackResponse(string $message, string $locale): string
    {
        return match($locale) {
            'bg' => 'Съжалявам, в момента имам технически проблеми. Моля, свържете се директно с нашия екип за помощ при търсенето на индустриални имоти.',
            'de' => 'Entschuldigung, ich habe momentan technische Probleme. Bitte wenden Sie sich direkt an unser Team für Hilfe bei der Suche nach Industrieimmobilien.',
            'ru' => 'Извините, у меня сейчас технические проблемы. Пожалуйста, обратитесь напрямую к нашей команде за помощью в поиске промышленной недвижимости.',
            default => 'Sorry, I\'m experiencing technical difficulties. Please contact our team directly for assistance with finding industrial properties.'
        };
    }

    /**
     * Generate conversation summary for context
     */
    public function generateConversationSummary(array $conversationHistory): string
    {
        if (empty($conversationHistory)) {
            return '';
        }

        // Extract key points from conversation
        $userMessages = array_filter($conversationHistory, fn($msg) => $msg['role'] === 'user');
        $lastMessages = array_slice($userMessages, -3); // Last 3 user messages

        $context = "User conversation context:\n";
        foreach ($lastMessages as $message) {
            $context .= "- " . substr($message['content'], 0, 100) . "\n";
        }

        return $context;
    }

    /**
     * Validate and sanitize user input
     */
    public function sanitizeInput(string $input): string
    {
        // Remove potentially harmful content
        $cleaned = strip_tags($input);
        $cleaned = preg_replace('/[^\p{L}\p{N}\s\-_.,!?()€$]/u', '', $cleaned);
        
        // Limit length
        return substr($cleaned, 0, 500);
    }

    /**
     * Check if API key is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->openAiApiKey) && 
               $this->openAiApiKey !== 'your_openai_api_key_here' &&
               $this->openAiApiKey !== 'sk-your-real-openai-api-key-here';
    }
}
