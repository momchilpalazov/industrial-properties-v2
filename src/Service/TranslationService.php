<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationService
{
    private $translator;
    private $requestStack;
    private $supportedLocales = ['bg', 'en', 'de', 'ru'];
    private $defaultLocale = 'bg';

    public function __construct(
        TranslatorInterface $translator,
        RequestStack $requestStack
    ) {
        $this->translator = $translator;
        $this->requestStack = $requestStack;
    }

    public function getCurrentLocale(): string
    {
        return $this->requestStack->getCurrentRequest()?->getLocale() ?? $this->defaultLocale;
    }

    public function getSupportedLocales(): array
    {
        return $this->supportedLocales;
    }

    public function getLocaleNames(): array
    {
        return [
            'bg' => 'Български',
            'en' => 'English',
            'de' => 'Deutsch',
            'ru' => 'Русский'
        ];
    }

    public function translate(string $key, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $this->translator->trans($key, $parameters, $domain, $locale);
    }

    public function translateToAll(string $key, array $parameters = [], string $domain = null): array
    {
        $translations = [];
        foreach ($this->supportedLocales as $locale) {
            $translations[$locale] = $this->translate($key, $parameters, $domain, $locale);
        }
        return $translations;
    }

    public function isValidLocale(string $locale): bool
    {
        return in_array($locale, $this->supportedLocales);
    }

    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }

    public function getLocalizedUrl(string $currentUrl, string $targetLocale): string
    {
        $parsedUrl = parse_url($currentUrl);
        $query = [];
        
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $query);
        }
        
        $query['lang'] = $targetLocale;
        
        $newQuery = http_build_query($query);
        
        return $parsedUrl['path'] . ($newQuery ? '?' . $newQuery : '');
    }
} 