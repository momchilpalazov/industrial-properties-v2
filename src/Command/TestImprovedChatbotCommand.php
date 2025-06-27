<?php

namespace App\Command;

use App\Service\AiChatbotService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-improved-chatbot',
    description: 'Тест на подобрения DeepSeek AI Chatbot',
)]
class TestImprovedChatbotCommand extends Command
{
    public function __construct(
        private AiChatbotService $aiChatbotService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('🚀 ТЕСТ НА ПОДОБРЕНИЯ DEEPSEEK AI CHATBOT');
        
        // Test cases to verify improvements
        $testCases = [
            [
                'message' => 'Какви индустриални имоти имате?',
                'description' => 'Общ въпрос за типове имоти'
            ],
            [
                'message' => 'Има ли складове в София?',
                'description' => 'Конкретен тип + локация'
            ],
            [
                'message' => 'Търся офис под наем в Пловдив',
                'description' => 'Комбиниран търговски въпрос'
            ],
            [
                'message' => 'Имате ли производствени сгради около 1000 кв.м?',
                'description' => 'Тип + площ'
            ],
            [
                'message' => 'Какви са възможностите за инвестиции?',
                'description' => 'Общ инвестиционен въпрос'
            ],
            [
                'message' => 'Може ли да видя имоти в Русе?',
                'description' => 'Локация фокус'
            ]
        ];

        foreach ($testCases as $index => $testCase) {
            $io->section("Тест " . ($index + 1) . ": " . $testCase['description']);
            $io->text("Въпрос: " . $testCase['message']);
            
            try {
                $response = $this->aiChatbotService->processMessage(
                    $testCase['message'],
                    'bg',
                    []
                );
                
                if ($response['success']) {
                    $io->success("✅ Успешен отговор");
                    $io->block($response['response'], null, 'fg=green', ' ', true);
                    
                    if (!empty($response['suggestions'])) {
                        $io->text("📝 Предложения:");
                        foreach ($response['suggestions'] as $suggestion) {
                            $io->text("  • " . $suggestion);
                        }
                    }
                    
                    $io->text("📊 Provider: " . ($response['provider'] ?? 'неизвестен'));
                    $io->text("🔢 Tokens: " . ($response['tokens_used'] ?? 0));
                    
                } else {
                    $io->error("❌ Грешка в отговора: " . ($response['error'] ?? 'Неизвестна грешка'));
                }
                
            } catch (\Exception $e) {
                $io->error("💥 Exception: " . $e->getMessage());
            }
            
            $io->newLine(2);
            
            // Small delay between requests
            sleep(2);
        }
        
        $io->success('🎉 Тестването завършено! Проверете резултатите за качество и consistency.');
        
        return Command::SUCCESS;
    }
} 