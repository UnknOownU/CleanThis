<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OperationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatistiqueController extends AbstractController
{
    #[Route('/stats', name: 'stats')]
    public function statistiques(OperationRepository $operationRepository): Response
    {
        // Récupérer les statistiques des salariés en fonction du nombre de missions
        $statistiquesSalaries = $operationRepository->getMissionStatistics();

        // Récupérer les statistiques des ventes par type d'opération
        $statistiquesOperations = $operationRepository->findByOperationTypeStatistics();

        // Récupérer le chiffre d'affaires total
        $chiffreAffaires = $operationRepository->getTotalSales();

        // Récupérer les données du chiffre d'affaires par date
        $chiffreAffairesParDate = $operationRepository->getSalesByDate();
        dump($chiffreAffairesParDate);
        // Récupérer les statistiques des missions par statut
        $missionStatusStatistics = $operationRepository->findMissionStatusStatistics();


        
        // Récupérer les salariés sur le podium
         $podiumEmployees = $operationRepository->getPodiumEmployees();

        // Passer les données récupérées à la vue Twig
        return $this->render('statistics/stats.html.twig', [
            'statistiquesSalaries' => $statistiquesSalaries,
            'statistiquesOperations' => $statistiquesOperations,
            'chiffreAffairesParDate' => $chiffreAffairesParDate,
            'missionStatusStatistics' => $missionStatusStatistics,
            'chiffreAffaires' => $chiffreAffaires, 
            'podiumEmployees' => $podiumEmployees,
            
        ]);
    }
}
