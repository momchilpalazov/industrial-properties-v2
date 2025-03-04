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

    #[Route('/checkout/{id}', name: 'payment_checkout', methods: ['GET'])]
    public function checkout(Promotion $promotion): Response
    {
        if ($promotion->isPaid()) {
            $this->addFlash('info', 'Тази промоция вече е платена.');
            return $this->redirectToRoute('admin_promotion_edit', ['id' => $promotion->getId()]);
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
            $this->addFlash('error', 'Грешка при създаване на платежна сесия: ' . $e->getMessage());
            return $this->redirectToRoute('admin_promotion_edit', ['id' => $promotion->getId()]);
        }
    }

    #[Route('/success/{id}', name: 'payment_success', methods: ['GET'])]
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

    #[Route('/cancel/{id}', name: 'payment_cancel', methods: ['GET'])]
    public function cancel(Promotion $promotion): Response
    {
        return $this->render('payment/cancel.html.twig', [
            'promotion' => $promotion,
        ]);
    }

    #[Route('/webhook', name: 'payment_webhook', methods: ['POST'])]
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

    #[Route('/generate-link/{id}', name: 'payment_generate_link', methods: ['GET'])]
    public function generatePaymentLink(Promotion $promotion): Response
    {
        // Генериране на уникален токен за плащането
        $token = bin2hex(random_bytes(16));
        
        // Запазване на токена в промоцията
        $promotion->setPaymentToken($token);
        $this->entityManager->flush();
        
        // Генериране на URL за плащане
        $paymentUrl = $this->generateUrl('payment_public_checkout', [
            'token' => $token
        ], UrlGeneratorInterface::ABSOLUTE_URL);
        
        return $this->render('payment/generate_link.html.twig', [
            'promotion' => $promotion,
            'payment_url' => $paymentUrl
        ]);
    }
    
    #[Route('/public/checkout/{token}', name: 'payment_public_checkout', methods: ['GET'])]
    public function publicCheckout(string $token): Response
    {
        // Намиране на промоцията по токена
        $promotion = $this->entityManager->getRepository(Promotion::class)->findOneBy(['paymentToken' => $token]);
        
        if (!$promotion) {
            throw $this->createNotFoundException('Невалиден платежен линк.');
        }
        
        // Проверка дали промоцията вече е платена
        if ($promotion->isPaid()) {
            return $this->redirectToRoute('payment_already_paid', ['token' => $token]);
        }
        
        // Създаване на платежна сесия
        $gateway = $this->settingsService->get('payment_gateway', 'stripe');
        $testMode = $this->settingsService->get('payment_test_mode', false);
        $currency = $this->settingsService->get('payment_currency', 'EUR');
        $totalPrice = $this->paymentService->calculateTotalPrice($promotion);
        
        $session = $this->paymentService->createPaymentSession($promotion);
        
        return $this->render('payment/public_checkout.html.twig', [
            'promotion' => $promotion,
            'gateway' => $gateway,
            'test_mode' => $testMode,
            'currency' => $currency,
            'total_price' => $totalPrice,
            'session' => $session
        ]);
    }
    
    #[Route('/already-paid/{token}', name: 'payment_already_paid', methods: ['GET'])]
    public function alreadyPaid(string $token): Response
    {
        $promotion = $this->entityManager->getRepository(Promotion::class)->findOneBy(['paymentToken' => $token]);
        
        if (!$promotion) {
            throw $this->createNotFoundException('Невалиден платежен линк.');
        }
        
        return $this->render('payment/already_paid.html.twig', [
            'promotion' => $promotion
        ]);
    }
} 