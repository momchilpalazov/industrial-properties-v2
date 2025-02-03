<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

abstract class AbstractController
{
    protected Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    protected function render(string $template, array $parameters = []): Response
    {
        $content = $this->twig->render($template, $parameters);
        return new Response($content);
    }

    protected function json(array $data, int $status = 200): Response
    {
        return new Response(
            json_encode($data),
            $status,
            ['Content-Type' => 'application/json']
        );
    }

    protected function redirectToRoute(string $route, array $parameters = []): Response
    {
        // TODO: Implement route generation
        return new Response('', 302, ['Location' => $route]);
    }
} 