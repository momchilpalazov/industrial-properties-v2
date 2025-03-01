<?php

namespace App\Service;

use App\Entity\Offer;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;

class OfferPdfGenerator
{
    private $twig;
    private $params;

    public function __construct(Environment $twig, ParameterBagInterface $params)
    {
        $this->twig = $twig;
        $this->params = $params;
    }

    public function generatePdf(Offer $offer): string
    {
        // Конфигурация на Dompdf
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->setIsRemoteEnabled(true);

        // Създаване на Dompdf инстанция
        $dompdf = new Dompdf($options);

        // Генериране на HTML съдържание
        $html = $this->twig->render('admin/offer/pdf/template.html.twig', [
            'offer' => $offer
        ]);

        // Зареждане на HTML в Dompdf
        $dompdf->loadHtml($html);

        // Задаване на размер на страницата
        $dompdf->setPaper('A4', 'portrait');

        // Рендериране на PDF
        $dompdf->render();

        // Генериране на уникално име на файла
        $fileName = sprintf('offer_%s_%s.pdf', 
            $offer->getId(),
            (new \DateTime())->format('Y-m-d_H-i-s')
        );

        // Запазване на файла
        $pdfPath = $this->params->get('kernel.project_dir') . '/public/uploads/offers/' . $fileName;
        file_put_contents($pdfPath, $dompdf->output());

        // Обновяване на пътя в офертата
        $offer->setPdfPath('/uploads/offers/' . $fileName);

        return $pdfPath;
    }
} 