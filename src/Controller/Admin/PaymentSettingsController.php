<?php

namespace App\Controller\Admin;

use App\Form\PaymentSettingsType;
use App\Service\SettingsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/settings/payment')]
class PaymentSettingsController extends AbstractController
{
    private SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    #[Route('/', name: 'admin_payment_settings', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $settings = [
            'payment_gateway' => $this->settingsService->get('payment_gateway', 'stripe'),
            'stripe_public_key' => $this->settingsService->get('stripe_public_key', ''),
            'stripe_secret_key' => $this->settingsService->get('stripe_secret_key', ''),
            'stripe_webhook_secret' => $this->settingsService->get('stripe_webhook_secret', ''),
            'paypal_client_id' => $this->settingsService->get('paypal_client_id', ''),
            'paypal_client_secret' => $this->settingsService->get('paypal_client_secret', ''),
            'payment_currency' => $this->settingsService->get('payment_currency', 'EUR'),
            'vat_rate' => $this->settingsService->get('vat_rate', '20'),
            'payment_test_mode' => $this->settingsService->get('payment_test_mode', true),
        ];

        $form = $this->createForm(PaymentSettingsType::class, $settings);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            foreach ($data as $key => $value) {
                $this->settingsService->set($key, $value);
            }

            $this->addFlash('success', 'Настройките за плащане бяха запазени успешно.');
            return $this->redirectToRoute('admin_payment_settings');
        }

        return $this->render('admin/settings/payment.html.twig', [
            'form' => $form->createView(),
        ]);
    }
} 