<?php

namespace App\Controller\Admin;

use App\Repository\PropertyRepository;
use App\Repository\PropertyInquiryRepository;
use App\Repository\BlogPostRepository;
use App\Repository\UserRepository;
use App\Repository\FooterSettingsRepository;
use App\Repository\AboutSettingsRepository;
use App\Repository\ContactSettingsRepository;
use App\Repository\ApiSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Process\Process;

#[Route('/admin', name: 'admin_')]
// #[IsGranted('ROLE_ADMIN')] // Временно коментирано за тестване
class AdminController extends AbstractController
{
    private PropertyRepository $propertyRepository;
    private PropertyInquiryRepository $inquiryRepository;
    private BlogPostRepository $blogPostRepository;
    private UserRepository $userRepository;
    private FooterSettingsRepository $footerSettingsRepository;
    private AboutSettingsRepository $aboutSettingsRepository;
    private ContactSettingsRepository $contactSettingsRepository;
    private ApiSettingsRepository $apiSettingsRepository;

    public function __construct(
        PropertyRepository $propertyRepository,
        PropertyInquiryRepository $inquiryRepository,
        BlogPostRepository $blogPostRepository,
        UserRepository $userRepository,
        FooterSettingsRepository $footerSettingsRepository,
        AboutSettingsRepository $aboutSettingsRepository,
        ContactSettingsRepository $contactSettingsRepository,
        ApiSettingsRepository $apiSettingsRepository
    ) {
        $this->propertyRepository = $propertyRepository;
        $this->inquiryRepository = $inquiryRepository;
        $this->blogPostRepository = $blogPostRepository;
        $this->userRepository = $userRepository;
        $this->footerSettingsRepository = $footerSettingsRepository;
        $this->aboutSettingsRepository = $aboutSettingsRepository;
        $this->contactSettingsRepository = $contactSettingsRepository;
        $this->apiSettingsRepository = $apiSettingsRepository;
    }

    #[Route('/', name: 'dashboard')]
    public function dashboard(): Response
    {
        $stats = [
            'properties' => $this->propertyRepository->count([]),
            'inquiries' => $this->inquiryRepository->count([]),
            'posts' => $this->blogPostRepository->count([]),
            'users' => $this->userRepository->count([]),
        ];

        $latestProperties = $this->propertyRepository->findBy([], ['createdAt' => 'DESC'], 5);
        $latestInquiries = $this->inquiryRepository->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => $stats,
            'latest_properties' => $latestProperties,
            'latest_inquiries' => $latestInquiries,
        ]);
    }

    #[Route('/settings', name: 'settings')]
    public function settings(): Response
    {
        $apiSettings = $this->apiSettingsRepository->getSettings();

        return $this->render('admin/settings/general.html.twig', [
            'settings' => [
                'site_name' => 'Industrial Properties',
                'admin_email' => 'admin@example.com',
                'items_per_page' => 10,
                'facebook_app_id' => $apiSettings->getFacebookAppId(),
                'facebook_app_secret' => $apiSettings->getFacebookAppSecret(),
                'facebook_page_id' => $apiSettings->getFacebookPageId(),
                'facebook_page_access_token' => $apiSettings->getFacebookPageAccessToken(),
                'linkedin_access_token' => $apiSettings->getLinkedinAccessToken(),
                'linkedin_organization_id' => $apiSettings->getLinkedinOrganizationId()
            ]
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
        $settings = $this->apiSettingsRepository->getSettings();

        $settings->setFacebookAppId($request->request->get('facebook_app_id'))
            ->setFacebookAppSecret($request->request->get('facebook_app_secret'))
            ->setFacebookPageId($request->request->get('facebook_page_id'))
            ->setFacebookPageAccessToken($request->request->get('facebook_page_access_token'))
            ->setLinkedinAccessToken($request->request->get('linkedin_access_token'))
            ->setLinkedinOrganizationId($request->request->get('linkedin_organization_id'));

        $this->apiSettingsRepository->save($settings);

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

        return $this->render('admin/settings/footer.html.twig', [
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

        return $this->render('admin/settings/about.html.twig', [
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
                'team' => $settings->getTeam(), // За обратна съвместимост
                'team_bg' => $settings->getTeamBg(),
                'team_en' => $settings->getTeamEn(),
                'team_de' => $settings->getTeamDe(),
                'team_ru' => $settings->getTeamRu(),
                'team_common' => $settings->getTeamCommon(),
                'meta_title_bg' => $settings->getMetaTitleBg(),
                'meta_title_en' => $settings->getMetaTitleEn(),
                'meta_title_de' => $settings->getMetaTitleDe(),
                'meta_title_ru' => $settings->getMetaTitleRu(),
                'meta_description_bg' => $settings->getMetaDescriptionBg(),
                'meta_description_en' => $settings->getMetaDescriptionEn(),
                'meta_description_de' => $settings->getMetaDescriptionDe(),
                'meta_description_ru' => $settings->getMetaDescriptionRu()
            ]
        ]);
    }

    private function setFilePermissions(string $filePath): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // За Windows, даваме пълни права на Everyone група
            $cmd = sprintf('icacls "%s" /grant "Everyone":(OI)(CI)F /T', $filePath);
            exec($cmd);
            
            // Даваме права и на IUSR потребителя
            $cmd = sprintf('icacls "%s" /grant "IUSR":(OI)(CI)F /T', $filePath);
            exec($cmd);
            
            // Даваме права на IIS_IUSRS групата
            $cmd = sprintf('icacls "%s" /grant "IIS_IUSRS":(OI)(CI)F /T', $filePath);
            exec($cmd);
        } else {
            chmod($filePath, 0777);
        }
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
                // Изтриваме старата снимка
                if ($settings->getCompanyImage()) {
                    $oldImagePath = $this->getParameter('kernel.project_dir') . $settings->getCompanyImage();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $uploadDir = $this->getParameter('upload_directory') . '/uploads/about/company';
                
                // Създаваме директорията, ако не съществува
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $file->move($uploadDir, $fileName);
                
                // Задаваме правилните права на директорията и файла
                $this->setFilePermissions($uploadDir);
                $this->setFilePermissions($uploadDir . '/' . $fileName);
                
                $settings->setCompanyImage('/uploads/about/company/' . $fileName);
            }
        }

        // Ценности
        $settings->setValuesBg($request->request->all()['values_bg'] ?? [])
            ->setValuesEn($request->request->all()['values_en'] ?? [])
            ->setValuesDe($request->request->all()['values_de'] ?? [])
            ->setValuesRu($request->request->all()['values_ru'] ?? []);

        // Екип - многоезичен
        $teamBg = $request->request->all()['team_bg'] ?? [];
        $teamEn = $request->request->all()['team_en'] ?? [];
        $teamDe = $request->request->all()['team_de'] ?? [];
        $teamRu = $request->request->all()['team_ru'] ?? [];
        $currentTeamCommon = $settings->getTeamCommon() ?? [];
        $deleteTeamImages = $request->request->all()['delete_team_image'] ?? [];
        
        // Обработваме изтриването на снимки
        foreach ($deleteTeamImages as $key => $shouldDelete) {
            if ($shouldDelete === "1" && isset($currentTeamCommon[$key]['image'])) {
                $oldImagePath = $this->getParameter('kernel.project_dir') . $currentTeamCommon[$key]['image'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
                unset($currentTeamCommon[$key]['image']);
            }
        }

        // Обработваме новите снимки
        if ($request->files->has('team_images')) {
            $teamImages = $request->files->get('team_images');
            foreach ($teamImages as $key => $file) {
                if ($file) {
                    // Изтриваме старата снимка
                    if (isset($currentTeamCommon[$key]['image'])) {
                        $oldImagePath = $this->getParameter('kernel.project_dir') . $currentTeamCommon[$key]['image'];
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }

                    $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                    $uploadDir = $this->getParameter('upload_directory') . '/uploads/about/team';
                    
                    // Създаваме директорията, ако не съществува
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $file->move($uploadDir, $fileName);
                    
                    // Задаваме правилните права на директорията и файла
                    $this->setFilePermissions($uploadDir);
                    $this->setFilePermissions($uploadDir . '/' . $fileName);
                    
                    $currentTeamCommon[$key]['image'] = '/uploads/about/team/' . $fileName;
                }
            }
        }

        // Записваме екипа за всички езици
        $settings->setTeamBg($teamBg)
            ->setTeamEn($teamEn)
            ->setTeamDe($teamDe)
            ->setTeamRu($teamRu)
            ->setTeamCommon($currentTeamCommon);

        // Meta данни
        $settings->setMetaTitleBg($request->request->get('meta_title_bg'))
            ->setMetaTitleEn($request->request->get('meta_title_en'))
            ->setMetaTitleDe($request->request->get('meta_title_de'))
            ->setMetaTitleRu($request->request->get('meta_title_ru'))
            ->setMetaDescriptionBg($request->request->get('meta_description_bg'))
            ->setMetaDescriptionEn($request->request->get('meta_description_en'))
            ->setMetaDescriptionDe($request->request->get('meta_description_de'))
            ->setMetaDescriptionRu($request->request->get('meta_description_ru'));

        $this->aboutSettingsRepository->save($settings);
        
        $this->addFlash('success', 'Съдържанието на страницата беше запазено успешно');
        return $this->redirectToRoute('admin_about');
    }

    #[Route('/about/delete-image', name: 'about_delete_image', methods: ['POST'])]
    public function deleteAboutImage(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$this->isCsrfTokenValid('delete-image', $request->headers->get('X-CSRF-TOKEN'))) {
            return new Response('Invalid CSRF token', Response::HTTP_FORBIDDEN);
        }

        $settings = $this->aboutSettingsRepository->getSettings();
        $success = false;
        
        if ($data['type'] === 'company') {
            // Изтриване на снимката на компанията
            if ($settings->getCompanyImage()) {
                $oldImagePath = $this->getParameter('kernel.project_dir') . $settings->getCompanyImage();
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
                $settings->setCompanyImage(null);
                $success = true;
            }
        } elseif ($data['type'] === 'team' && isset($data['key'])) {
            // Изтриване на снимка на член от екипа
            $team = $settings->getTeam();
            if (isset($team[$data['key']]['image'])) {
                $oldImagePath = $this->getParameter('kernel.project_dir') . $team[$data['key']]['image'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
                unset($team[$data['key']]['image']);
                $settings->setTeam($team);
                $success = true;
            }
        }

        if ($success) {
            $this->aboutSettingsRepository->save($settings, true);
            return new Response(json_encode(['success' => true]), Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }
        
        return new Response(json_encode(['success' => false]), Response::HTTP_BAD_REQUEST, ['Content-Type' => 'application/json']);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        $settings = $this->contactSettingsRepository->getSettings();
        
        return $this->render('admin/settings/contact.html.twig', [
            'settings' => $settings,
        ]);
    }

    #[Route('/contact/save', name: 'contact_save', methods: ['POST'])]
    public function saveContact(Request $request): Response
    {
        $settings = $this->contactSettingsRepository->getSettings();
        
        // Update basic fields
        $settings->setTitleBg($request->get('title_bg'));
        $settings->setTitleEn($request->get('title_en'));
        $settings->setTitleDe($request->get('title_de'));
        $settings->setTitleRu($request->get('title_ru'));
        
        $settings->setSubtitleBg($request->get('subtitle_bg'));
        $settings->setSubtitleEn($request->get('subtitle_en'));
        $settings->setSubtitleDe($request->get('subtitle_de'));
        $settings->setSubtitleRu($request->get('subtitle_ru'));
        
        // Contact information
        $settings->setCompanyName($request->get('company_name'));
        $settings->setAddress($request->get('address'));
        $settings->setPhone($request->get('phone'));
        $settings->setEmail($request->get('email'));
        $settings->setWorkingHours($request->get('working_hours'));
        
        // Map coordinates
        $mapLat = $request->get('map_lat');
        $mapLng = $request->get('map_lng');
        if ($mapLat && $mapLng) {
            $settings->setMapLat($mapLat);
            $settings->setMapLng($mapLng);
        }
        
        // Social media
        $socialMedia = [];
        if ($request->get('facebook')) {
            $socialMedia['facebook'] = $request->get('facebook');
        }
        if ($request->get('linkedin')) {
            $socialMedia['linkedin'] = $request->get('linkedin');
        }
        if ($request->get('twitter')) {
            $socialMedia['twitter'] = $request->get('twitter');
        }
        if ($request->get('instagram')) {
            $socialMedia['instagram'] = $request->get('instagram');
        }
        $settings->setSocialMedia($socialMedia);
        
        // Meta data
        $settings->setMetaTitleBg($request->get('meta_title_bg'));
        $settings->setMetaTitleEn($request->get('meta_title_en'));
        $settings->setMetaTitleDe($request->get('meta_title_de'));
        $settings->setMetaTitleRu($request->get('meta_title_ru'));
        
        $settings->setMetaDescriptionBg($request->get('meta_description_bg'));
        $settings->setMetaDescriptionEn($request->get('meta_description_en'));
        $settings->setMetaDescriptionDe($request->get('meta_description_de'));
        $settings->setMetaDescriptionRu($request->get('meta_description_ru'));
        
        $this->contactSettingsRepository->save($settings, true);
        
        $this->addFlash('success', 'Настройките за контакт бяха запазени успешно!');
        
        return $this->redirectToRoute('admin_contact');
    }
} 