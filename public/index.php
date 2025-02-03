<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Kernel;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Create and boot kernel
$kernel = new Kernel($_ENV['APP_ENV'] ?? 'prod');
$kernel->boot();

// Handle request
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();