<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ContactRepository;

class ContactService
{
    private ContactRepository $contactRepository;

    public function __construct(ContactRepository $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    public function sendContactMessage(array $data): bool
    {
        // Валидация на входните данни
        if (empty($data['name']) || empty($data['email']) || 
            empty($data['subject']) || empty($data['message'])) {
            return false;
        }

        // Валидация на имейл
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Валидация на телефон (ако е предоставен)
        if (!empty($data['phone']) && !preg_match('/^[0-9+\-\s()]*$/', $data['phone'])) {
            return false;
        }

        // Базова защита от спам
        if (strlen($data['message']) > 2000 || 
            preg_match('/https?:\/\//i', $data['message']) > 2) {
            return false;
        }

        try {
            // Добавяне на IP адрес
            $data['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? null;

            // Почистване на входните данни
            $messageData = [
                'name' => strip_tags($data['name']),
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'subject' => strip_tags($data['subject']),
                'message' => strip_tags($data['message']),
                'ip_address' => $data['ip_address']
            ];

            $messageId = $this->contactRepository->saveMessage($messageData);

            if ($messageId > 0) {
                // TODO: Изпращане на имейл нотификация
                $this->sendEmailNotification($messageData);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            // TODO: Добавяне на логване
            return false;
        }
    }

    public function getUnreadMessagesCount(): int
    {
        return $this->contactRepository->getUnreadMessagesCount();
    }

    public function markAsRead(int $id): bool
    {
        return $this->contactRepository->markAsRead($id);
    }

    public function getLatestMessages(int $limit = 10): array
    {
        return $this->contactRepository->getLatestMessages($limit);
    }

    public function searchMessages(array $criteria): array
    {
        return $this->contactRepository->searchMessages($criteria);
    }

    public function cleanupOldMessages(int $daysOld = 90): int
    {
        return $this->contactRepository->deleteOldMessages($daysOld);
    }

    private function sendEmailNotification(array $messageData): void
    {
        // TODO: Имплементиране на изпращане на имейл
        // Това е заглушка, която ще бъде имплементирана по-късно
        // с използване на подходяща библиотека за изпращане на имейли
    }

    public function formatMessage(string $message): string
    {
        // Премахване на HTML тагове
        $message = strip_tags($message);
        
        // Конвертиране на URL адреси в линкове
        $message = preg_replace(
            '/(https?:\/\/[^\s<]+)/i',
            '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
            $message
        );
        
        // Форматиране на нови редове
        $message = nl2br($message);
        
        return $message;
    }
} 