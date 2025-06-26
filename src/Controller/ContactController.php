<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ContactService;
use App\Repository\ContactSettingsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ContactController extends AbstractController
{
    private ContactService $contactService;
    private ContactSettingsRepository $contactSettingsRepository;

    public function __construct(
        Environment $twig,
        ContactService $contactService,
        ContactSettingsRepository $contactSettingsRepository
    ) {
        parent::__construct($twig);
        $this->contactService = $contactService;
        $this->contactSettingsRepository = $contactSettingsRepository;
    }

    public function index(Request $request): Response
    {
        $locale = $request->getLocale();
        $settings = $this->contactSettingsRepository->getSettings();
        
        // Prepare contact data based on locale
        $contactData = [
            'title' => $this->getLocalizedField($settings, 'title', $locale),
            'subtitle' => $this->getLocalizedField($settings, 'subtitle', $locale),
            'company_name' => $settings->getCompanyName(),
            'address' => $settings->getAddress(),
            'phone' => $settings->getPhone(),
            'email' => $settings->getEmail(),
            'working_hours' => $settings->getWorkingHours(),
            'social_media' => $settings->getSocialMedia(),
            'map_coordinates' => $settings->getMapCoordinates(),
        ];
        
        return $this->render('contact/index.html.twig', [
            'current_language' => $locale,
            'contact' => $contactData,
        ]);
    }

    private function getLocalizedField($settings, string $field, string $locale): ?string
    {
        $method = 'get' . ucfirst($field) . ucfirst($locale);
        return method_exists($settings, $method) ? $settings->$method() : null;
    }

    public function send(Request $request): Response
    {
        if (!$request->isMethod('POST')) {
            return $this->json(['error' => 'Method not allowed'], Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $data = json_decode($request->getContent(), true);

        // Валидация на входните данни
        $errors = $this->validateContactData($data);
        if (!empty($errors)) {
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->contactService->sendContactMessage($data);

        if ($result) {
            return $this->json(['message' => 'Message sent successfully']);
        }

        return $this->json(
            ['error' => 'Failed to send message'],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    private function validateContactData(array $data): array
    {
        $errors = [];
        $requiredFields = ['name', 'email', 'subject', 'message'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = 'This field is required';
            }
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (!empty($data['phone']) && !preg_match('/^[0-9+\-\s()]*$/', $data['phone'])) {
            $errors['phone'] = 'Invalid phone format';
        }

        return $errors;
    }
} 