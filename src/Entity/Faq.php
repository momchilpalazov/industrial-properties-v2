<?php

namespace App\Entity;

use App\Repository\FaqRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FaqRepository::class)]
#[ORM\Table(name: 'faqs')]
class Faq
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Моля въведете въпрос на български')]
    private ?string $questionBg = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Моля въведете въпрос на английски')]
    private ?string $questionEn = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Моля въведете отговор на български')]
    private ?string $answerBg = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Моля въведете отговор на английски')]
    private ?string $answerEn = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Моля изберете категория')]
    private ?string $category = null;

    #[ORM\Column]
    private ?int $position = 0;

    #[ORM\Column]
    private bool $isActive = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestionBg(): ?string
    {
        return $this->questionBg;
    }

    public function setQuestionBg(string $questionBg): self
    {
        $this->questionBg = $questionBg;
        return $this;
    }

    public function getQuestionEn(): ?string
    {
        return $this->questionEn;
    }

    public function setQuestionEn(string $questionEn): self
    {
        $this->questionEn = $questionEn;
        return $this;
    }

    public function getAnswerBg(): ?string
    {
        return $this->answerBg;
    }

    public function setAnswerBg(string $answerBg): self
    {
        $this->answerBg = $answerBg;
        return $this;
    }

    public function getAnswerEn(): ?string
    {
        return $this->answerEn;
    }

    public function setAnswerEn(string $answerEn): self
    {
        $this->answerEn = $answerEn;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getQuestion(string $locale = 'bg'): ?string
    {
        return $locale === 'bg' ? $this->questionBg : $this->questionEn;
    }

    public function getAnswer(string $locale = 'bg'): ?string
    {
        return $locale === 'bg' ? $this->answerBg : $this->answerEn;
    }
} 