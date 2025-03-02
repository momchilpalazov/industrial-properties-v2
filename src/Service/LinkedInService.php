<?php

namespace App\Service;

use App\Entity\Property;
use App\Entity\SocialPost;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LinkedInService
{
    private Client $client;
    private EntityManagerInterface $entityManager;
    private UrlGeneratorInterface $urlGenerator;
    private string $accessToken;
    private string $organizationId;

    public function __construct(
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        ParameterBagInterface $params
    ) {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->accessToken = $params->get('linkedin_access_token');
        $this->organizationId = $params->get('linkedin_organization_id');

        $this->client = new Client([
            'base_uri' => 'https://api.linkedin.com/v2/',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'X-Restli-Protocol-Version' => '2.0.0',
            ]
        ]);
    }

    public function shareProperty(Property $property): ?SocialPost
    {
        try {
            // Първо качваме изображението
            $imageUrn = $this->uploadImage($property);

            // Подготвяме текста на публикацията
            $text = $this->prepareMessage($property);

            // Създаваме публикацията с изображение
            $response = $this->client->post('ugcPosts', [
                'json' => [
                    'author' => 'urn:li:organization:' . $this->organizationId,
                    'lifecycleState' => 'PUBLISHED',
                    'specificContent' => [
                        'com.linkedin.ugc.ShareContent' => [
                            'shareMediaCategory' => 'IMAGE',
                            'media' => [
                                [
                                    'status' => 'READY',
                                    'media' => $imageUrn,
                                ]
                            ],
                            'shareCommentary' => [
                                'text' => $text
                            ],
                            'visibility' => [
                                'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC'
                            ]
                        ]
                    ]
                ]
            ]);

            if ($response->getStatusCode() === 201) {
                $postData = json_decode($response->getBody()->getContents(), true);
                $postId = $postData['id'] ?? null;

                // Запазваме информацията за публикацията
                $post = new SocialPost();
                $post->setProperty($property)
                    ->setPlatform('linkedin')
                    ->setPostId($postId)
                    ->setStatus('success');

                $this->entityManager->persist($post);
                $this->entityManager->flush();

                return $post;
            }

            throw new \Exception('Неуспешно публикуване в LinkedIn');
        } catch (\Exception $e) {
            // При грешка създаваме запис със статус failed
            $post = new SocialPost();
            $post->setProperty($property)
                ->setPlatform('linkedin')
                ->setStatus('failed');

            $this->entityManager->persist($post);
            $this->entityManager->flush();

            throw $e;
        }
    }

    private function uploadImage(Property $property): string
    {
        if (!$property->getMainImage()) {
            throw new \RuntimeException('Имотът няма главна снимка');
        }

        $imageUrl = $this->urlGenerator->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL)
            . 'uploads/images/'
            . $property->getMainImage()->getFilename();

        // Регистрираме качването на изображението
        $registerResponse = $this->client->post('assets?action=registerUpload', [
            'json' => [
                'registerUploadRequest' => [
                    'recipes' => ['urn:li:digitalmediaRecipe:feedshare-image'],
                    'owner' => 'urn:li:organization:' . $this->organizationId,
                    'serviceRelationships' => [
                        [
                            'relationshipType' => 'OWNER',
                            'identifier' => 'urn:li:userGeneratedContent'
                        ]
                    ]
                ]
            ]
        ]);

        $uploadData = json_decode($registerResponse->getBody()->getContents(), true);
        $uploadUrl = $uploadData['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'];
        $asset = $uploadData['value']['asset'];

        // Качваме изображението
        $imageContent = file_get_contents($imageUrl);
        $this->client->put($uploadUrl, [
            'headers' => [
                'Content-Type' => 'image/jpeg'
            ],
            'body' => $imageContent
        ]);

        return $asset;
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
} 