<?php

namespace App\Entity;

use App\Repository\ApiSettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApiSettingsRepository::class)]
#[ORM\Table(name: 'api_settings')]
class ApiSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $facebookAppId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $facebookAppSecret = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $facebookPageId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $facebookPageAccessToken = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkedinAccessToken = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkedinOrganizationId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFacebookAppId(): ?string
    {
        return $this->facebookAppId;
    }

    public function setFacebookAppId(?string $facebookAppId): self
    {
        $this->facebookAppId = $facebookAppId;
        return $this;
    }

    public function getFacebookAppSecret(): ?string
    {
        return $this->facebookAppSecret;
    }

    public function setFacebookAppSecret(?string $facebookAppSecret): self
    {
        $this->facebookAppSecret = $facebookAppSecret;
        return $this;
    }

    public function getFacebookPageId(): ?string
    {
        return $this->facebookPageId;
    }

    public function setFacebookPageId(?string $facebookPageId): self
    {
        $this->facebookPageId = $facebookPageId;
        return $this;
    }

    public function getFacebookPageAccessToken(): ?string
    {
        return $this->facebookPageAccessToken;
    }

    public function setFacebookPageAccessToken(?string $facebookPageAccessToken): self
    {
        $this->facebookPageAccessToken = $facebookPageAccessToken;
        return $this;
    }

    public function getLinkedinAccessToken(): ?string
    {
        return $this->linkedinAccessToken;
    }

    public function setLinkedinAccessToken(?string $linkedinAccessToken): self
    {
        $this->linkedinAccessToken = $linkedinAccessToken;
        return $this;
    }

    public function getLinkedinOrganizationId(): ?string
    {
        return $this->linkedinOrganizationId;
    }

    public function setLinkedinOrganizationId(?string $linkedinOrganizationId): self
    {
        $this->linkedinOrganizationId = $linkedinOrganizationId;
        return $this;
    }
} 