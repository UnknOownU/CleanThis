<?php
// src/Controller/ProfilController.php

namespace App\Controller;

use App\Form\UserType;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;

class ProfilController extends AbstractController
{
    private UserPasswordHasherInterface $userPasswordHasher;
    private Security $security;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher, Security $security)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->security = $security;
    }

    /**
     * @Route("/profile/edit", name="profile_edit")
     */
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->security->getUser();

        if (!$user) {
            // Redirige vers la page de connexion ou une page d'erreur
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();
            if ($newPassword) {
                $user->setPassword($this->userPasswordHasher->hashPassword($user, $newPassword));
            }

            // Pas besoin de faire un persist sur une entité déjà gérée
            $entityManager->flush();

            // Message flash de succès et redirection
            $this->addFlash('success', 'Votre profil a été mis à jour.');
            return $this->redirectToRoute('admin');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
