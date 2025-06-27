<?php

namespace App\Service;

use App\Service\AiDataService;
use OpenAI\Client as OpenAIClient;
use OpenAI\Factory as OpenAIFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AiChatbotService
{
    private const MAX_CONTEXT_PROPERTIES = 5;
    private const MAX_TOKENS = 1000;
    
    // AI Provider constants
    private const PROVIDER_OPENAI = 'openai';
    private const PROVIDER_DEEPSEEK = 'deepseek';
    
    // Model configurations
    private const MODELS = [
        self::PROVIDER_OPENAI => [
            'model' => 'gpt-3.5-turbo',
            'endpoint' => 'https://api.openai.com/v1/chat/completions',
            'free' => false,
            'name' => 'OpenAI GPT-3.5 Turbo'
        ],
        self::PROVIDER_DEEPSEEK => [
            'model' => 'deepseek-chat',
            'endpoint' => 'https://api.deepseek.com/v1/chat/completions',
            'free' => true,
            'name' => 'DeepSeek Chat (Free)'
        ]
    ];

    private ?OpenAIClient $openAi = null;
    private string $currentProvider;

    public function __construct(
        private AiDataService $aiDataService,
        private string $openAiApiKey,
        private string $deepSeekApiKey,
        private HttpClientInterface $httpClient,
        private string $defaultProvider = self::PROVIDER_DEEPSEEK // Default to free option
    ) {
        $this->currentProvider = $this->defaultProvider;
        $this->initializeProviders();
    }

    private function initializeProviders(): void
    {
        // Initialize OpenAI if key is available
        if ($this->isProviderConfigured(self::PROVIDER_OPENAI)) {
            try {
                $factory = new OpenAIFactory();
                $this->openAi = $factory->withApiKey($this->openAiApiKey)->make();
            } catch (\Exception $e) {
                error_log("OpenAI client initialization failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Process chat message and return AI response
     */
    public function processMessage(string $message, string $locale = 'en', array $context = []): array
    {
        // Auto-select best available provider if current one is not configured
        if (!$this->isProviderConfigured($this->currentProvider)) {
            $this->currentProvider = $this->getBestAvailableProvider();
        }

        if (!$this->isProviderConfigured($this->currentProvider)) {
            return $this->getFallbackResponseArray($message, $locale);
        }

        try {
            // Get relevant properties based on the message
            error_log("Processing message: $message");
            
            $relevantProperties = $this->findRelevantProperties($message, $locale);
            error_log("Found relevant properties: " . count($relevantProperties));
            
            // Even if no exact matches found, we should have some properties from the fallback
            // If somehow we still have no properties, log this but continue with empty properties
            if (empty($relevantProperties)) {
                error_log("WARNING: No properties available even after fallback");
            }
            
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

            // Call AI API based on provider
            $response = $this->callAIProvider($messages);

            return [
                'success' => true,
                'response' => $response['content'],
                'properties_context' => $relevantProperties,
                'conversation_id' => $context['conversation_id'] ?? uniqid('chat_'),
                'locale' => $locale,
                'usage' => $response['usage'] ?? null,
                'suggestions' => $this->generateFollowUpSuggestions($message, $locale),
                'provider' => $this->currentProvider,
                'model' => self::MODELS[$this->currentProvider]['name']
            ];

        } catch (\Exception $e) {
            error_log("Error in processMessage: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            // Always provide a helpful response even when there's an error
            $errorMessage = $e->getMessage();
            $isApiError = strpos($errorMessage, 'API') !== false || 
                          strpos($errorMessage, '401') !== false || 
                          strpos($errorMessage, '403') !== false || 
                          strpos($errorMessage, 'auth') !== false;
            
            return [
                'success' => false,
                'error' => [
                    'message' => $isApiError ? 'AI service temporarily unavailable' : 'Failed to process your query',
                    'code' => $isApiError ? 'AI_SERVICE_ERROR' : 'QUERY_PROCESSING_ERROR',
                    'details' => $e->getMessage()
                ],
                'fallback_response' => $this->getFallbackResponse($message, $locale),
                'provider' => $this->currentProvider
            ];
        }
    }

    /**
     * Call AI provider based on current selection
     */
    private function callAIProvider(array $messages): array
    {
        switch ($this->currentProvider) {
            case self::PROVIDER_OPENAI:
                return $this->callOpenAI($messages);
            case self::PROVIDER_DEEPSEEK:
                return $this->callDeepSeek($messages);
            default:
                throw new \Exception('Invalid AI provider: ' . $this->currentProvider);
        }
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAI(array $messages): array
    {
        if (!$this->openAi) {
            throw new \Exception('OpenAI client not initialized');
        }

        $response = $this->openAi->chat()->create([
            'model' => self::MODELS[self::PROVIDER_OPENAI]['model'],
            'messages' => $messages,
            'max_tokens' => self::MAX_TOKENS,
            'temperature' => 0.7,
            'top_p' => 0.9,
            'frequency_penalty' => 0.0,
            'presence_penalty' => 0.6,
        ]);

        return [
            'content' => $response->choices[0]->message->content,
            'usage' => [
                'prompt_tokens' => $response->usage->promptTokens,
                'completion_tokens' => $response->usage->completionTokens,
                'total_tokens' => $response->usage->totalTokens,
            ]
        ];
    }

    /**
     * Call DeepSeek API
     */
    private function callDeepSeek(array $messages): array
    {
        $response = $this->httpClient->request('POST', self::MODELS[self::PROVIDER_DEEPSEEK]['endpoint'], [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->deepSeekApiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => self::MODELS[self::PROVIDER_DEEPSEEK]['model'],
                'messages' => $messages,
                'max_tokens' => self::MAX_TOKENS,
                'temperature' => 0.7,
                'stream' => false
            ]
        ]);

        $data = $response->toArray();

        if (!isset($data['choices'][0]['message']['content'])) {
            throw new \Exception('Invalid DeepSeek API response');
        }

        return [
            'content' => $data['choices'][0]['message']['content'],
            'usage' => $data['usage'] ?? null
        ];
    }

    /**
     * Set AI provider
     */
    public function setProvider(string $provider): bool
    {
        if (!array_key_exists($provider, self::MODELS)) {
            return false;
        }

        if (!$this->isProviderConfigured($provider)) {
            return false;
        }

        $this->currentProvider = $provider;
        return true;
    }

    /**
     * Get current provider
     */
    public function getCurrentProvider(): string
    {
        return $this->currentProvider;
    }

    /**
     * Get available providers
     */
    public function getAvailableProviders(): array
    {
        $providers = [];
        foreach (self::MODELS as $key => $config) {
            $providers[$key] = [
                'name' => $config['name'],
                'free' => $config['free'],
                'available' => $this->isProviderConfigured($key),
                'current' => $key === $this->currentProvider
            ];
        }
        return $providers;
    }

    /**
     * Check if provider is configured
     */
    private function isProviderConfigured(string $provider): bool
    {
        switch ($provider) {
            case self::PROVIDER_OPENAI:
                return !empty($this->openAiApiKey) && 
                       $this->openAiApiKey !== 'your_openai_api_key_here' &&
                       $this->openAiApiKey !== 'sk-your-real-openai-api-key-here';
            case self::PROVIDER_DEEPSEEK:
                return !empty($this->deepSeekApiKey) && 
                       $this->deepSeekApiKey !== 'your_deepseek_api_key_here';
            default:
                return false;
        }
    }

    /**
     * Get best available provider (prefers free options)
     */
    private function getBestAvailableProvider(): string
    {
        // Prefer free providers first
        foreach (self::MODELS as $provider => $config) {
            if ($config['free'] && $this->isProviderConfigured($provider)) {
                return $provider;
            }
        }

        // Fallback to any available provider
        foreach (self::MODELS as $provider => $config) {
            if ($this->isProviderConfigured($provider)) {
                return $provider;
            }
        }

        return self::PROVIDER_DEEPSEEK; // Default fallback
    }

    /**
     * Check if any AI provider is configured
     */
    public function isConfigured(): bool
    {
        foreach (array_keys(self::MODELS) as $provider) {
            if ($this->isProviderConfigured($provider)) {
                return true;
            }
        }
        return false;
    }

    private function getFallbackResponseArray(string $message, string $locale): array
    {
        return [
            'success' => false,
            'error' => [
                'message' => 'No AI providers configured',
                'code' => 'NO_PROVIDERS_AVAILABLE'
            ],
            'fallback_response' => $this->getFallbackResponse($message, $locale),
            'provider' => 'none'
        ];
    }

    /**
     * Find properties relevant to the user's message
     */
    private function findRelevantProperties(string $message, string $locale): array
    {
        try {
            // Parse the message to extract search criteria
            $criteria = $this->parseMessageToCriteria($message);
            
            // Debug logging
            error_log("Search criteria parsed: " . json_encode($criteria, JSON_UNESCAPED_UNICODE));
            
            // Search for relevant properties with the original criteria
            // Don't modify the location array - this is now properly handled in AiDataService
            $searchResults = $this->aiDataService->searchPropertiesForAi($criteria, $locale);
            
            // Debug logging
            error_log("Search results count: " . (isset($searchResults['results']) ? count($searchResults['results']) : 0));
            
            // If no results, try broader search
            if (empty($searchResults['results'])) {
                // Try with location only
                if (!empty($criteria['location'])) {
                    $locationOnlyCriteria = ['location' => $criteria['location']];
                    error_log("Trying location-only search with: " . json_encode($locationOnlyCriteria, JSON_UNESCAPED_UNICODE));
                    $locationResults = $this->aiDataService->searchPropertiesForAi($locationOnlyCriteria, $locale);
                    
                    if (!empty($locationResults['results'])) {
                        error_log("Location-only search found " . count($locationResults['results']) . " results");
                        return array_slice($locationResults['results'], 0, self::MAX_CONTEXT_PROPERTIES);
                    }
                }
                
                // Try with type only
                if (!empty($criteria['type'])) {
                    $typeOnlyCriteria = ['type' => $criteria['type']];
                    error_log("Trying type-only search with: " . json_encode($typeOnlyCriteria, JSON_UNESCAPED_UNICODE));
                    $typeResults = $this->aiDataService->searchPropertiesForAi($typeOnlyCriteria, $locale);
                    
                    if (!empty($typeResults['results'])) {
                        error_log("Type-only search found " . count($typeResults['results']) . " results");
                        return array_slice($typeResults['results'], 0, self::MAX_CONTEXT_PROPERTIES);
                    }
                }
                
                // Get all properties as fallback
                $allProperties = $this->aiDataService->getAllPropertiesForAi(1, self::MAX_CONTEXT_PROPERTIES, $locale);
                error_log("No results found, returning all properties: " . count($allProperties['properties']));
                return $allProperties['properties'];
            }
            
            // Limit to most relevant properties
            return array_slice($searchResults['results'], 0, self::MAX_CONTEXT_PROPERTIES);
        } catch (\Exception $e) {
            error_log("Error in findRelevantProperties: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            // Return empty array on error to avoid breaking the chat
            return [];
        }
    }

    /**
     * Build system prompt with current property context
     */
    private function buildSystemPrompt(string $locale, array $properties): string
    {
        $languageInstructions = $this->getLanguageInstructions($locale);
        
        $systemPrompt = "Вие сте AI асистент за Industrial Properties Europe, водещата платформа за индустриални имоти в България и Балканите.

{$languageInstructions}

Вашата роля:
- Помагате на потребителите да намерят индустриални имоти (складове, производствени помещения, офис сгради, логистични центрове)
- Предоставяте препоръки за имоти според нуждите на потребителя
- Отговаряте на въпроси за локации, цени и характеристики на имотите
- Бъдете полезен, професионален и знаещ в областта на индустриалните недвижими имоти

ВАЖНИ УКАЗАНИЯ:
- Винаги отговаряйте на {$locale} език
- Бъдете конкретен относно детайлите на имотите когато са налични
- Ако нямате точна информация, насочете потребителите да се свържат с компанията
- Препоръчвайте подходящи имоти от текущата база данни
- Ако няма точно това което се търси, ясно обяснете това и ЗАДЪЛЖИТЕЛНО предложете алтернативи:
  * Ако се търси определен тип имот в даден град, но го няма - предложете друг тип имот в същия град
  * Ако се търси имот в определен град, но го няма - предложете подобен имот в друг град
  * НИКОГА не казвайте че има технически проблем, ако просто няма точно съвпадение
- Пазете отговорите кратки но информативни (максимум 150 думи)
- Включвайте ID номерата на имотите когато препоръчвате конкретни такива
- Винаги бъдете честни за наличността - ако няма точно това което се търси, кажете го ясно

";

        if (!empty($properties)) {
            $systemPrompt .= "НАЛИЧНИ ИМОТИ В МОМЕНТА:\n";
            foreach ($properties as $property) {
                $systemPrompt .= $this->formatPropertyForPrompt($property, $locale);
            }
            $systemPrompt .= "\n";
        } else {
            $systemPrompt .= "ВАЖНО: В момента не са намерени имоти в базата данни. Обяснете това на потребителя и предложете да се свърже с екипа за повече възможности.\n\n";
        }

        $systemPrompt .= "ИНФОРМАЦИЯ ЗА КОМПАНИЯТА:
- Уебсайт: Industrial Properties Europe  
- Специализация: Индустриални недвижими имоти в България
- Езици: български, английски, немски, руски
- Свържете се за детайлни запитвания и огледи на имоти

Винаги завършвайте отговорите с полезен въпрос или предложение за следващи стъпки.";

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
        $status = $property['characteristics']['status'] ?? 'N/A';
        $id = $property['id'] ?? 'N/A';
        $url = $property['url'] ?? '#';

        // Format price nicely
        $priceFormatted = is_numeric($price) ? number_format($price, 0, '', ' ') . ' EUR' : $price;
        
        // Format area nicely
        $areaFormatted = is_numeric($area) ? number_format($area, 0, '', ' ') . ' m²' : $area;

        return "Имот ID {$id}: {$title}
Локация: {$location}
Тип: {$type} | Площ: {$areaFormatted} | Цена: {$priceFormatted}
Статус: {$status}
Линк: {$url}

";
    }

    /**
     * Parse user message to search criteria
     */
    private function parseMessageToCriteria(string $message): array
    {
        $criteria = [];
        $messageLower = mb_strtolower($message, 'UTF-8');

        // Location detection - support both English and Bulgarian
        $locations = [
            'софия' => ['София', 'Sofia', 'sofia'],
            'пловдив' => ['Пловдив', 'Plovdiv', 'plovdiv'], 
            'варна' => ['Варна', 'Varna', 'varna'],
            'бургас' => ['Бургас', 'Burgas', 'burgas'],
            'русе' => ['Русе', 'Ruse', 'ruse'],
            'хасково' => ['Хасково', 'Haskovo', 'haskovo'],
            'севлиево' => ['Севлиево', 'Sevlievo', 'sevlievo'],
            'благоевград' => ['Благоевград', 'Blagoevgrad', 'blagoevgrad']
        ];
        
        foreach ($locations as $bgKey => $variants) {
            foreach ($variants as $variant) {
                if (mb_strpos($messageLower, mb_strtolower($variant, 'UTF-8')) !== false) {
                    $criteria['location'] = $variants; // Pass all variants for searching
                    error_log("Location found in message: " . json_encode($variants, JSON_UNESCAPED_UNICODE));
                    break 2;
                }
            }
        }

        // Property type detection - improved Bulgarian support
        $types = [
            'warehouse' => ['warehouse', 'склад', 'складово', 'storage', 'lager', 'складов', 'складове', 'складови', 'склада'],
            'office' => ['office', 'офис', 'büro', 'офиси', 'офисов', 'офисен', 'офисна', 'офисни'],
            'industrial' => ['industrial', 'индустриален', 'manufacturing', 'производство', 'industrie', 'промишлен', 'фабрика', 'индустриални'],
            'logistics' => ['logistics', 'логистичен', 'distribution', 'дистрибуция', 'logistik', 'логистика', 'логистични', 'логистичен'],
            'business' => ['business', 'бизнес', 'търговски', 'commercial', 'kommerziell']
        ];

        foreach ($types as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_strpos($messageLower, mb_strtolower($keyword, 'UTF-8')) !== false) {
                    $criteria['type'] = $type;
                    error_log("Type found in message: $type (keyword: $keyword)");
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

        error_log("Final criteria: " . json_encode($criteria, JSON_UNESCAPED_UNICODE));
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
            'de' => 'Entschuldigung, ich habe momentan technische Probleme. Bitte wenden Sie sich direkt an unser Team за Hilfe bei der Suche nach Industrieimmobilien.',
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
}
