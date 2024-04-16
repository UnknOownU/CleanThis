<?php

namespace App\Controller;

use Exception;
use App\Service\LogsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserAuthenticatorController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, LogsService $logsService): Response
    {
         if ($this->getUser()) {

             return $this->redirectToRoute('app_login');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(Request $request, LogsService $logsService): Response
    {
        // Clear the session including the stored locale
        $request->getSession()->invalidate();
        // Log successful logout
        try {
            $logsService->postLog([
            'loggerName' => 'AuthController',
            'user' => 'N\C',
            'message' => 'User logout successfully',
            'level' => 'info'
        ]);
        } catch (Exception $e) {
            echo 'Insertion du log échoué';
        }
        // Redirect to the login page or any other page
        return $this->redirectToRoute('app_login');
    }
}
