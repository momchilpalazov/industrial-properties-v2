<?php

namespace App\Twig;

use App\Service\TranslationService;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TranslationExtension extends AbstractExtension
{
    private $translationService;
    private $requestStack;

    public function __construct(
        TranslationService $translationService,
        RequestStack $requestStack
    ) {
        $this->translationService = $translationService;
        $this->requestStack = $requestStack;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_locale', [$this, 'getCurrentLocale']),
            new TwigFunction('get_locale_name', [$this, 'getLocaleName']),
            new TwigFunction('get_supported_locales', [$this, 'getSupportedLocales']),
            new TwigFunction('get_locale_names', [$this, 'getLocaleNames']),
            new TwigFunction('get_localized_url', [$this, 'getLocalizedUrl']),
            new TwigFunction('translate_to_all', [$this, 'translateToAll']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('locale_name', [$this, 'getLocaleName']),
        ];
    }

    public function getCurrentLocale(): string
    {
        return $this->translationService->getCurrentLocale();
    }

    public function getLocaleName(string $locale): string
    {
        return $this->translationService->getLocaleNames()[$locale] ?? $locale;
    }

    public function getSupportedLocales(): array
    {
        return $this->translationService->getSupportedLocales();
    }

    public function getLocaleNames(): array
    {
        return $this->translationService->getLocaleNames();
    }

    public function getLocalizedUrl(string $targetLocale): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return '';
        }

        return $this->translationService->getLocalizedUrl(
            $request->getRequestUri(),
            $targetLocale
        );
    }

    public function translateToAll(string $key, array $parameters = [], string $domain = null): array
    {
        return $this->translationService->translateToAll($key, $parameters, $domain);
    }
} 