<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ServicesdetaillittleController extends AbstractController
{
    #[Route('/services-detail-little', name: 'services-detail-little')]
    public function index(): Response
    {
        return $this->render('user/services-detail-little.html.twig', [
            'controller_name' => 'ServicesdetaillittleController',
        ]);
    }
}