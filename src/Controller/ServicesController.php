<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ServicesController extends AbstractController
{
    #[Route('/services', name: 'services')]
    public function index(): Response
    {
        return $this->render('user/services.html.twig', [
            'controller_name' => 'ServicesController',
        ]);
    }
}