<?php

namespace App\Service;

use App\Entity\Offer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;

class OfferMailer
{
    private $mailer;
    private $twig;
    private $params;

    public function __construct(
        MailerInterface $mailer,
        Environment $twig,
        ParameterBagInterface $params
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->params = $params;
    }

    public function sendOfferEmail(Offer $offer): void
    {
        $email = (new Email())
            ->from('your-email@example.com') // Трябва да се замени с реален имейл
            ->to($offer->getEmail())
            ->subject('Оферта за имот ' . $offer->getProperty()->getReferenceNumber())
            ->html(
                $this->twig->render('admin/offer/email/offer_email.html.twig', [
                    'offer' => $offer
                ])
            );

        // Прикачване на PDF файла, ако съществува
        if ($offer->getPdfPath()) {
            $pdfPath = $this->params->get('kernel.project_dir') . '/public' . $offer->getPdfPath();
            if (file_exists($pdfPath)) {
                $email->attachFromPath($pdfPath);
            }
        }

        $this->mailer->send($email);
    }
} 