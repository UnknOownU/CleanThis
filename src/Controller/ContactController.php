<?php

namespace App\Controller;

use App\Service\LogsService;
use Exception;
use DateTimeImmutable;
use App\Service\SendMailService;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(Request $request, MailerInterface $mailer, SendMailService $mail, LogsService $logsService): Response
    {
        if ($request->isMethod('POST')) {
            $formData = $request->request->all();

            // Récupérer les données du formulaire
            $volunteerName = $formData['volunteer-name'];
            $volunteerEmail = $formData['volunteer-email'];
            $jobSalary = $formData['job-salary'];
            $volunteerMessage = $formData['volunteer-message'];

            // Construire le contenu de l'email
            $emailContent = "Nom: $volunteerName\n";
            $emailContent .= "Email: $volunteerEmail\n";
            $emailContent .= "Type de service: $jobSalary\n";
            $emailContent .= "Message: $volunteerMessage";

            // Créer un objet Email
            $email = (new Email())
                ->from($volunteerEmail)
                ->to('votre@mail.com') // Adresse email où vous souhaitez recevoir les soumissions du formulaire
                ->subject('Nouveau message du formulaire de contact')
                ->text($emailContent);

            // Envoyer l'email
            $mailer->send($email);

                // Generate a token
                $token = $this->generateToken();

                // Calculate the expiration time (e.g., 1 hour from now)
                $expirationTime = (new DateTimeImmutable())->modify('+1 hour');

                // Store the token and its expiration time in your database or session
                // For simplicity, let's assume you store it in a session variable
                $request->getSession()->set('registration_token', [
                    'token' => $token,
                    'expiration_time' => $expirationTime,
                ]);

                // Construct the registration link with the token
                $registrationLink = $this->generateUrl('app_register', [
                    'token' => $token,
                ], UrlGeneratorInterface::ABSOLUTE_URL);

            try {
                $mail->send(
                    'no-reply@cleanthis.fr',
                    $volunteerEmail,
                    'Création de votre compte',
                    'createaccount',
                    [
                        'user' => $volunteerEmail,
                        'registrationLink' => $registrationLink
                    ]
                );
            } catch (Exception $e) {
                echo 'Caught exception: Connexion avec MailHog sur 1025 non établie',  $e->getMessage(), "\n";
            } 

             // Log edit operation
             try {
                $logsService->postLog([
                'loggerName' => 'Contact',
                'user' => $volunteerEmail,
                'message' => 'User send email contact',
                'level' => 'info'
            ]);
            } catch (Exception $e) {
            }

            // Redirection vers une page de confirmation ou tout autre traitement après l'envoi du formulaire
            return $this->redirectToRoute('confirmation_page');
        }

        // Si la requête n'est pas de type POST ou si le formulaire n'a pas été soumis, afficher le formulaire
        return $this->render('user/contact.html.twig', [
            'controller_name' => 'ContactController',
        ]);
    }

    #[Route('/confirmation', name: 'confirmation_page')]
    public function confirmationPage(): Response
    {
        return $this->render('user/confirmation.html.twig');
    }

    private function generateToken(): string
    {
        return bin2hex(random_bytes(16)); // Generate a random token
    }
}
