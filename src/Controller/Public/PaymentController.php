<?php

namespace App\Controller\Public;

use App\Entity\Promotion;
use App\Service\PaymentService;
use App\Service\SettingsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

    #[Route('/checkout/{token}', name: 'payment_public_checkout', methods: ['GET'])]
    public function checkout(string $token): Response
    {
        // Намиране на промоцията по токена
        $promotion = $this->entityManager->getRepository(Promotion::class)->findOneBy(['paymentToken' => $token]);
        
        if (!$promotion) {
            return $this->render('payment/error_public.html.twig', [
                'error_title' => 'Невалиден платежен линк',
                'error_message' => 'Този платежен линк е невалиден или вече е изтекъл.'
            ]);
        }
        
        // Проверка дали промоцията вече е платена
        if ($promotion->isPaid()) {
            return $this->redirectToRoute('payment_public_already_paid', ['token' => $token]);
        }
        
        try {
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
        } catch (\Exception $e) {
            $errorMessage = 'Възникна проблем при създаването на платежна сесия. ';
            if (str_contains($e->getMessage(), 'Липсва Stripe таен ключ')) {
                $errorMessage .= 'Моля, конфигурирайте Stripe таен ключ в настройките за плащане.';
            } else {
                $errorMessage .= 'Моля, проверете настройките за плащане.';
            }
            
            return $this->render('payment/error_public.html.twig', [
                'error_title' => 'Грешка при създаване на платежна сесия',
                'error_message' => $errorMessage,
                'error_details' => $e->getMessage(),
                'promotion' => $promotion
            ]);
        }
    }

    #[Route('/success/{token}', name: 'payment_public_success', methods: ['GET'])]
    public function success(string $token): Response
    {
        // Намиране на промоцията по токена
        $promotion = $this->entityManager->getRepository(Promotion::class)->findOneBy(['paymentToken' => $token]);
        
        if (!$promotion) {
            throw $this->createNotFoundException('Невалиден платежен линк.');
        }
        
        $transactionId = 'tx_' . uniqid();
        
        if (!$promotion->isPaid()) {
            $this->paymentService->processPayment($promotion, $transactionId);
        }
        
        return $this->render('payment/success.html.twig', [
            'promotion' => $promotion,
            'is_public' => true
        ]);
    }

    #[Route('/cancel/{token}', name: 'payment_public_cancel', methods: ['GET'])]
    public function cancel(string $token): Response
    {
        // Намиране на промоцията по токена
        $promotion = $this->entityManager->getRepository(Promotion::class)->findOneBy(['paymentToken' => $token]);
        
        if (!$promotion) {
            throw $this->createNotFoundException('Невалиден платежен линк.');
        }
        
        return $this->render('payment/cancel.html.twig', [
            'promotion' => $promotion,
            'is_public' => true
        ]);
    }

    #[Route('/already-paid/{token}', name: 'payment_public_already_paid', methods: ['GET'])]
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