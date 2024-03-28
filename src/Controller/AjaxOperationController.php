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
    $user = $security->getUser();

    if ($user !== null) {
        $operation = new Operation();
        $operation->setType($request->request->get('type')); // Utiliser request->get pour les champs de texte
        $operation->setAttachmentFile($request->files->get('attachmentFile')); // Utiliser request->files->get pour le fichier
        $operation->setName($request->request->get('name'));
        $operation->setDescription($request->request->get('description'));
        $operation->setZipcodeOpe($request->request->get('zipcode'));
        $operation->setStreetOpe($request->request->get('street'));
        $operation->setCityOpe($request->request->get('city'));
        $operation->setCustomer($user);
        $operation->setPrice($this->determinePriceBasedOnType($request->request->get('type')));


        $rdvDate = $request->request->get('rdvDate');
        if ($rdvDate) {
            $operation->setRdvAt(new \DateTimeImmutable($rdvDate));
        }

        $entityManager->persist($operation);
        $entityManager->flush();

        return $this->json(['status' => 'success', 'message' => 'Opération créée avec succès']);
    } else {
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

        /**
 * @Route("/ajax/edit-operation/{id}", name="ajax_edit_operation")
 */
public function editOperation(int $id, EntityManagerInterface $entityManager): JsonResponse
{
    $operation = $entityManager->getRepository(Operation::class)->find($id);
    if (!$operation) {
        return $this->json(['status' => 'error', 'message' => 'Opération non trouvée'], 404);
    }

    // Convertir l'entité en tableau ou objet utilisable pour le JSON
    $operationData = [
        'id' => $operation->getId(),
        'type' => $operation->getType(),
        'name' => $operation->getName(),
        'description' => $operation->getDescription(),
        'street' => $operation->getStreetOpe(),
        'zipcode' => $operation->getZipcodeOpe(),
        'city' => $operation->getCityOpe(),
    ];

    return $this->json(['status' => 'success', 'operation' => $operationData]);
}
        /**
        * @Route("/ajax/update-operation/{id}", name="ajax_update_operation")
        */
        public function updateOperation(Request $request, Operation $operation, EntityManagerInterface $entityManager): JsonResponse
        {
           if (!$operation) {
               return $this->json(['status' => 'error', 'message' => 'Opération non trouvée']);
           }
       
           $data = json_decode($request->getContent(), true);
           
           $operation->setType($request->request->get('type')); // Utiliser request->get pour les champs de texte
           $operation->setAttachmentFile($request->files->get('attachmentFile')); // Utiliser request->files->get pour le fichier
           $operation->setName($request->request->get('name'));
           $operation->setDescription($request->request->get('description'));
           $operation->setZipcodeOpe($request->request->get('zipcode'));
           $operation->setStreetOpe($request->request->get('street'));
           $operation->setCityOpe($request->request->get('city'));
           $operation->setPrice($this->determinePriceBasedOnType($request->request->get('type')));
           $operation->setRdvAt(new \DateTimeImmutable());
       
           $entityManager->flush();
       
           return $this->json(['status' => 'success', 'message' => 'Opération mise à jour avec succès']);
        }

        }
