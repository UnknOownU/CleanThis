<?php
// src/Service/InvoiceService.php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Operation;
use Twig\Environment;

class InvoiceService {
    private Environment $twig;

    public function __construct(Environment $twig) {
        $this->twig = $twig;
    }

    public function generateInvoice(Operation $operation): string {
        // Vous avez maintenant directement accès aux données de l'opération grâce à l'objet $operation
        $html = $this->twig->render('invoice/invoice_template.html.twig', [
            'operation' => $operation,
        ]);
    
        // Instancier et configurer DOMPDF
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
    
        return $dompdf->output();
    }
    
}
