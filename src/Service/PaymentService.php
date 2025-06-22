<?php

namespace App\Service;

use App\Entity\Promotion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentService
{
    private EntityManagerInterface $entityManager;
    private SettingsService $settingsService;
    private UrlGeneratorInterface $urlGenerator;
    private VipService $vipService;

    public function __construct(
        EntityManagerInterface $entityManager,
        SettingsService $settingsService,
        UrlGeneratorInterface $urlGenerator,
        VipService $vipService
    ) {
        $this->entityManager = $entityManager;
        $this->settingsService = $settingsService;
        $this->urlGenerator = $urlGenerator;
        $this->vipService = $vipService;
    }

    public function createPaymentSession(Promotion $promotion): array
    {
        $gateway = $this->settingsService->get('payment_gateway', 'stripe');
        
        if ($gateway === 'stripe') {
            return $this->createStripeSession($promotion);
        } elseif ($gateway === 'paypal') {
            return $this->createPayPalSession($promotion);
        }
        
        throw new \Exception('Невалидна платежна система');
    }
    
    private function createStripeSession(Promotion $promotion): array
    {
        $isTestMode = $this->settingsService->get('payment_test_mode', true);
        $secretKey = $this->settingsService->get('stripe_secret_key', '');
        
        if (empty($secretKey)) {
            throw new \Exception('Липсва Stripe таен ключ');
        }
        
        // Тук ще добавим интеграцията със Stripe API
        // За момента връщаме примерни данни
        
        $successUrl = $this->urlGenerator->generate('payment_success', [
            'id' => $promotion->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
        
        $cancelUrl = $this->urlGenerator->generate('payment_cancel', [
            'id' => $promotion->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
        
        return [
            'id' => 'stripe_session_' . uniqid(),
            'url' => 'https://example.com/stripe-checkout',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'test_mode' => $isTestMode,
        ];
    }
    
    private function createPayPalSession(Promotion $promotion): array
    {
        $isTestMode = $this->settingsService->get('payment_test_mode', true);
        $clientId = $this->settingsService->get('paypal_client_id', '');
        
        if (empty($clientId)) {
            throw new \Exception('Липсва PayPal Client ID');
        }
        
        // Тук ще добавим интеграцията с PayPal API
        // За момента връщаме примерни данни
        
        $successUrl = $this->urlGenerator->generate('payment_success', [
            'id' => $promotion->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
        
        $cancelUrl = $this->urlGenerator->generate('payment_cancel', [
            'id' => $promotion->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
        
        return [
            'id' => 'paypal_order_' . uniqid(),
            'url' => 'https://example.com/paypal-checkout',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'test_mode' => $isTestMode,
        ];
    }    public function processPayment(Promotion $promotion, string $transactionId): void
    {
        $promotion->setIsPaid(true);
        $promotion->setPaidAt(new \DateTime());
        $promotion->setTransactionId($transactionId);
        
        $this->entityManager->flush();
        
        // Автоматично активиране на VIP статус при платена VIP промоция
        if ($promotion->getType() === 'vip') {
            $this->vipService->activateVipFromPromotion($promotion);
        }
    }
    
    public function calculateTotalPrice(Promotion $promotion): float
    {
        $price = $promotion->getPrice();
        $vatRate = $this->settingsService->get('vat_rate', 20);
        
        $vat = $price * ($vatRate / 100);
        $total = $price + $vat;
        
        return round($total, 2);
    }
} 