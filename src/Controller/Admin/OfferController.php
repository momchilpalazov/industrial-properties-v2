<?php

namespace App\Controller\Admin;

use App\Entity\Offer;
use App\Entity\PropertyInquiry;
use App\Form\OfferType;
use App\Repository\OfferRepository;
use App\Service\OfferPdfGenerator;
use App\Service\OfferMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/offers')]
class OfferController extends AbstractController
{
    #[Route('/', name: 'admin_offer_index', methods: ['GET'])]
    public function index(OfferRepository $offerRepository): Response
    {
        return $this->render('admin/offer/index.html.twig', [
            'offers' => $offerRepository->findAll(),
        ]);
    }

    #[Route('/new/{inquiry}', name: 'admin_offer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PropertyInquiry $inquiry, EntityManagerInterface $entityManager): Response
    {
        $offer = $inquiry->createOffer();
        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($offer);
            $entityManager->flush();

            $this->addFlash('success', 'Офертата беше създадена успешно.');
            return $this->redirectToRoute('admin_offer_index');
        }

        return $this->render('admin/offer/new.html.twig', [
            'offer' => $offer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_offer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Offer $offer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Офертата беше обновена успешно.');
            return $this->redirectToRoute('admin_offer_index');
        }

        return $this->render('admin/offer/edit.html.twig', [
            'offer' => $offer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_offer_delete', methods: ['POST'])]
    public function delete(Request $request, Offer $offer, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$offer->getId(), $request->request->get('_token'))) {
            $entityManager->remove($offer);
            $entityManager->flush();
            $this->addFlash('success', 'Офертата беше изтрита успешно.');
        }

        return $this->redirectToRoute('admin_offer_index');
    }

    #[Route('/{id}/generate-pdf', name: 'admin_offer_generate_pdf', methods: ['POST'])]
    public function generatePdf(Offer $offer, OfferPdfGenerator $pdfGenerator, EntityManagerInterface $entityManager): Response
    {
        try {
            $pdfPath = $pdfGenerator->generatePdf($offer);
            $entityManager->flush(); // Запазваме новия PDF път

            $this->addFlash('success', 'PDF документът беше генериран успешно.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Възникна грешка при генерирането на PDF: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_offer_index');
    }

    #[Route('/{id}/send-email', name: 'admin_offer_send_email', methods: ['POST'])]
    public function sendEmail(
        Offer $offer,
        OfferPdfGenerator $pdfGenerator,
        OfferMailer $offerMailer,
        EntityManagerInterface $entityManager
    ): Response {
        try {
            // Генерираме PDF ако няма такъв
            if (!$offer->getPdfPath()) {
                $pdfGenerator->generatePdf($offer);
                $entityManager->flush();
            }

            // Изпращаме имейла
            $offerMailer->sendOfferEmail($offer);

            // Променяме статуса на офертата
            $offer->setStatus('изпратена');
            $entityManager->flush();

            $this->addFlash('success', 'Офертата беше изпратена успешно на ' . $offer->getEmail());
        } catch (\Exception $e) {
            $this->addFlash('error', 'Възникна грешка при изпращането на офертата: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_offer_index');
    }
} 