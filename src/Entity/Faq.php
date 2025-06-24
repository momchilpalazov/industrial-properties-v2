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
    private ?int $id = null;    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Моля въведете въпрос на български')]
    private ?string $questionBg = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Моля въведете въпрос на английски')]
    private ?string $questionEn = null;

    #[ORM\Column(name: 'question_de', length: 255)]
    #[Assert\NotBlank(message: 'Моля въведете въпрос на немски')]
    private ?string $questionDe = null;

    #[ORM\Column(name: 'question_ru', length: 255)]
    #[Assert\NotBlank(message: 'Моля въведете въпрос на руски')]
    private ?string $questionRu = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Моля въведете отговор на български')]
    private ?string $answerBg = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Моля въведете отговор на английски')]
    private ?string $answerEn = null;

    #[ORM\Column(name: 'answer_de', type: 'text')]
    #[Assert\NotBlank(message: 'Моля въведете отговор на немски')]
    private ?string $answerDe = null;

    #[ORM\Column(name: 'answer_ru', type: 'text')]
    #[Assert\NotBlank(message: 'Моля въведете отговор на руски')]
    private ?string $answerRu = null;#[ORM\ManyToOne(targetEntity: FaqCategory::class, inversedBy: 'faqs')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id')]
    #[Assert\NotNull(message: 'Моля изберете категория')]
    private ?FaqCategory $category = null;

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
    }    public function getQuestionEn(): ?string
    {
        return $this->questionEn;
    }

    public function setQuestionEn(string $questionEn): self
    {
        $this->questionEn = $questionEn;
        return $this;
    }

    public function getQuestionDe(): ?string
    {
        return $this->questionDe;
    }

    public function setQuestionDe(string $questionDe): self
    {
        $this->questionDe = $questionDe;
        return $this;
    }

    public function getQuestionRu(): ?string
    {
        return $this->questionRu;
    }

    public function setQuestionRu(string $questionRu): self
    {
        $this->questionRu = $questionRu;
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
    }    public function getAnswerEn(): ?string
    {
        return $this->answerEn;
    }

    public function setAnswerEn(string $answerEn): self
    {
        $this->answerEn = $answerEn;
        return $this;
    }

    public function getAnswerDe(): ?string
    {
        return $this->answerDe;
    }

    public function setAnswerDe(string $answerDe): self
    {
        $this->answerDe = $answerDe;
        return $this;
    }

    public function getAnswerRu(): ?string
    {
        return $this->answerRu;
    }

    public function setAnswerRu(string $answerRu): self
    {
        $this->answerRu = $answerRu;
        return $this;
    }public function getCategory(): ?FaqCategory
    {
        return $this->category;
    }

    public function setCategory(?FaqCategory $category): self
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
    }    public function getQuestion(string $locale = 'bg'): ?string
    {
        return match ($locale) {
            'en' => $this->questionEn,
            'de' => $this->questionDe,
            'ru' => $this->questionRu,
            default => $this->questionBg,
        };
    }

    public function getAnswer(string $locale = 'bg'): ?string
    {
        return match ($locale) {
            'en' => $this->answerEn,
            'de' => $this->answerDe,
            'ru' => $this->answerRu,
            default => $this->answerBg,
        };
    }
} 