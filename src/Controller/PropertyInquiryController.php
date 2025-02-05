<?php

namespace App\Controller;

use App\Entity\Property;
use App\Entity\PropertyInquiry;
use App\Form\PropertyInquiryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PropertyInquiryController extends AbstractController
{
    #[Route('/property/{id}/inquiry', name: 'app_property_inquiry', methods: ['POST'])]
    public function submit(
        Request $request,
        Property $property,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        TranslatorInterface $translator
    ): Response {
        $inquiry = new PropertyInquiry();
        $inquiry->setProperty($property);
        
        $form = $this->createForm(PropertyInquiryType::class, $inquiry);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($inquiry);
            $entityManager->flush();
            
            // Send email to admin
            $email = (new TemplatedEmail())
                ->from($inquiry->getEmail())
                ->to('admin@example.com')
                ->subject($translator->trans('property.inquiry.email.subject', [
                    '%property%' => $property->getTitle()
                ]))
                ->htmlTemplate('emails/property_inquiry.html.twig')
                ->context([
                    'inquiry' => $inquiry,
                    'property' => $property
                ]);
            
            $mailer->send($email);
            
            $this->addFlash('success', 'property.inquiry.success');
            
            return $this->redirectToRoute('app_property_show', [
                'id' => $property->getId()
            ]);
        }
        
        return $this->render('property/_inquiry.html.twig', [
            'form' => $form->createView(),
            'property' => $property
        ]);
    }
} 