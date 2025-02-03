<?php

declare(strict_types=1);

namespace App;

use App\Controller\HomeController;
use App\Controller\PropertyController;
use App\Controller\BlogController;
use App\Controller\ContactController;
use App\Repository\PropertyRepository;
use App\Repository\BlogRepository;
use App\Repository\ContactRepository;
use App\Service\PropertyService;
use App\Service\BlogService;
use App\Service\ContactService;
use App\Service\FileUploadService;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Kernel
{
    private string $environment;
    private ContainerBuilder $container;
    private RouteCollection $routes;

    public function __construct(string $environment)
    {
        $this->environment = $environment;
        $this->container = new ContainerBuilder();
        $this->routes = new RouteCollection();
    }

    public function boot(): void
    {
        $this->registerServices();
        $this->registerRoutes();
        $this->container->compile();
    }

    public function handle(Request $request): Response
    {
        $context = new RequestContext();
        $context->fromRequest($request);

        $matcher = new UrlMatcher($this->routes, $context);
        
        try {
            $attributes = $matcher->match($request->getPathInfo());
            $controller = $attributes['_controller'];
            unset($attributes['_controller'], $attributes['_route']);
            
            return $this->container->get($controller)->__invoke($request, $attributes);
        } catch (\Exception $e) {
            return new Response('Page not found', Response::HTTP_NOT_FOUND);
        }
    }

    private function registerServices(): void
    {
        // Database
        $this->container->register('database.connection', Connection::class)
            ->setFactory([DriverManager::class, 'getConnection'])
            ->setArguments([[
                'dbname' => $_ENV['DB_NAME'],
                'user' => $_ENV['DB_USER'],
                'password' => $_ENV['DB_PASSWORD'],
                'host' => $_ENV['DB_HOST'],
                'driver' => 'pdo_mysql',
            ]]);

        // Twig
        $this->container->register('twig.loader', FilesystemLoader::class)
            ->setArguments([__DIR__ . '/../templates']);

        $this->container->register('twig', Environment::class)
            ->setArguments([new Reference('twig.loader')])
            ->addMethodCall('addGlobal', ['environment', $this->environment]);

        // File Upload Service
        $this->container->register('service.file_upload', FileUploadService::class)
            ->setArguments([
                $_ENV['UPLOAD_DIR'],
                (int) $_ENV['MAX_FILE_SIZE']
            ]);

        // Repositories
        $this->container->register('repository.property', PropertyRepository::class)
            ->setArguments([new Reference('database.connection')]);

        $this->container->register('repository.blog', BlogRepository::class)
            ->setArguments([new Reference('database.connection')]);

        $this->container->register('repository.contact', ContactRepository::class)
            ->setArguments([new Reference('database.connection')]);

        // Services
        $this->container->register('service.property', PropertyService::class)
            ->setArguments([
                new Reference('repository.property'),
                new Reference('service.file_upload')
            ]);

        $this->container->register('service.blog', BlogService::class)
            ->setArguments([new Reference('repository.blog')]);

        $this->container->register('service.contact', ContactService::class)
            ->setArguments([new Reference('repository.contact')]);

        // Controllers
        $this->container->register('controller.home', HomeController::class)
            ->setArguments([
                new Reference('twig'),
                new Reference('service.property'),
                new Reference('service.blog')
            ]);

        $this->container->register('controller.property', PropertyController::class)
            ->setArguments([
                new Reference('twig'),
                new Reference('service.property')
            ]);

        $this->container->register('controller.blog', BlogController::class)
            ->setArguments([
                new Reference('twig'),
                new Reference('service.blog')
            ]);

        $this->container->register('controller.contact', ContactController::class)
            ->setArguments([
                new Reference('twig'),
                new Reference('service.contact')
            ]);
    }

    private function registerRoutes(): void
    {
        // Home page
        $this->routes->add('home', new Route(
            '/',
            ['_controller' => 'controller.home']
        ));

        // Properties
        $this->routes->add('properties', new Route(
            '/properties',
            ['_controller' => 'controller.property', '_method' => 'index']
        ));

        $this->routes->add('property_show', new Route(
            '/property/{id}',
            ['_controller' => 'controller.property', '_method' => 'show'],
            ['id' => '\d+']
        ));

        $this->routes->add('property_contact', new Route(
            '/property/{id}/contact',
            ['_controller' => 'controller.property', '_method' => 'contact'],
            ['id' => '\d+']
        ));

        // Blog
        $this->routes->add('blog', new Route(
            '/blog',
            ['_controller' => 'controller.blog', '_method' => 'index']
        ));

        $this->routes->add('blog_show', new Route(
            '/blog/{slug}',
            ['_controller' => 'controller.blog', '_method' => 'show'],
            ['slug' => '[a-z0-9\-]+']
        ));

        $this->routes->add('blog_comment', new Route(
            '/blog/post/{postId}/comment',
            ['_controller' => 'controller.blog', '_method' => 'comment'],
            ['postId' => '\d+']
        ));

        // Contact
        $this->routes->add('contact', new Route(
            '/contact',
            ['_controller' => 'controller.contact', '_method' => 'index']
        ));

        $this->routes->add('contact_send', new Route(
            '/contact/send',
            ['_controller' => 'controller.contact', '_method' => 'send']
        ));
    }
} 