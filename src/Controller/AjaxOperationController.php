<?php
// src/Controller/AjaxOperationController.php

namespace App\Controller;

use Symfony\Component\Security\Core\Security;
use App\Entity\user;
use App\Entity\Operation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AjaxOperationController extends AbstractController
{
    /**
     * @Route("/ajax/get-cleaning-options", name="ajax_get_cleaning_options")
     */
    public function getCleaningOptions(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $operationType = $data['type'] ?? '';

        // Ici, on détermine les options en fonction du type d'opération
        $options = $this->determineCleaningOptions($operationType);

        return $this->json(['options' => $options]);
    }

    private function determineCleaningOptions(string $operationType): array
    {
        // Ici, mettez votre logique pour déterminer les options de nettoyage
        // Par exemple :
        switch ($operationType) {
            case 'Little':
                return ['Nettoyage de bureau', 'Nettoyage de fenêtres','Nettoyage de sols','Nettoyage de sanitaires','Nettoyage de cuisines','Nettoyage de meubles','Nettoyage de tapis','Nettoyage de murs','Désinfection de surfaces','Nettoyage équipements électroniques','Nettoyage de portes et poignées','Nettoyage de claviers et téléphones','Nettoyage de rideaux','Nettoyage de luminaires','Nettoyage de petits espaces extérieurs','Nettoyage de vitrines','Nettoyage de volets','Nettoyage de petits entrepôts','Nettoyage après petit événement','Nettoyage de véhicules de société'];
            case 'Medium':
                return [
                    'Nettoyage commercial',
                    'Nettoyage de moquette',
                    'Nettoyage après travaux',
                    'Nettoyage de façades',
                    'Nettoyage de parkings',
                    'Nettoyage de terrasses',
                    'Nettoyage de jardins',
                    'Nettoyage de grandes surfaces vitrées',
                    'Nettoyage de halls d\'entrée',
                    'Nettoyage de restaurants',
                    'Nettoyage de magasins',
                    'Nettoyage de gymnases',
                    'Nettoyage de salles de conférence',
                    'Nettoyage de zones de réception',
                    'Nettoyage de piscines',
                    'Nettoyage de salles d\'exposition',
                    'Nettoyage de salles de sport',
                    'Nettoyage de grandes cuisines',
                    'Nettoyage de vestiaires',
                    'Nettoyage d\'aires de jeux'
                ];
            case 'Big':
                return  [
                    'Nettoyage industriel',
                    'Nettoyage de façade',
                    'Nettoyage de grandes structures',
                    'Nettoyage de chantiers',
                    'Nettoyage de grandes zones extérieures',
                    'Nettoyage après sinistre',
                    'Nettoyage de silos',
                    'Nettoyage de dépôts',
                    'Nettoyage de hangars',
                    'Nettoyage de quais',
                    'Nettoyage de grandes zones de stockage',
                    'Nettoyage de zones de production',
                    'Nettoyage de machinerie lourde',
                    'Nettoyage de grues',
                    'Nettoyage de flottes de véhicules',
                    'Nettoyage de pistes',
                    'Nettoyage de terminaux',
                    'Nettoyage de centres logistiques',
                    'Nettoyage de docks',
                    'Nettoyage de grandes aires de jeux'
                ]
                ; 
            default:
                return [];
        }
    }

/**
 * @Route("/ajax/create-operation", name="ajax_create_operation")
 */
public function createOperation(Request $request, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $security->getUser();

        if ($user !== null) {
            $operation = new Operation();
            $operation->setType($data['type']);
            $operation->setName($data['name']);
            $operation->setDescription($data['description']);
            $operation->setZipcodeOpe($data['zipcode']);
            $operation->setstreetOpe($data['street']);
            $operation->setcityOpe($data['city']);
            $operation->setCustomer($user);  // Utilisation de l'identifiant de l'utilisateur
            $operation->setPrice($this->determinePriceBasedOnType($data['type']));
            $operation->setCreatedAt(new \DateTimeImmutable());

            // Autres propriétés de l'opération...

            $entityManager->persist($operation);
            $entityManager->flush();

            return $this->json(['status' => 'success', 'message' => 'Opération créée avec succès']);
        } else {
            // Gérer le cas où l'utilisateur n'est pas récupéré avec succès
            // Par exemple, enregistrer un message d'erreur ou renvoyer une réponse d'erreur appropriée
            return $this->json(['status' => 'error', 'message' => 'Utilisateur non trouvé']);
        }
    
    
}

private function determinePriceBasedOnType(string $type): int
{
    // Déterminez le prix en fonction du type d'opération
    switch ($type) {
        case 'Little':
            return 1000;
        case 'Medium':
            return 2500;
        case 'Big':
            return 5000;
        default:
            return 0; // ou un autre prix par défaut pour des opérations personnalisées
    }
}

}
