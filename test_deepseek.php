<?php

require_once 'vendor/autoload.php';

use Symfony\Component\HttpClient\HttpClient;

$httpClient = HttpClient::create();
$apiKey = 'sk-22f35c984509479da2f792dfcb153d0c';
$apiUrl = 'https://api.deepseek.com/v1/chat/completions';

$text = 'Тест текст';
$sourceLang = 'Bulgarian';
$targetLang = 'English';

$systemPrompt = "You are a professional translator specializing in real estate terminology. " .
    "Translate the following text from {$sourceLang} to {$targetLang}. " .
    "Maintain the original formatting (HTML tags, line breaks, etc.) and ensure accuracy in real estate terms. " .
    "Provide only the translated text without any explanations or additional comments.";

try {
    $response = $httpClient->request('POST', $apiUrl, [
        'headers' => [
            'Authorization' => 'Bearer ' . $apiKey,
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
            'temperature' => 0.1,
            'max_tokens' => 2000
        ]
    ]);

    $data = $response->toArray();
    echo "Success! Translation: " . $data['choices'][0]['message']['content'] . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Response: " . $response->getContent(false) . "\n";
}
