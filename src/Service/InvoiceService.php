<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Operation;
use Twig\Environment;
use Symfony\Component\String\Slugger\SluggerInterface;

class InvoiceService {
    private Environment $twig;
    private string $projectDir;
    private SluggerInterface $slugger;

    // Ajoutez le constructeur avec $projectDir
    public function __construct(Environment $twig, string $projectDir, SluggerInterface $slugger) {
        $this->twig = $twig;
        $this->projectDir = $projectDir;
        $this->slugger = $slugger;
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

    public function generateInvoiceMail(Operation $operation): string {
        // Generate a unique filename for the PDF
        $pdfFilename = $this->slugger->slug($operation->getId()) . '.pdf';
    
        // Obtain the full path to the logo file
        $logoPath = $this->projectDir . '/public/images/logo.png';
    
        // Check if the file exists
        if (!file_exists($logoPath)) {
            throw new \Exception('Le fichier logo n\'existe pas.');
        }
    
        // Convert the image to base64
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoBase64 = 'data:image/png;base64,' . $logoData;
    
        // Get the directory where PDFs will be stored
        $pdfPath = $this->projectDir . '/var/pdf/';
    
        // Create the directory if it doesn't exist
        if (!file_exists($pdfPath)) {
            mkdir($pdfPath, 0755, true);
        }
    
        // Generate the HTML for the invoice
        $html = $this->twig->render('invoice/invoice_template.html.twig', [
            'operation' => $operation,
            'logo_base64' => $logoBase64, 
        ]);
    
        // Configure and render the PDF
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
    
        // Save the PDF to the filesystem
        $output = $dompdf->output();
        file_put_contents($pdfPath . $pdfFilename, $output);
    
        // Return the path to the generated PDF
        return $pdfPath . $pdfFilename;
    }
}
