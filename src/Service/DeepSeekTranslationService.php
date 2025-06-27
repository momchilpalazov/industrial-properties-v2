<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class DeepSeekTranslationService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;
    private string $apiUrl;

    public function __construct(HttpClientInterface $httpClient, string $deepSeekApiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $deepSeekApiKey;
        $this->apiUrl = 'https://api.deepseek.com/v1/chat/completions';
    }

    public function translate(string $text, string $sourceLang, string $targetLang, string $context = 'general'): string
    {
        $langMap = [
            'bg' => 'Bulgarian',
            'en' => 'English', 
            'de' => 'German',
            'ru' => 'Russian'
        ];

        $sourceLangName = $langMap[$sourceLang] ?? $sourceLang;
        $targetLangName = $langMap[$targetLang] ?? $targetLang;

        $systemPrompt = $this->buildSystemPrompt($context, $sourceLangName, $targetLangName);

        $response = $this->httpClient->request('POST', $this->apiUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'deepseek-chat',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt
                    ],
                    [
                        'role' => 'user',
                        'content' => $text
                    ]
                ],
                'temperature' => 0.1, // Ниска температура за по-консистентни преводи
                'max_tokens' => 2000
            ]
        ]);

        $data = $response->toArray();

        if (!isset($data['choices'][0]['message']['content'])) {
            throw new \RuntimeException('Invalid response from DeepSeek API');
        }

        return trim($data['choices'][0]['message']['content']);
    }

    private function buildSystemPrompt(string $context, string $sourceLang, string $targetLang): string
    {
        $basePrompt = "You are a professional translator specializing in real estate terminology. ";
        $basePrompt .= "Translate the following text from {$sourceLang} to {$targetLang}. ";
        $basePrompt .= "Maintain the original formatting (HTML tags, line breaks, etc.) and ensure accuracy in real estate terms. ";
        $basePrompt .= "Provide only the translated text without any explanations or additional comments.";

        if ($context === 'real_estate') {
            $basePrompt .= " Focus on accurate translation of property descriptions, locations, and real estate terminology.";
            $basePrompt .= " Preserve professional tone and technical precision.";
        }

        return $basePrompt;
    }
}
