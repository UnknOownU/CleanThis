<?php

namespace App\Controller;

use App\Repository\OperationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class StatistiqueController extends AbstractController
{
    #[Route('/stats', name: 'stats')]
    public function statistiques(OperationRepository $operationRepository): Response
    {
            try {
                $this->denyAccessUnlessGranted('ROLE_ADMIN');
            } catch (AccessDeniedException $e) {
                return $this->redirectToRoute('admin');
            }
        $chiffreAffairesMoisEnCours = $operationRepository->findSalesForCurrentMonth();
         // Récupérer les missions en coursdes salariés 
        $totalMissionsEnCours = $operationRepository->countMissionsEnCours();

         // Récupérer les missions terminées des salariés 
        $operationsTerminees = $operationRepository->countOperationsTerminees();

        // Récupérer les statistiques des salariés en fonction du nombre de missions
        $statistiquesSalaries = $operationRepository->getMissionStatistics();

        // Récupérer les statistiques des ventes par type d'opération
        $statistiquesOperations = $operationRepository->findByOperationTypeStatistics();

        // Récupérer le chiffre d'affaires total
        $chiffreAffaires = $operationRepository->getTotalSales();

        // Récupérer les données du chiffre d'affaires par date
        $chiffreAffairesParDate = $operationRepository->getSalesByDate();
        
        // Récupérer les statistiques des missions par statut
        $missionStatusStatistics = $operationRepository->findMissionStatusStatistics();
        

        // Récupérer les salariés sur le podium
        $podiumEmployees = $operationRepository->getPodiumEmployees();

        // Récupérer les missions en cours
        $missionsEnCours = $operationRepository->countMissionsEnCours();

        // Passer les données récupérées à la vue Twig
        return $this->render('statistics/stats.html.twig', [
            'totalMissionsEnCours' => $totalMissionsEnCours,
            'statistiquesSalaries' => $statistiquesSalaries,
            'statistiquesOperations' => $statistiquesOperations,
            'chiffreAffairesParDate' => $chiffreAffairesParDate,
            'missionStatusStatistics' => $missionStatusStatistics,
            'chiffreAffaires' => $chiffreAffaires, 
            'podiumEmployees' => $podiumEmployees,
            'missionsEnCours' => $missionsEnCours,
            'operationsTerminees' => $operationsTerminees,
            'chiffreAffairesMoisEnCours' => $chiffreAffairesMoisEnCours,
        ]);
    }
}
