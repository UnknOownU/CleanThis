<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(Request $request, MailerInterface $mailer): Response
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
}
