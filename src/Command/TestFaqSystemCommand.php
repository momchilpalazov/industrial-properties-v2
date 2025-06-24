<?php

namespace App\Command;

use App\Entity\Faq;
use App\Entity\FaqCategory;
use App\Repository\FaqCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-faq-system',
    description: 'Тестване на FAQ системата с многоезична поддръжка'
)]
class TestFaqSystemCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FaqCategoryRepository $categoryRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Намираме първата категория
        $category = $this->categoryRepository->findOneBy([], ['id' => 'ASC']);
        
        if (!$category) {
            $io->error('Няма намерени FAQ категории!');
            return Command::FAILURE;
        }

        // Създаваме тестов FAQ запис
        $faq = new Faq();
        $faq->setQuestionBg('Тестов въпрос на български?');
        $faq->setQuestionEn('Test question in English?');
        $faq->setQuestionDe('Testfrage auf Deutsch?');
        $faq->setQuestionRu('Тестовый вопрос на русском?');
        
        $faq->setAnswerBg('Това е тестов отговор на български език.');
        $faq->setAnswerEn('This is a test answer in English.');
        $faq->setAnswerDe('Das ist eine Testantwort auf Deutsch.');
        $faq->setAnswerRu('Это тестовый ответ на русском языке.');
        
        $faq->setCategory($category);
        $faq->setPosition(999);
        $faq->setIsActive(true);

        try {
            $this->entityManager->persist($faq);
            $this->entityManager->flush();
            
            $io->success("✅ Тестов FAQ запис създаден успешно!");
            $io->info("📋 Детайли:");
            $io->text("ID: " . $faq->getId());
            $io->text("Категория: " . $category->getName('bg'));
            $io->text("BG: " . $faq->getQuestion('bg'));
            $io->text("EN: " . $faq->getQuestion('en'));
            $io->text("DE: " . $faq->getQuestion('de'));
            $io->text("RU: " . $faq->getQuestion('ru'));
            
        } catch (\Exception $e) {
            $io->error('Грешка при създаване на FAQ запис: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
