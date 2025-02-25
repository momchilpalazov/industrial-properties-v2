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

class InquiryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PropertyRepository $propertyRepository,
        private CsrfTokenManagerInterface $csrfTokenManager
    ) {}

    #[Route('/property/{id}/inquiry', name: 'property_inquiry_create', methods: ['POST'])]
    public function create(Request $request, int $id): Response
    {
        $token = new CsrfToken('inquiry', $request->request->get('_token'));
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid CSRF token'
            ], Response::HTTP_BAD_REQUEST);
        }

        $property = $this->propertyRepository->find($id);
        if (!$property) {
            return $this->json([
                'success' => false,
                'message' => 'Property not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $inquiry = new PropertyInquiry();
        $inquiry->setProperty($property)
            ->setName($request->request->get('name'))
            ->setEmail($request->request->get('email'))
            ->setPhone($request->request->get('phone'))
            ->setMessage($request->request->get('message'))
            ->setIsRead(false);

        $this->entityManager->persist($inquiry);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Inquiry sent successfully'
        ]);
    }
} 