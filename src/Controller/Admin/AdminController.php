<?php

namespace App\Controller\Admin;

use App\Repository\PropertyRepository;
use App\Repository\PropertyInquiryRepository;
use App\Repository\BlogPostRepository;
use App\Repository\UserRepository;
use App\Repository\FooterSettingsRepository;
use App\Repository\AboutSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Process\Process;

#[Route('', name: 'admin_')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private PropertyRepository $propertyRepository,
        private PropertyInquiryRepository $inquiryRepository,
        private BlogPostRepository $blogPostRepository,
        private UserRepository $userRepository,
        private FooterSettingsRepository $footerSettingsRepository,
        private AboutSettingsRepository $aboutSettingsRepository
    ) {}

    #[Route('/', name: 'dashboard')]
    public function dashboard(): Response
    {
        $queryBuilder = $this->inquiryRepository->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.isRead = :isRead')
            ->setParameter('isRead', false);

        return $this->render('admin/dashboard.html.twig', [
            'user' => $this->getUser(),
            'stats' => [
                'properties' => $this->propertyRepository->count([]),
                'inquiries' => $queryBuilder->getQuery()->getSingleScalarResult(),
                'posts' => $this->blogPostRepository->count([]),
                'users' => $this->userRepository->count([])
            ],
            'latest_properties' => $this->propertyRepository->findBy([], ['createdAt' => 'DESC'], 5),
            'latest_inquiries' => $this->inquiryRepository->findBy([], ['createdAt' => 'DESC'], 5)
        ]);
    }

    #[Route('/settings', name: 'settings')]
    public function settings(): Response
    {
        // TODO: Зареждане на настройките от базата данни
        $settings = [
            'site_name' => 'Industrial Properties',
            'admin_email' => 'admin@example.com',
            'items_per_page' => 10,
            'here_maps_api_key' => $_ENV['HERE_MAPS_API_KEY'] ?? ''
        ];

        return $this->render('admin/settings.html.twig', [
            'settings' => $settings
        ]);
    }

    #[Route('/settings/save', name: 'settings_save', methods: ['POST'])]
    public function saveSettings(Request $request): Response
    {
        // TODO: Запазване на настройките в базата данни
        $this->addFlash('success', 'Настройките бяха запазени успешно');
        return $this->redirectToRoute('admin_settings');
    }

    #[Route('/settings/api', name: 'settings_api', methods: ['POST'])]
    public function saveApiSettings(Request $request): Response
    {
        // TODO: Запазване на API настройките
        $this->addFlash('success', 'API настройките бяха запазени успешно');
        return $this->redirectToRoute('admin_settings');
    }

    #[Route('/profile', name: 'profile')]
    public function profile(): Response
    {
        return $this->render('admin/profile.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/cache/clear', name: 'cache_clear', methods: ['POST'])]
    public function clearCache(): Response
    {
        $process = new Process(['php', 'bin/console', 'cache:clear', '--env=prod']);
        $process->setWorkingDirectory($this->getParameter('kernel.project_dir'));
        $process->run();

        if (!$process->isSuccessful()) {
            $this->addFlash('error', 'Възникна грешка при изчистването на кеша: ' . $process->getErrorOutput());
        } else {
            $this->addFlash('success', 'Кешът беше изчистен успешно');
        }

        return $this->redirectToRoute('admin_settings');
    }

    #[Route('/footer', name: 'footer')]
    public function footer(): Response
    {
        $settings = $this->footerSettingsRepository->getSettings();

        return $this->render('admin/footer.html.twig', [
            'footer_settings' => [
                'description' => $settings->getDescription(),
                'address' => $settings->getAddress(),
                'phone' => $settings->getPhone(),
                'email' => $settings->getEmail(),
                'social_links' => $settings->getSocialLinks()
            ]
        ]);
    }

    #[Route('/footer/save', name: 'footer_save', methods: ['POST'])]
    public function saveFooter(Request $request): Response
    {
        $settings = $this->footerSettingsRepository->getSettings();
        
        $settings->setDescription($request->request->get('description'))
            ->setAddress($request->request->get('address'))
            ->setPhone($request->request->get('phone'))
            ->setEmail($request->request->get('email'))
            ->setSocialLinks($request->request->all()['social_links'] ?? []);

        $this->footerSettingsRepository->save($settings);
        
        $this->addFlash('success', 'Настройките на футъра бяха запазени успешно');
        return $this->redirectToRoute('admin_footer');
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        $settings = $this->aboutSettingsRepository->getSettings();

        return $this->render('admin/about.html.twig', [
            'settings' => [
                'title_bg' => $settings->getTitleBg(),
                'title_en' => $settings->getTitleEn(),
                'title_de' => $settings->getTitleDe(),
                'title_ru' => $settings->getTitleRu(),
                'subtitle_bg' => $settings->getSubtitleBg(),
                'subtitle_en' => $settings->getSubtitleEn(),
                'subtitle_de' => $settings->getSubtitleDe(),
                'subtitle_ru' => $settings->getSubtitleRu(),
                'content_bg' => $settings->getContentBg(),
                'content_en' => $settings->getContentEn(),
                'content_de' => $settings->getContentDe(),
                'content_ru' => $settings->getContentRu(),
                'company_image' => $settings->getCompanyImage(),
                'values_bg' => $settings->getValuesBg(),
                'values_en' => $settings->getValuesEn(),
                'values_de' => $settings->getValuesDe(),
                'values_ru' => $settings->getValuesRu(),
                'team' => $settings->getTeam(),
                'meta_title_bg' => $settings->getMetaTitleBg(),
                'meta_title_en' => $settings->getMetaTitleEn(),
                'meta_description_bg' => $settings->getMetaDescriptionBg(),
                'meta_description_en' => $settings->getMetaDescriptionEn()
            ]
        ]);
    }

    #[Route('/about/save', name: 'about_save', methods: ['POST'])]
    public function saveAbout(Request $request): Response
    {
        $settings = $this->aboutSettingsRepository->getSettings();
        
        // Заглавна секция
        $settings->setTitleBg($request->request->get('title_bg'))
            ->setTitleEn($request->request->get('title_en'))
            ->setTitleDe($request->request->get('title_de'))
            ->setTitleRu($request->request->get('title_ru'))
            ->setSubtitleBg($request->request->get('subtitle_bg'))
            ->setSubtitleEn($request->request->get('subtitle_en'))
            ->setSubtitleDe($request->request->get('subtitle_de'))
            ->setSubtitleRu($request->request->get('subtitle_ru'));

        // История секция
        $settings->setContentBg($request->request->get('content_bg'))
            ->setContentEn($request->request->get('content_en'))
            ->setContentDe($request->request->get('content_de'))
            ->setContentRu($request->request->get('content_ru'));

        // Снимка на компанията
        if ($request->files->has('company_image')) {
            $file = $request->files->get('company_image');
            if ($file) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move(
                    $this->getParameter('uploads_directory') . '/about',
                    $fileName
                );
                $settings->setCompanyImage('/uploads/about/' . $fileName);
            }
        }

        // Ценности
        $settings->setValuesBg($request->request->all()['values_bg'] ?? [])
            ->setValuesEn($request->request->all()['values_en'] ?? [])
            ->setValuesDe($request->request->all()['values_de'] ?? [])
            ->setValuesRu($request->request->all()['values_ru'] ?? []);

        // Екип
        $team = $request->request->all()['team'] ?? [];
        if ($request->files->has('team_images')) {
            $teamImages = $request->files->get('team_images');
            foreach ($teamImages as $key => $file) {
                if ($file) {
                    $originalExtension = $file->getClientOriginalExtension();
                    $fileName = md5(uniqid()) . '.' . $originalExtension;
                    $file->move(
                        $this->getParameter('upload_directory') . '/team',
                        $fileName
                    );
                    $team[$key]['image'] = $fileName;
                }
            }
        }
        $settings->setTeam($team);

        // Meta данни
        $settings->setMetaTitleBg($request->request->get('meta_title_bg'))
            ->setMetaTitleEn($request->request->get('meta_title_en'))
            ->setMetaDescriptionBg($request->request->get('meta_description_bg'))
            ->setMetaDescriptionEn($request->request->get('meta_description_en'));

        $this->aboutSettingsRepository->save($settings);
        
        $this->addFlash('success', 'Съдържанието на страницата беше запазено успешно');
        return $this->redirectToRoute('admin_about');
    }
} 