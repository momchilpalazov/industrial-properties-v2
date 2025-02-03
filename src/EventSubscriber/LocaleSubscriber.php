<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    private string $defaultLocale;

    public function __construct(string $defaultLocale = 'bg')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        
        // Опитваме се да вземем локала от различни източници
        $locale = $request->query->get('lang') // От URL параметър
            ?? $request->getSession()->get('_locale') // От сесията
            ?? $request->getPreferredLanguage(['bg', 'en', 'de', 'ru']) // От браузъра
            ?? $this->defaultLocale; // Или използваме default

        // Валидираме локала
        if (!in_array($locale, ['bg', 'en', 'de', 'ru'])) {
            $locale = $this->defaultLocale;
        }

        // Запазваме локала в сесията
        $request->getSession()->set('_locale', $locale);
        
        // Задаваме локала за текущата заявка
        $request->setLocale($locale);
    }
} 