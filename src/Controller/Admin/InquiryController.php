<?php

namespace App\Controller\Admin;

use App\Entity\PropertyInquiry;
use App\Repository\PropertyInquiryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/inquiries')]
#[IsGranted('ROLE_ADMIN')]
class InquiryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PropertyInquiryRepository $inquiryRepository,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private PaginatorInterface $paginator
    ) {}

    #[Route('/', name: 'admin_property_inquiries', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('i', 'p')
            ->from(PropertyInquiry::class, 'i')
            ->leftJoin('i.property', 'p')
            ->orderBy('i.createdAt', 'DESC');

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/inquiry/index.html.twig', [
            'inquiries' => $pagination,
        ]);
    }

    #[Route('/{id}/toggle-read', name: 'admin_property_inquiry_toggle_read', methods: ['POST'])]
    public function toggleRead(PropertyInquiry $inquiry): Response
    {
        $inquiry->setIsRead(!$inquiry->isRead());
        $this->entityManager->flush();

        return $this->json(['isRead' => $inquiry->isRead()]);
    }

    #[Route('/{id}/delete', name: 'admin_property_inquiry_delete', methods: ['POST'])]
    public function delete(PropertyInquiry $inquiry): Response
    {
        $this->entityManager->remove($inquiry);
        $this->entityManager->flush();

        $this->addFlash('success', 'Запитването беше изтрито успешно.');

        return $this->redirectToRoute('admin_property_inquiries');
    }

    #[Route('/{id}', name: 'admin_property_inquiry_show', methods: ['GET'])]
    public function show(PropertyInquiry $inquiry): Response
    {
        if (!$inquiry->isRead()) {
            $inquiry->setIsRead(true);
            $this->entityManager->flush();
        }

        return $this->render('admin/inquiry/show.html.twig', [
            'inquiry' => $inquiry
        ]);
    }

    #[Route('/{id}/respond', name: 'admin_property_inquiry_respond', methods: ['POST'])]
    public function respond(Request $request, PropertyInquiry $inquiry): Response
    {
        $token = new CsrfToken('inquiry_respond', $request->request->get('_token'));
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            $this->addFlash('error', 'Невалиден CSRF токен.');
            return $this->redirectToRoute('admin_property_inquiry_show', ['id' => $inquiry->getId()]);
        }

        $response = $request->request->get('response');
        if (empty($response)) {
            $this->addFlash('error', 'Моля, въведете отговор.');
            return $this->redirectToRoute('admin_property_inquiry_show', ['id' => $inquiry->getId()]);
        }

        $inquiry->setResponse($response);
        $this->entityManager->flush();

        $this->addFlash('success', 'Отговорът беше запазен успешно. Моля, изпратете имейл до ' . $inquiry->getEmail());

        return $this->redirectToRoute('admin_property_inquiry_show', ['id' => $inquiry->getId()]);
    }
} 