<?php

namespace App\Entity;

use App\Repository\SettingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
#[ORM\Table(name: 'settings')]
class Setting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $key = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private $value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function getValue()
    {
        if (is_string($this->value) && $this->isJson($this->value)) {
            return json_decode($this->value, true);
        }
        
        return $this->value;
    }

    public function setValue($value): self
    {
        if (is_array($value) || is_object($value)) {
            $this->value = json_encode($value);
        } else {
            $this->value = $value;
        }

        return $this;
    }
    
    private function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
} 