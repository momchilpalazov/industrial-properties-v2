<?php

namespace App\Controller;

use App\Entity\Property;
use App\Service\PropertyBrochureGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PropertyBrochureController extends AbstractController
{
    #[Route('/property/{id}/brochure', name: 'app_property_brochure')]
    public function generate(
        Request $request,
        Property $property,
        PropertyBrochureGenerator $brochureGenerator
    ): Response {
        return $brochureGenerator->generatePdf(
            $property,
            $request->getLocale()
        );
    }
} 