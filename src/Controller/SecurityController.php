<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bridge\Twig\NodeVisitor\Scope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class SecurityController extends AbstractController
{

    public const SCOPES = [
        'google'=> [],
     ];


    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    
    
    }

    #[Route(path: '/logout', name: 'app_logout')]
       public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

   
    #[Route(path:'/oauth/connect/{service}', name:'auth_oauth_connect', methods: ['GET'])]
        public function connect(string $service, ClientRegistry $clientRegistery): RedirectResponse
    {
        if (!in_array($service, array_keys(self::SCOPES), true)){
          throw $this->createNotFoundException();  
        }
        return $clientRegistery
        ->getClient($service)
        ->redirect(self::SCOPES[$service]);
    }

    
   
    public function check():Response
    {
        return new Response(status:200);
    }
}
