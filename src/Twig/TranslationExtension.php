<?php

namespace App\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TranslationExtension extends AbstractExtension
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('current_language', [$this, 'getCurrentLanguage']),
            new TwigFunction('language_name', [$this, 'getLanguageName']),
            new TwigFunction('available_languages', [$this, 'getAvailableLanguages']),
        ];
    }

    public function getCurrentLanguage(): string
    {
        return $this->requestStack->getCurrentRequest()->getLocale();
    }

    public function getLanguageName(string $code): string
    {
        return match ($code) {
            'bg' => 'Български',
            'en' => 'English',
            'de' => 'Deutsch',
            'ru' => 'Русский',
            default => $code,
        };
    }

    public function getAvailableLanguages(): array
    {
        return [
            'bg' => [
                'code' => 'bg',
                'name' => 'Български',
                'flag' => '🇧🇬'
            ],
            'en' => [
                'code' => 'en',
                'name' => 'English',
                'flag' => '🇬🇧'
            ],
            'de' => [
                'code' => 'de',
                'name' => 'Deutsch',
                'flag' => '🇩🇪'
            ],
            'ru' => [
                'code' => 'ru',
                'name' => 'Русский',
                'flag' => '🇷🇺'
            ]
        ];
    }
} 