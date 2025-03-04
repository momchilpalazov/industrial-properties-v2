<?php

namespace App\Controller\Admin;

use App\Entity\Promotion;
use App\Service\PaymentService;
use App\Service\SettingsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private PaymentService $paymentService;
    private SettingsService $settingsService;

    public function __construct(
        EntityManagerInterface $entityManager,
        PaymentService $paymentService,
        SettingsService $settingsService
    ) {
        $this->entityManager = $entityManager;
        $this->paymentService = $paymentService;
        $this->settingsService = $settingsService;
    }

    #[Route('/checkout/{id}', name: 'admin_payment_checkout', methods: ['GET'])]
    public function checkout(Promotion $promotion): Response
    {
        if ($promotion->isPaid()) {
            return $this->render('payment/error_admin.html.twig', [
                'error_title' => 'Промоцията е вече платена',
                'error_message' => 'Тази промоция вече е платена и не може да бъде платена отново.',
                'promotion' => $promotion
            ]);
        }

        try {
            $session = $this->paymentService->createPaymentSession($promotion);
            
            return $this->render('payment/checkout.html.twig', [
                'promotion' => $promotion,
                'session' => $session,
                'total_price' => $this->paymentService->calculateTotalPrice($promotion),
                'currency' => $this->settingsService->get('payment_currency', 'EUR'),
                'gateway' => $this->settingsService->get('payment_gateway', 'stripe'),
                'test_mode' => $this->settingsService->get('payment_test_mode', true),
            ]);
        } catch (\Exception $e) {
            $errorMessage = 'Възникна проблем при създаването на платежна сесия. ';
            if (str_contains($e->getMessage(), 'Липсва Stripe таен ключ')) {
                $errorMessage .= 'Моля, конфигурирайте Stripe таен ключ в настройките за плащане.';
            } else {
                $errorMessage .= 'Моля, проверете настройките за плащане.';
            }
            
            return $this->render('payment/error_admin.html.twig', [
                'error_title' => 'Грешка при създаване на платежна сесия',
                'error_message' => $errorMessage,
                'error_details' => $e->getMessage(),
                'promotion' => $promotion
            ]);
        }
    }

    #[Route('/success/{id}', name: 'admin_payment_success', methods: ['GET'])]
    public function success(Request $request, Promotion $promotion): Response
    {
        $transactionId = $request->query->get('transaction_id', 'tx_' . uniqid());
        
        if (!$promotion->isPaid()) {
            $this->paymentService->processPayment($promotion, $transactionId);
            $this->addFlash('success', 'Плащането беше успешно обработено.');
        }
        
        return $this->render('payment/success.html.twig', [
            'promotion' => $promotion,
        ]);
    }

    #[Route('/cancel/{id}', name: 'admin_payment_cancel', methods: ['GET'])]
    public function cancel(Promotion $promotion): Response
    {
        return $this->render('payment/cancel.html.twig', [
            'promotion' => $promotion,
        ]);
    }

    #[Route('/webhook', name: 'admin_payment_webhook', methods: ['POST'])]
    public function webhook(Request $request): Response
    {
        $gateway = $this->settingsService->get('payment_gateway', 'stripe');
        $payload = $request->getContent();
        
        if ($gateway === 'stripe') {
            // Тук ще добавим обработката на Stripe webhook
            // За момента просто връщаме успешен отговор
        } elseif ($gateway === 'paypal') {
            // Тук ще добавим обработката на PayPal webhook
            // За момента просто връщаме успешен отговор
        }
        
        return new Response('Webhook received', Response::HTTP_OK);
    }

    #[Route('/generate-link/{id}', name: 'admin_payment_generate_link', methods: ['GET'])]
    public function generatePaymentLink(Promotion $promotion): Response
    {
        // Генериране на уникален токен за плащането
        $token = bin2hex(random_bytes(16));
        
        // Запазване на токена в промоцията
        $promotion->setPaymentToken($token);
        $this->entityManager->flush();
        
        // Генериране на URL за плащане
        $paymentUrl = $this->generateUrl('payment_public_checkout', [
            'token' => $token,
            '_locale' => $this->getParameter('locale')
        ], UrlGeneratorInterface::ABSOLUTE_URL);
        
        return $this->render('payment/generate_link.html.twig', [
            'promotion' => $promotion,
            'payment_url' => $paymentUrl
        ]);
    }
} 