<?php
// src/Controller/InvoiceController.php

namespace App\Controller;

use Exception;
use App\Entity\Operation;
use App\Service\LogsService;
use App\Service\InvoiceService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InvoiceController extends AbstractController
{
    private InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService) {
        $this->invoiceService = $invoiceService;
    }

    #[Route('/operation/{id}/download-invoice', name: 'operation_download_invoice')]
    public function downloadInvoice(Operation $operation, LogsService $logsService): Response {
        // Vérifiez que l'opération est terminée avant de générer la facture
        if ($operation->getStatus() !== 'Terminée') {
            throw $this->createNotFoundException('La facture n\'est pas disponible.');
        }

        $customer = $operation->getCustomer();
        $user = $customer->getEmail();
        
        // Log download invoice
        try {
            $logsService->postLog([
            'loggerName' => 'Operation',
            'user' => $user,
            'message' => 'User consulted invoice',
            'level' => 'info'
        ]);
        } catch (Exception $e) {
        }
        
        $pdfContent = $this->invoiceService->generateInvoice($operation);

        // Retournez une réponse avec le contenu du PDF et les en-têtes appropriés
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="facture-operation-' . $operation->getId() . '.pdf"',
        ]);
    }
}
