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
        try {
            error_log("Processing message: " . $message);
            
            // Sanitize input
            $message = $this->sanitizeInput($message);
            
            // Find relevant properties first
            $relevantProperties = $this->findRelevantProperties($message, $locale);
            error_log("Found relevant properties: " . count($relevantProperties));
            
            // Build system prompt with property context
            $systemPrompt = $this->buildSystemPrompt($locale, $relevantProperties);
            
            // Build conversation messages
            $messages = [
                [
                    'role' => 'system',
                    'content' => $systemPrompt
                ],
                [
                    'role' => 'user', 
                    'content' => $message
                ]
            ];
            
            // Add conversation context if provided
            if (!empty($context['conversation_history'])) {
                array_splice($messages, -1, 0, $context['conversation_history']);
            }
            
            // Call AI provider
            $aiResponse = $this->callAIProvider($messages);
            
            if (!$aiResponse['success']) {
                error_log("AI Provider failed: " . ($aiResponse['error'] ?? 'Unknown error'));
                return [
                    'success' => false,
                    'error' => $aiResponse['error'] ?? 'AI service temporarily unavailable',
                    'fallback_response' => $this->getFallbackResponse($message, $locale),
                    'provider' => $aiResponse['provider'] ?? 'none'
                ];
            }
            
            // Generate follow-up suggestions
            $suggestions = $this->generateFollowUpSuggestions($message, $locale);
            
            return [
                'success' => true,
                'response' => $aiResponse['response'],
                'suggestions' => $suggestions,
                'properties_found' => count($relevantProperties),
                'provider' => $aiResponse['provider'],
                'tokens_used' => $aiResponse['tokens_used'] ?? 0
            ];
            
        } catch (\Exception $e) {
            error_log("Error in processMessage: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            return [
                'success' => false,
                'error' => 'An unexpected error occurred: ' . $e->getMessage(),
                'fallback_response' => $this->getFallbackResponse($message, $locale),
                'provider' => 'error'
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
        try {
            // DeepSeek API endpoint
            $url = 'https://api.deepseek.com/v1/chat/completions';
            
            // Configure for DeepSeek R1 best practices
            $requestData = [
                'model' => 'deepseek-chat', // Use the chat model for better reasoning
                'messages' => $messages,
                'temperature' => 0.7, // Optimal for reasoning tasks
                'max_tokens' => 1000,
                'top_p' => 0.95, // Good balance for creativity and coherence
                'top_k' => 40, // Help with token selection
                'frequency_penalty' => 0.1, // Reduce repetition
                'presence_penalty' => 0.1, // Encourage diverse vocabulary
                'stream' => false
            ];

            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->deepSeekApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $requestData,
                'timeout' => 30
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent();

            if ($statusCode !== 200) {
                error_log("DeepSeek API error: Status $statusCode, Content: $content");
                throw new \Exception("DeepSeek API request failed with status: $statusCode");
            }

            $data = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("DeepSeek JSON decode error: " . json_last_error_msg());
                throw new \Exception("Invalid JSON response from DeepSeek API");
            }

            if (!isset($data['choices'][0]['message']['content'])) {
                error_log("DeepSeek unexpected response structure: " . $content);
                throw new \Exception("Unexpected response structure from DeepSeek API");
            }

            $responseText = $data['choices'][0]['message']['content'];
            
            // Clean up any formatting artifacts that might come from DeepSeek
            $responseText = $this->cleanDeepSeekResponse($responseText);

            return [
                'success' => true,
                'response' => $responseText,
                'provider' => 'deepseek',
                'tokens_used' => $data['usage']['total_tokens'] ?? 0
            ];

        } catch (\Exception $e) {
            error_log("DeepSeek API error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => 'deepseek'
            ];
        }
    }

    /**
     * Clean DeepSeek response from potential formatting artifacts
     */
    private function cleanDeepSeekResponse(string $response): string
    {
        // Remove any thinking tags that might leak through
        $response = preg_replace('/<think>.*?<\/think>/s', '', $response);
        $response = preg_replace('/<thinking>.*?<\/thinking>/s', '', $response);
        
        // Clean up any double spacing or line breaks
        $response = preg_replace('/\n{3,}/', "\n\n", $response);
        $response = preg_replace('/[ \t]+/', ' ', $response);
        
        // Trim and return
        return trim($response);
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
            $searchResults = $this->aiDataService->searchPropertiesForAi($criteria, $locale);
            
            // Debug logging
            error_log("Search results count: " . (isset($searchResults['results']) ? count($searchResults['results']) : 0));
            
            // If we have results, return them
            if (!empty($searchResults['results'])) {
                error_log("Found " . count($searchResults['results']) . " results with full criteria");
                return array_slice($searchResults['results'], 0, self::MAX_CONTEXT_PROPERTIES);
            }

            // Progressive fallback strategy
            $fallbackStrategies = [];

            // Strategy 1: Try location + broader type matching
            if (!empty($criteria['location']) && !empty($criteria['type'])) {
                $broadTypeCriteria = ['location' => $criteria['location']];
                $fallbackStrategies[] = ['criteria' => $broadTypeCriteria, 'name' => 'location-only'];
            }

            // Strategy 2: Try type only with flexible area/price
            if (!empty($criteria['type'])) {
                $typeOnlyCriteria = ['type' => $criteria['type']];
                $fallbackStrategies[] = ['criteria' => $typeOnlyCriteria, 'name' => 'type-only'];
            }

            // Strategy 3: Try location only
            if (!empty($criteria['location'])) {
                $locationOnlyCriteria = ['location' => $criteria['location']];
                $fallbackStrategies[] = ['criteria' => $locationOnlyCriteria, 'name' => 'location-only'];
            }

            // Strategy 4: Try area/price only if specified
            if (!empty($criteria['min_area']) || !empty($criteria['max_area']) || 
                !empty($criteria['min_price']) || !empty($criteria['max_price'])) {
                $rangeCriteria = [];
                if (!empty($criteria['min_area'])) $rangeCriteria['min_area'] = $criteria['min_area'];
                if (!empty($criteria['max_area'])) $rangeCriteria['max_area'] = $criteria['max_area'];
                if (!empty($criteria['min_price'])) $rangeCriteria['min_price'] = $criteria['min_price'];
                if (!empty($criteria['max_price'])) $rangeCriteria['max_price'] = $criteria['max_price'];
                $fallbackStrategies[] = ['criteria' => $rangeCriteria, 'name' => 'range-only'];
            }

            // Try each fallback strategy
            foreach ($fallbackStrategies as $strategy) {
                error_log("Trying fallback strategy: " . $strategy['name'] . " with criteria: " . json_encode($strategy['criteria'], JSON_UNESCAPED_UNICODE));
                $fallbackResults = $this->aiDataService->searchPropertiesForAi($strategy['criteria'], $locale);
                
                if (!empty($fallbackResults['results'])) {
                    error_log("Fallback strategy '" . $strategy['name'] . "' found " . count($fallbackResults['results']) . " results");
                    return array_slice($fallbackResults['results'], 0, self::MAX_CONTEXT_PROPERTIES);
                }
            }
            
            // Last resort: Get all properties
            error_log("All fallback strategies failed, getting all properties");
            $allProperties = $this->aiDataService->getAllPropertiesForAi(1, self::MAX_CONTEXT_PROPERTIES, $locale);
            
            if (!empty($allProperties['properties'])) {
                error_log("Returning all properties: " . count($allProperties['properties']));
                return $allProperties['properties'];
            }

            // Absolutely no properties found
            error_log("No properties found in database at all");
            return [];
            
        } catch (\Exception $e) {
            error_log("Error in findRelevantProperties: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            // Emergency fallback: try to get any properties
            try {
                $emergency = $this->aiDataService->getAllPropertiesForAi(1, self::MAX_CONTEXT_PROPERTIES, $locale);
                return $emergency['properties'] ?? [];
            } catch (\Exception $emergencyException) {
                error_log("Emergency fallback also failed: " . $emergencyException->getMessage());
                return [];
            }
        }
    }

    /**
     * Build system prompt with current property context
     */
    private function buildSystemPrompt(string $locale, array $properties): string
    {
        $languageInstructions = $this->getLanguageInstructions($locale);
        
        $systemPrompt = "# Вашата роля
Вие сте AI асистент за Industrial Properties Europe - водещата платформа за индустриални имоти в България и Балканите.

# Инструкции за отговор
{$languageInstructions}

# Начин на мислене
<thinking>
- Анализирайте въпроса стъпка по стъпка
- Проверете наличните данни за имоти
- Формулирайте ясен и полезен отговор
- Предложете конкретни следващи стъпки
</thinking>

# ФОРМАТИРАНЕ НА ОТГОВОРА:
- Използвайте кратки, ясни параграфи
- Разделяйте различните секции с празен ред
- За списъци използвайте bullet points (•)
- Форматирайте цените като: 150 000 EUR
- Форматирайте площите като: 1 200 m²
- Използвайте **болд** за важна информация
- Подравнявайте текста логически

# Вашите основни задачи:
1. Помагате на потребителите да намерят подходящи индустриални имоти
2. Предоставяте точна информация за наличните имоти 
3. Обяснявате характеристиките и предимствата на различните типове имоти
4. Насочвате към най-подходящите възможности според нуждите

# Типове имоти, с които работим:
• Складови помещения
• Производствени сгради  
• Логистични центрове
• Офис сгради с индустриален профил
• Земи за развитие

# СТРУКТУРА НА ОТГОВОРА:
1. **Кратко резюме** на намерените възможности
2. **Детайлна информация** за всеки имот:
   - **Име/Тип:** [описание]
   - **Локация:** [град/район]
   - **Площ:** [число] m²
   - **Цена:** [число] EUR [/месец ако е наем]
   - **Статус:** [свободен/нает/в процес]
   - **Линк:** [URL за детайли]
3. **Допълнителни опции** ако е подходящо
4. **Следващи стъпки** или въпрос към потребителя

# ВАЖНИ УКАЗАНИЯ:
- Винаги отговаряйте на {$locale} език
- Бъдете точен и конкретен относно детайлите на имотите
- Включвайте ID номерата на имотите когато препоръчвате
- Ако няма точно съвпадение, ЗАДЪЛЖИТЕЛНО предложете алтернативи
- НИКОГА не казвайте че има технически проблем
- Пазете отговорите информативни но добре структурирани
- Винаги завършвайте с полезен въпрос или предложение

";

        if (!empty($properties)) {
            $systemPrompt .= "\n# НАЛИЧНИ ИМОТИ В МОМЕНТА:\n\n";
            foreach ($properties as $property) {
                $systemPrompt .= $this->formatPropertyForPrompt($property, $locale) . "\n";
            }
        } else {
            $systemPrompt .= "\n# ВНИМАНИЕ: В момента не са намерени точно съвпадащи имоти в базата данни.\n\n";
            $systemPrompt .= "**ВАЖНО:** Обяснете това честно на потребителя, но ЗАДЪЛЖИТЕЛНО предложете:\n";
            $systemPrompt .= "• Да се свърже с нашия екип за персонализирано търсене\n";
            $systemPrompt .= "• Да предостави повече детайли за своите изисквания\n";
            $systemPrompt .= "• Алтернативни възможности за подобни имоти\n\n";
        }

        $systemPrompt .= "\n# ИНФОРМАЦИЯ ЗА КОМПАНИЯТА:
• **Уебсайт:** Industrial Properties Europe
• **Специализация:** Индустриални недвижими имоти в България и Балканите  
• **Езици:** български, английски, немски, руски
• **Услуги:** Продажби, наем, консултации, оценки

# КОНТАКТ ЗА ДЕТАЙЛНИ ЗАПИТВАНИЯ:
Свържете се с нашия експертен екип за персонализирано обслужване и огледи на имоти.

**ПОМНЕТЕ:** Винаги завършвайте с конкретен въпрос или предложение за следващи стъпки!";

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

        // Format price with proper spacing
        $priceFormatted = 'N/A';
        if (is_numeric($price)) {
            $priceFormatted = number_format($price, 0, '', ' ') . ' EUR';
        } elseif (!empty($price) && $price !== 'N/A') {
            $priceFormatted = $price;
        }
        
        // Format area with proper spacing
        $areaFormatted = 'N/A';
        if (is_numeric($area)) {
            $areaFormatted = number_format($area, 0, '', ' ') . ' m²';
        } elseif (!empty($area) && $area !== 'N/A') {
            $areaFormatted = $area;
        }

        return "**Имот ID {$id}:** {$title}
• **Локация:** {$location}
• **Тип:** {$type}
• **Площ:** {$areaFormatted}
• **Цена:** {$priceFormatted}
• **Статус:** {$status}
• **Детайли:** [{$url}]({$url})

";
    }

    /**
     * Parse user message to search criteria
     */
    private function parseMessageToCriteria(string $message): array
    {
        $criteria = [];
        $messageLower = mb_strtolower($message, 'UTF-8');

        // Enhanced location detection with more variations
        $locations = [
            'софия' => ['софия', 'sofia', 'софийски', 'столица', 'столицата'],
            'пловдив' => ['пловдив', 'plovdiv', 'пловдивски'], 
            'варна' => ['варна', 'varna', 'варненски'],
            'бургас' => ['бургас', 'burgas', 'бургаски'],
            'русе' => ['русе', 'ruse', 'русенски'],
            'стара загора' => ['стара загора', 'stara zagora', 'старозагорски'],
            'плевен' => ['плевен', 'pleven', 'плевенски'],
            'хасково' => ['хасково', 'haskovo', 'хасковски'],
            'севлиево' => ['севлиево', 'sevlievo'],
            'благоевград' => ['благоевград', 'blagoevgrad'],
            'перник' => ['перник', 'pernik'],
            'шумен' => ['шумен', 'shumen'],
            'ямбол' => ['ямбол', 'yambol'],
            'видин' => ['видин', 'vidin'],
            'монтана' => ['монтана', 'montana'],
            'враца' => ['враца', 'vratsa']
        ];
        
        foreach ($locations as $bgKey => $variants) {
            foreach ($variants as $variant) {
                if (mb_strpos($messageLower, mb_strtolower($variant, 'UTF-8')) !== false) {
                    $criteria['location'] = $bgKey; // Return single string, not array
                    error_log("Location found in message: " . $bgKey);
                    break 2;
                }
            }
        }

        // Enhanced property type detection with more Bulgarian terms
        $types = [
            'warehouse' => [
                'склад', 'складово', 'складов', 'складова', 'складови', 'складове', 'складища',
                'warehouse', 'storage', 'lager', 'magazzino'
            ],
            'office' => [
                'офис', 'офиси', 'офисов', 'офисен', 'офисна', 'офисни', 'офисно',
                'office', 'büro', 'ufficio', 'bureau'
            ],
            'industrial' => [
                'индустриален', 'индустриална', 'индустриални', 'индустриално',
                'производство', 'производствен', 'производствена', 'производствени', 
                'фабрика', 'завод', 'цех', 
                'industrial', 'manufacturing', 'factory', 'plant', 'industrie'
            ],
            'logistics' => [
                'логистичен', 'логистична', 'логистични', 'логистично',
                'логистика', 'дистрибуция', 'разпределение',
                'logistics', 'distribution', 'logistik'
            ],
            'business' => [
                'бизнес', 'търговски', 'търговска', 'търговско', 'търговски',
                'business', 'commercial', 'kommerziell'
            ],
            'land' => [
                'земя', 'земи', 'парцел', 'парцели', 'терен', 'терени',
                'земеделска', 'промишлена земя', 'УПИ', 'ПИ',
                'land', 'plot', 'terrain', 'ground'
            ]
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

        // Price range detection
        $pricePatterns = [
            '/(\d+)\s*-\s*(\d+)\s*(хиляди|лв|лева|евро|euro|eur)/ui',
            '/до\s*(\d+)\s*(хиляди|лв|лева|евро|euro|eur)/ui',
            '/над\s*(\d+)\s*(хиляди|лв|лева|евро|euro|eur)/ui',
            '/между\s*(\d+)\s*и\s*(\d+)\s*(хиляди|лв|лева|евро|euro|eur)/ui'
        ];
        
        foreach ($pricePatterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                if (isset($matches[2])) {
                    $criteria['min_price'] = intval($matches[1]) * (stripos($matches[3], 'хиляди') !== false ? 1000 : 1);
                    $criteria['max_price'] = intval($matches[2]) * (stripos($matches[3], 'хиляди') !== false ? 1000 : 1);
                } else {
                    $price = intval($matches[1]) * (stripos($matches[2], 'хиляди') !== false ? 1000 : 1);
                    if (stripos($pattern, 'до') !== false) {
                        $criteria['max_price'] = $price;
                    } elseif (stripos($pattern, 'над') !== false) {
                        $criteria['min_price'] = $price;
                    }
                }
                break;
            }
        }

        // Area detection
        $areaPatterns = [
            '/(\d+)\s*-\s*(\d+)\s*(кв\.?\s*м|м2|квадрат)/ui',
            '/до\s*(\d+)\s*(кв\.?\s*м|м2|квадрат)/ui',
            '/над\s*(\d+)\s*(кв\.?\s*м|м2|квадрат)/ui',
            '/(\d+)\s*(кв\.?\s*м|м2|квадрат)/ui'
        ];
        
        foreach ($areaPatterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                if (isset($matches[2]) && !preg_match('/до|над/', $pattern)) {
                    $criteria['min_area'] = intval($matches[1]);
                    $criteria['max_area'] = intval($matches[2]);
                } else {
                    $area = intval($matches[1]);
                    if (stripos($pattern, 'до') !== false) {
                        $criteria['max_area'] = $area;
                    } elseif (stripos($pattern, 'над') !== false) {
                        $criteria['min_area'] = $area;
                    } else {
                        // For single area mentions, assume flexible range
                        $criteria['min_area'] = $area * 0.8;
                        $criteria['max_area'] = $area * 1.2;
                    }
                }
                break;
            }
        }

        error_log("Final parsed criteria: " . json_encode($criteria, JSON_UNESCAPED_UNICODE));
        return $criteria;
    }

    /**
     * Generate follow-up suggestions
     */
    private function generateFollowUpSuggestions(string $message, string $locale): array
    {
        // Improved follow-up suggestions based on message context and DeepSeek best practices
        $suggestions = [];
        $messageLower = mb_strtolower($message, 'UTF-8');
        
        // Location-based suggestions
        if (mb_strpos($messageLower, 'софия') !== false || mb_strpos($messageLower, 'sofia') !== false) {
            $suggestions[] = "Какви са предимствата на индустриалните имоти около София?";
            $suggestions[] = "Имате ли складове в индустриалните зони на София?";
        } elseif (mb_strpos($messageLower, 'пловдив') !== false) {
            $suggestions[] = "Какви са възможностите за инвестиции в Пловдив?";
            $suggestions[] = "Имате ли производствени сгради в района на Пловдив?";
        }
        
        // Type-based suggestions
        if (mb_strpos($messageLower, 'склад') !== false || mb_strpos($messageLower, 'warehouse') !== false) {
            $suggestions[] = "Какви са размерите на наличните складове?";
            $suggestions[] = "Можете ли да покажете складове с добра логистична свързаност?";
            $suggestions[] = "Какви са цените за наем на складови площи?";
        } elseif (mb_strpos($messageLower, 'офис') !== false || mb_strpos($messageLower, 'office') !== false) {
            $suggestions[] = "Търся офис с добра транспортна достъпност";
            $suggestions[] = "Какви офиси имате в бизнес центрове?";
        } elseif (mb_strpos($messageLower, 'производство') !== false || mb_strpos($messageLower, 'industrial') !== false) {
            $suggestions[] = "Нужна ми е производствена сграда с електрозахранване";
            $suggestions[] = "Имате ли промишлени имоти с готова инфраструктура?";
        }
        
        // Investment-related suggestions
        if (mb_strpos($messageLower, 'инвестиция') !== false || mb_strpos($messageLower, 'investment') !== false) {
            $suggestions[] = "Какви са най-добрите възможности за инвестиции?";
            $suggestions[] = "Можете ли да препоръчате имоти с висок потенциал?";
        }
        
        // General suggestions if none above match
        if (empty($suggestions)) {
            $suggestions = [
                "Какви типове индустриални имоти предлагате?",
                "Имате ли имоти в определен ценови диапазон?",
                "Можете ли да помогнете с избор на локация?",
                "Какви са услугите, които предлагате?",
                "Как мога да организирам оглед на имот?"
            ];
        }
        
        // Ensure we don't have too many suggestions
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
