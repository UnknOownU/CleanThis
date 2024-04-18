<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Service\LogsService;
use App\Service\SendMailService;
use App\Form\RegistrationFormType;
use App\Security\UserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, UserAuthenticator $authenticator, EntityManagerInterface $entityManager, SendMailService $mail, LogsService $logsService): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
                
            ) ;

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            
            try {
                $mail->send(
                    'no-reply@cleanthis.fr',
                    $user->getEmail(),
                    'Activation de votre compte CleanThis',
                    'register',
                    [
                        'user' => $user
                    ]
                );
            } catch (Exception $e) {
                
                echo 'Caught exception: Connexion avec MailHog sur 1025 non établie',  $e->getMessage(), "\n";
                
            }

            $role = $user->getRoles();
            $userId = $user->getId();

            // Log successful registration
            try {
                $logsService->postLog([
                'loggerName' => 'Registration',
                'user' => 'Anonymous',
                'message' => 'User registered',
                'level' => 'info',
                'data' => [
                    'role' => $role,
                    'userId' => $userId
                ]
            ]);
            } catch (Exception $e) {
            }
            
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
