<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Operation;
use Twig\Environment;

class InvoiceService {
    private Environment $twig;
    private string $projectDir;

    // Ajoutez le constructeur avec $projectDir
    public function __construct(Environment $twig, string $projectDir) {
        $this->twig = $twig;
        $this->projectDir = $projectDir;
    }

    public function generateInvoice(Operation $operation): string {
        // Obtenez le chemin complet du fichier logo
        $logoPath = $this->projectDir . '/public/images/logo.png';

        // Vérifiez si le fichier existe
        if (!file_exists($logoPath)) {
            throw new \Exception('Le fichier logo n\'existe pas.');
        }

        // Convertissez l'image en chaîne encodée en base64
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoBase64 = 'data:image/png;base64,' . $logoData;

        // Passez l'image encodée en base64 à votre template Twig
        $html = $this->twig->render('invoice/invoice_template.html.twig', [
            'operation' => $operation,
            'logo_base64' => $logoBase64,
        ]);

        // Configurez dompdf et générez le PDF
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Retournez le PDF généré
        return $dompdf->output();
    }
}