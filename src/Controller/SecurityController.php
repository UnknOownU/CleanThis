<?php

namespace App\Controller;

use ApiPlatform\Api\UrlGeneratorInterface;
use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UserRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bridge\Twig\NodeVisitor\Scope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

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

    #[Route('/forgotpass', name:'forgotten_password')]
        public function forgottenPassword(
            Request $request,
            UserRepository $userRepository,
            TokenGeneratorInterface $tokenGenerator,
            EntityManagerInterface $entityManager,
            SendMailService $mail
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
        UserPasswordHasherInterface $passwordHasher
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

                    return $this->redirectToRoute('app_login');
            }

            return $this -> render('security/reset_password.html.twig', [
                'passForm' => $form->createView()
            ]);


        }
        
    }  
}

