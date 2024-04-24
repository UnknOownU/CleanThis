<?php

namespace App\Controller;

use Exception;
use Doctrine\ORM\EntityManager;
use App\Service\SendMailService;
use App\Repository\UserRepository;
use App\Form\ResetPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Api\UrlGeneratorInterface;
use App\Form\ResetPasswordRequestFormType;
use App\Service\LogsService;
use Symfony\Bridge\Twig\NodeVisitor\Scope;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;

class SecurityController extends AbstractController
{
    public const SCOPES = [
        'google'=> [],
     ];

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, LogsService $logsService): Response
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

    #[Route('/logout', name: 'app_logout')]
    public function logout(Request $request, LogsService $logsService): Response
    {
        // Clear the session including the stored locale
        $request->getSession()->invalidate();

        // Redirect to the login page or any other page
        return $this->redirectToRoute('app_login');
    }

    #[Route(path:'/oauth/connect/{service}', name:'auth_oauth_connect', methods: ['GET'])]
        public function connect(string $service, ClientRegistry $clientRegistery, LogsService $logsService): RedirectResponse
    {
        if (!in_array($service, array_keys(self::SCOPES), true)){
          throw $this->createNotFoundException();  
        }
                // Log successful login google
                try {
                    $logsService->postLog([
                    'loggerName' => 'Login',
                    'user' => 'Anonymous',
                    'message' => 'User login with Google',
                    'level' => 'info'
                ]);
                } catch (Exception $e) {
                    echo 'Insertion du log échoué';
                }
        return $clientRegistery
        ->getClient($service)
        ->redirect(self::SCOPES[$service]);
    }
   
public function check():Response
    {
        return new Response(status:200);
    }

    #[Route('/forgotpass', name:'forgotten_password')]
        public function forgottenPassword(
            Request $request,
            UserRepository $userRepository,
            TokenGeneratorInterface $tokenGenerator,
            EntityManagerInterface $entityManager,
            SendMailService $mail,
            LogsService $logsService
            ): Response
        {
            $form = $this->createForm(ResetPasswordRequestFormType::class);
            
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user = $userRepository->findOneByEmail($form->get('email')
                ->getData());

                if ($user) {
                    $token = $tokenGenerator->generateToken();
                    $user->setResetToken($token);
                    $entityManager->persist($user);
                    $entityManager->flush();

                    $url = $this->generateUrl('reset_pass', ['token' => $token],
                    UrlGeneratorInterface::ABS_URL);
                    
                    $context = compact('url', 'user');
                    $mail->send(
                        'no-replay@cleanthis.fr',
                        $user->getEmail(),
                        'Reinitialisation du mot de passe',
                        'password_reset',
                        $context
                    );
                    // Log forgotten pass
                    try {
                        $logsService->postLog([
                        'loggerName' => 'Security',
                        'user' => 'Anonymous',
                        'message' => 'User called forgotten password',
                        'level' => 'info'
                    ]);
                    } catch (Exception $e) {
                    }
                    
                    return $this->redirectToRoute('app_login');
                }

                return $this->redirectToRoute('app_login');
            }

            return $this->render('security/reset_password_request.html.twig', [
                'requestPassForm' => $form->createView()
            ]);
        }

    #[Route('/forgotpass/{token}', name:'reset_pass')]  
    public function resetPass(
        string $token,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        LogsService $logsService
        ): Response 
    {
        $user = $userRepository->findOneByResetToken($token);
        if ($user) {
            $form = $this->createForm(ResetPasswordFormType::class);
            
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user->setResetToken('');
                $user->setPassword(
                    $passwordHasher -> hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                    );
                    $entityManager->persist($user);
                    $entityManager->flush();

            // Log new pass
            try {
                $logsService->postLog([
                'loggerName' => 'Security',
                'user' => 'Anonymous',
                'message' => 'User set new password from forgotten password',
                'level' => 'info'
            ]);
            } catch (Exception $e) {
            }
                    return $this->redirectToRoute('app_login');
            }

            return $this -> render('security/reset_password.html.twig', [
                'passForm' => $form->createView()
            ]);


        }
        
    }  
}

