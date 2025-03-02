<?php

namespace App\Service;

use App\Entity\Property;
use App\Entity\SocialPost;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\Facebook;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FacebookService
{
    private Facebook $facebook;
    private EntityManagerInterface $entityManager;
    private UrlGeneratorInterface $urlGenerator;
    private string $pageId;
    private string $pageAccessToken;

    public function __construct(
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        ParameterBagInterface $params
    ) {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;

        // Facebook SDK инициализация
        $this->facebook = new Facebook([
            'app_id' => $params->get('facebook_app_id'),
            'app_secret' => $params->get('facebook_app_secret'),
            'default_graph_version' => 'v19.0',
        ]);

        $this->pageId = $params->get('facebook_page_id');
        $this->pageAccessToken = $params->get('facebook_page_access_token');
        $this->facebook->setDefaultAccessToken($this->pageAccessToken);
    }

    public function shareProperty(Property $property): ?SocialPost
    {
        try {
            // Подготвяме съдържанието за публикацията
            $message = $this->prepareMessage($property);
            $imageUrl = $this->getPropertyMainImageUrl($property);
            
            // Създаваме Facebook публикация
            $response = $this->facebook->post("/{$this->pageId}/photos", [
                'message' => $message,
                'url' => $imageUrl,
            ]);

            // Запазваме информацията за публикацията
            $post = new SocialPost();
            $post->setProperty($property)
                ->setPlatform('facebook')
                ->setPostId($response->getGraphNode()->getField('id'))
                ->setStatus('success');

            $this->entityManager->persist($post);
            $this->entityManager->flush();

            return $post;
        } catch (\Exception $e) {
            // При грешка създаваме запис със статус failed
            $post = new SocialPost();
            $post->setProperty($property)
                ->setPlatform('facebook')
                ->setStatus('failed');

            $this->entityManager->persist($post);
            $this->entityManager->flush();

            throw $e;
        }
    }

    private function prepareMessage(Property $property): string
    {
        $message = "🏢 {$property->getTitleBg()}\n\n";
        $message .= "📍 {$property->getLocationBg()}\n";
        $message .= "🏗️ {$property->getArea()} m²\n";
        
        if ($property->getPrice()) {
            $message .= "💰 " . number_format($property->getPrice(), 0, '.', ' ') . " €\n\n";
        }

        // Добавяме първите 200 символа от описанието
        $description = strip_tags($property->getDescriptionBg());
        $message .= mb_substr($description, 0, 200) . "...\n\n";

        // Добавяме характеристики
        if ($property->getFeatures()) {
            $message .= "✨ Характеристики:\n";
            foreach ($property->getFeatures() as $feature) {
                $message .= "✓ {$feature}\n";
            }
            $message .= "\n";
        }

        // Добавяме линк към имота
        $url = $this->urlGenerator->generate('property_show', [
            'id' => $property->getId()
        ], UrlGeneratorInterface::ABSOLUTE_URL);
        
        $message .= "🔎 Вижте повече на: {$url}";

        return $message;
    }

    private function getPropertyMainImageUrl(Property $property): string
    {
        if (!$property->getMainImage()) {
            throw new \RuntimeException('Property has no main image');
        }

        return $this->urlGenerator->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL)
            . 'uploads/images/'
            . $property->getMainImage()->getFilename();
    }
} 