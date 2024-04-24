<?php
// src/Controller/InvoiceController.php

namespace App\Controller;


use App\Entity\User;

use Exception;
use App\Entity\Operation;
use App\Service\LogsService;
use App\Service\InvoiceService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security; 


class InvoiceController extends AbstractController
{
    private InvoiceService $invoiceService;
    private $security;

    public function __construct(InvoiceService $invoiceService, Security $security) {
        $this->invoiceService = $invoiceService;
        $this->security = $security;
    }

    #[Route('/operation/{id}/download-invoice', name: 'operation_download_invoice')]

    public function downloadInvoice(Operation $operation, LogsService $logsService): Response {
        $user = $this->getUser();
       
        if (!$this->isUserAllowedToViewOperation($user, $operation)) {
            return $this->redirectToRoute('admin'); 
        }

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
            'user' => 'Anonymous',
            'message' => 'User downloaded invoice',
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
    private function isUserAllowedToViewOperation($user, Operation $operation): bool {
        // Vérifier si l'utilisateur a le rôle admin
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }
    
        // Vérifier si l'utilisateur est le client ou le salarié associé à l'opération
        return $operation->getCustomer() === $user || $operation->getSalarie() === $user;
    }
    
}
