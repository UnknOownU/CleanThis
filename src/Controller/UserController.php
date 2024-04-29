<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Entity\Operation;
use App\Form\UserType;
use App\Service\LogsService;
use App\Repository\UserRepository;
use App\Repository\OperationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }


    #[Route('/users', name: 'api_users_by_roles', methods: ['GET'])]
    public function getUsersByRoles(UserRepository $userRepository, OperationRepository $operationRepository): JsonResponse
    {
        $roles = ['ROLE_ADMIN', 'ROLE_SENIOR', 'ROLE_APPRENTI'];
        $users = $userRepository->findByRoles($roles);
    
        $formattedUsers = array_map(function ($user) use ($operationRepository) {
            $assignedOperations = $operationRepository->findAssignedOperationsByUser($user->getId());
            $assignedOperationsNames = array_map(function ($operation) {
                return $operation['name']; // Assurez-vous que 'name' est correctement sélectionné dans la requête
            }, $assignedOperations);
    
            return [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'name' => $user->getName(),
                'firstname' => $user->getFirstname(),
                'assignedOperationsNames' => $assignedOperationsNames,
            ];
        }, $users);
        return $this->json($formattedUsers);
    }
    
}
