<?php
namespace App\Controller;

use App\Form\ProfileType;
use App\Form\SensitiveInfoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfilController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $userPasswordHasher;
    private Security $security;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->security = $security;
    }

    /**
     * @Route("/profile/edit", name="profile_edit")
     */
    public function edit(Request $request)
    {
        $user = $this->security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        $profileForm = $this->createForm(ProfileType::class, $user);
        $sensitiveInfoForm = $this->createForm(SensitiveInfoType::class, $user);

        $profileForm->handleRequest($request);
        $sensitiveInfoForm->handleRequest($request);

        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Vos informations ont été mises à jour.');
        }

        if ($sensitiveInfoForm->isSubmitted() && $sensitiveInfoForm->isValid()) {
            $currentPassword = $sensitiveInfoForm->get('currentPassword')->getData();
            if (!$this->userPasswordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error_password', 'Le mot de passe actuel est incorrect.');
            } else {
                if ($newPassword = $sensitiveInfoForm->get('newPassword')->getData()) {
                    $user->setPassword($this->userPasswordHasher->hashPassword($user, $newPassword));
                }
                $this->entityManager->flush();
                $this->addFlash('success', 'Vos informations sensibles ont été mises à jour.');
            }
        }

        return $this->render('user/edit.html.twig', [
            'profileForm' => $profileForm->createView(),
            'sensitiveInfoForm' => $sensitiveInfoForm->createView(),
        ]);
    
    }
    /**
     * @Route("/check-password", name="check_password", methods={"POST"})
     */
    public function checkPassword(Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        $data = json_decode($request->getContent(), true);
        $isValid = false;

        if ($user && isset($data['password'])) {
            $isValid = $this->userPasswordHasher->isPasswordValid($user, $data['password']);
        }

        return new JsonResponse(['isValid' => $isValid]);
    }
}
