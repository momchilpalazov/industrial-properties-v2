<?php

namespace App\Controller\Public;

use App\Entity\PropertyInquiry;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class InquiryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PropertyRepository $propertyRepository,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private ParameterBagInterface $parameterBag,
        private HttpClientInterface $httpClient
    ) {}

    #[Route('/property/{id}/inquiry', name: 'property_inquiry_create', methods: ['POST'])]
    public function create(Request $request, int $id): Response
    {
        $token = new CsrfToken('inquiry', $request->request->get('_token'));
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            return $this->json([
                'success' => false,
                'message' => 'Невалиден CSRF токен'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Проверка за съгласие с обработката на лични данни
        if (!$request->request->has('gdprConsent')) {
            return $this->json([
                'success' => false,
                'message' => 'Моля, дайте съгласие за обработка на личните данни'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Валидация на reCAPTCHA
        $recaptchaResponse = $request->request->get('g-recaptcha-response');
        if (!$recaptchaResponse) {
            return $this->json([
                'success' => false,
                'message' => 'Моля, потвърдете, че не сте робот'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Получаване на reCAPTCHA секретен ключ от параметрите
        try {
            $recaptchaSecretKey = $this->parameterBag->get('recaptcha.secret_key');
        } catch (\Exception $e) {
            // Ако възникне грешка, използваме тестов ключ от документацията на Google
            // Този ключ работи само за localhost
            $recaptchaSecretKey = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe';
        }

        // Проверка на reCAPTCHA отговора чрез Google API
        try {
            $response = $this->httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret' => $recaptchaSecretKey,
                    'response' => $recaptchaResponse,
                    'remoteip' => $request->getClientIp()
                ]
            ]);

            $responseData = $response->toArray();
            if (!$responseData['success']) {
                return $this->json([
                    'success' => false,
                    'message' => 'reCAPTCHA валидацията не бе успешна. Моля, опитайте отново.'
                ], Response::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            // Log the error
            return $this->json([
                'success' => false,
                'message' => 'Грешка при валидация на reCAPTCHA'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $property = $this->propertyRepository->find($id);
        if (!$property) {
            return $this->json([
                'success' => false,
                'message' => 'Имотът не е намерен'
            ], Response::HTTP_NOT_FOUND);
        }

        $inquiry = new PropertyInquiry();
        $inquiry->setProperty($property)
            ->setName($request->request->get('name'))
            ->setEmail($request->request->get('email'))
            ->setPhone($request->request->get('phone'))
            ->setMessage($request->request->get('message'))
            ->setGdprConsent(true)
            ->setIsRead(false);

        $this->entityManager->persist($inquiry);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Запитването е изпратено успешно'
        ]);
    }
} 