<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OperationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\VarDumper\VarDumper; // Import de la classe VarDumper

class StatistiqueController extends AbstractController
{
    #[Route('/stats', name: 'stats')]
    public function statistiques(OperationRepository $operationRepository): Response
    {
        // Récupérer le salarié avec le plus de missions depuis le repository
        $salariePlusDeMissions = $operationRepository->findSalarieWithMostMissions();
        VarDumper::dump($salariePlusDeMissions);
        // Récupérer les statistiques des salariés en fonction du nombre de missions
        $statistiquesSalaries = $operationRepository->getMissionStatistics();
        VarDumper::dump($statistiquesSalaries);
        // Récupérer les statistiques des ventes par type d'opération
        $statistiquesOperations = $operationRepository->findByOperationTypeStatistics();
        VarDumper::dump($statistiquesOperations);
        // Récupérer le chiffre d'affaires total
        $chiffreAffaires = $operationRepository->getTotalSales();
        VarDumper::dump($chiffreAffaires);
        // Récupérer les données du chiffre d'affaires par date
        $chiffreAffairesParDate = $operationRepository->getSalesByDate(); // Méthode à définir dans votre repository
        VarDumper::dump($chiffreAffairesParDate);
        // Passer les données récupérées à la vue Twig
        return $this->render('statistics/stats.html.twig', [
            'salariePlusDeMissions' => $salariePlusDeMissions,
            'statistiquesSalaries' => $statistiquesSalaries,
            'statistiquesOperations' => $statistiquesOperations,
            'chiffreAffaires' => $chiffreAffaires,
            'chiffreAffairesParDate' => $chiffreAffairesParDate,
        ]);
       
    }
}
