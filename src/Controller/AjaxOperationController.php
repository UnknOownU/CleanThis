<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Entity\Operation;
use App\Service\SendMailService;
use App\Repository\UserRepository;
use App\Service\LogsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AjaxOperationController extends AbstractController
{
    /**
     * @Route("/ajax/get-cleaning-options", name="ajax_get_cleaning_options")
     */
    public function getCleaningOptions(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $operationType = $data['type'] ?? '';

        $options = $this->determineCleaningOptions($operationType);

        return $this->json(['options' => $options]);
    }

    private function determineCleaningOptions(string $operationType): array
    {
        switch ($operationType) {
            case 'Little':
                return [
                    'Nettoyage de bureau', 'Nettoyage de fenêtres', 'Nettoyage de sols', 'Nettoyage de sanitaires',
                    'Nettoyage de cuisines', 'Nettoyage de meubles', 'Nettoyage de tapis', 'Nettoyage de murs',
                    'Désinfection de surfaces', 'Nettoyage équipements électroniques', 'Nettoyage de portes et poignées',
                    'Nettoyage de claviers et téléphones', 'Nettoyage de rideaux', 'Nettoyage de luminaires',
                    'Nettoyage de petits espaces extérieurs', 'Nettoyage de vitrines', 'Nettoyage de volets',
                    'Nettoyage de petits entrepôts', 'Nettoyage après petit événement', 'Nettoyage de véhicules de société'
                ];
            case 'Medium':
                return [
                    'Nettoyage commercial', 'Nettoyage de moquette', 'Nettoyage après travaux', 'Nettoyage de façades',
                    'Nettoyage de parkings', 'Nettoyage de terrasses', 'Nettoyage de jardins', 'Nettoyage de grandes surfaces vitrées',
                    'Nettoyage de halls d\'entrée', 'Nettoyage de restaurants', 'Nettoyage de magasins', 'Nettoyage de gymnases',
                    'Nettoyage de salles de conférence', 'Nettoyage de zones de réception', 'Nettoyage de piscines',
                    'Nettoyage de salles d\'exposition', 'Nettoyage de salles de sport', 'Nettoyage de grandes cuisines',
                    'Nettoyage de vestiaires', 'Nettoyage d\'aires de jeux'
                ];
            case 'Big':
                return [
                    'Nettoyage industriel', 'Nettoyage de façade', 'Nettoyage de grandes structures', 'Nettoyage de chantiers',
                    'Nettoyage de grandes zones extérieures', 'Nettoyage après sinistre', 'Nettoyage de silos',
                    'Nettoyage de dépôts', 'Nettoyage de hangars', 'Nettoyage de quais', 'Nettoyage de grandes zones de stockage',
                    'Nettoyage de zones de production', 'Nettoyage de machinerie lourde', 'Nettoyage de grues', 'Nettoyage de flottes de véhicules',
                    'Nettoyage de pistes', 'Nettoyage de terminaux', 'Nettoyage de centres logistiques', 'Nettoyage de docks', 'Nettoyage de grandes aires de jeux'
                ];
            default:
                return ['Custom'];
        }
    }

    /**
     * @Route("/ajax/create-operation", name="ajax_create_operation")
     */
    public function createOperation(Request $request, EntityManagerInterface $entityManager, Security $security, SendMailService $mail, LogsService $logsService): JsonResponse
    {
        $user = $security->getUser();

        if ($user !== null) {
            $rdvDateTimeString = $request->request->get('rdvDateTime');
            $rdvDateTime = new \DateTime($rdvDateTimeString);
            $today = new \DateTime();
            $today->setTime(0, 0, 0);

            if ($rdvDateTime < $today) {
                return $this->json(['status' => 'error', 'message' => 'Les rendez-vous pour le jour même ne sont pas autorisés.']);
            }

            if ($rdvDateTime->format('w') == 0) { // Dimanche
                return $this->json(['status' => 'error', 'message' => 'Les rendez-vous le dimanche ne sont pas autorisés.']);
            }

            $operation = new Operation();
            $operation->setType($request->request->get('type'));
            $operation->setName($request->request->get('name'));
            $operation->setDescription($request->request->get('description'));
            $operation->setZipcodeOpe($request->request->get('zipcode'));
            $operation->setStreetOpe($request->request->get('street'));
            $operation->setCityOpe($request->request->get('city'));
            $operation->setCustomer($user);
            $operation->setPrice($this->determinePriceBasedOnType($request->request->get('type')));

            $rdvDateTime = $request->request->get('rdvDateTime');
            if ($rdvDateTime) {
                try {
                    $operation->setRdvAt(new \DateTimeImmutable($rdvDateTime));
                } catch (\Exception $e) {
                    return $this->json(['status' => 'error', 'message' => 'Invalid date format']);
                }
            }

            // Gestion des fichiers
            $attachmentFile = $request->files->get('attachmentFile');
            if ($attachmentFile) {
                $operation->setAttachmentFile($attachmentFile);
            }

            // Définir la date d'acceptation
            $operation->setAcceptedAt(new \DateTimeImmutable());

            $entityManager->persist($operation);
            $entityManager->flush();

            $customer = $operation->getCustomer();
            $created = $operation->getCreatedAt();
            $idOpe = $operation->getId();
            $customerId = $customer->getId();

            // Log successful creation
            try {
                $logsService->postLog([
                    'loggerName' => 'Operation',
                    'user' => 'Anonymous',
                    'message' => 'User created operation',
                    'level' => 'info',
                    'data' => [
                        'id_ope' => $idOpe,
                        'created' => $created,
                        'customer_id' => $customerId
                    ]
                ]);
            } catch (Exception $e) {
                // Log the exception
            }

            // Send mail
            try {
                $mail->send(
                    'no-reply@cleanthis.fr',
                    $customer->getEmail(),
                    'Création de votre opération',
                    'opecreate',
                    ['user' => $customer]
                );
            } catch (Exception $e) {
                // Log the exception
            }

            return $this->json(['status' => 'success', 'message' => 'Opération créée avec succès']);
        } else {
            return $this->json(['status' => 'error', 'message' => 'Utilisateur non trouvé']);
        }
    }

    private function determinePriceBasedOnType(string $type): int
    {
        switch ($type) {
            case 'Little':
                return 1000;
            case 'Medium':
                return 2500;
            case 'Big':
                return 5000;
            default:
                return 0; // Prix par défaut pour des opérations personnalisées
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
    public function updateOperation(Request $request, Operation $operation, EntityManagerInterface $entityManager, LogsService $logsService): JsonResponse
    {
        if (!$operation) {
            return $this->json(['status' => 'error', 'message' => 'Opération non trouvée']);
        }

        $operation->setType($request->request->get('type'));
        $operation->setName($request->request->get('name'));
        $operation->setDescription($request->request->get('description'));
        $operation->setZipcodeOpe($request->request->get('zipcode'));
        $operation->setStreetOpe($request->request->get('street'));
        $operation->setCityOpe($request->request->get('city'));
        $operation->setPrice($this->determinePriceBasedOnType($request->request->get('type')));

        // Mise à jour de la date d'acceptation si nécessaire
        $operation->setAcceptedAt(new \DateTimeImmutable());

        $entityManager->flush();
        $idOpe = $operation->getId();

        // Log edit operation
        try {
            $logsService->postLog([
                'loggerName' => 'Operation',
                'user' => 'Anonymous',
                'message' => 'User edited operation',
                'level' => 'info',
                'data' => [
                    'id_ope' => $idOpe
                ]
            ]);
        } catch (Exception $e) {
            // Log the exception
        }

        return $this->json(['status' => 'success', 'message' => 'Opération mise à jour avec succès']);
    }

    /**
     * @Route("/ajax/update-operation-operator/{id}", name="ajax_update_operation_operator")
     */
    public function updateOperationOperator(int $id, Request $request, EntityManagerInterface $entityManager, Security $security, UrlGeneratorInterface $urlGenerator, LogsService $logsService): RedirectResponse
    {
        if (!$security->isGranted('ROLE_ADMIN')) {
            return new RedirectResponse($urlGenerator->generate('your_login_route_name'));
        }

        $operation = $entityManager->getRepository(Operation::class)->find($id);
        if (!$operation) {
            return new RedirectResponse($urlGenerator->generate('your_error_route_name'));
        }

        $data = $request->request->all();
        $newOperatorId = $data['newOperatorId'] ?? null;

        if (null === $newOperatorId) {
            return new RedirectResponse($urlGenerator->generate('your_error_route_name'));
        }

        $newOperator = $entityManager->getRepository(User::class)->find($newOperatorId);
        if (!$newOperator) {
            return new RedirectResponse($urlGenerator->generate('your_error_route_name'));
        }

        $operation->setSalarie($newOperator);
        $idOpe = $operation->getId();
        $entityManager->flush();

        try {
            $logsService->postLog([
                'loggerName' => 'Operation',
                'user' => 'Anonymous',
                'message' => 'User changed operator',
                'level' => 'info',
                'data' => [
                    'id_ope' => $idOpe
                ]
            ]);
        } catch (Exception $e) {
            // Log the exception
        }

        return new RedirectResponse('/admin');
    }

    /**
     * @Route("/admin/change-operator/{id}", name="admin_change_operator")
     */
    public function changeOperator(int $id, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $operation = $entityManager->getRepository(Operation::class)->find($id);
        if (!$operation) {
            throw $this->createNotFoundException('Opération non trouvée');
        }

        $operators = $userRepository->findByRoles(['ROLE_SENIOR', 'ROLE_ADMIN', 'ROLE_APPRENTI']);

        return $this->render('admin/change.operator.twig', [
            'operation' => $operation,
            'operators' => $operators,
        ]);
    }
}
