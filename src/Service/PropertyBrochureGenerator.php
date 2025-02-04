<?php

namespace App\Service;

use App\Entity\Property;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class PropertyBrochureGenerator
{
    public function __construct(
        private Environment $twig,
        private TranslatorInterface $translator,
        private string $publicDir
    ) {}

    public function generatePdf(Property $property, string $locale): Response
    {
        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        
        $dompdf = new Dompdf($options);
        
        // Get property images
        $images = [];
        foreach ($property->getImages() as $image) {
            $images[] = base64_encode(file_get_contents(
                $this->publicDir . '/uploads/properties/' . $image->getFilename()
            ));
        }
        
        // Render template
        $html = $this->twig->render('pdf/property_brochure.html.twig', [
            'property' => $property,
            'images' => $images,
            'locale' => $locale
        ]);
        
        // Generate PDF
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Create response
        $response = new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . 
                    $this->translator->trans('property.brochure.filename', [
                        '%property%' => $property->getTitle()
                    ]) . '.pdf"'
            ]
        );
        
        return $response;
    }
} 