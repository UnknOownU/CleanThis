<?php

namespace App\Controller;

use App\Entity\Operation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/operations', name: 'operations_')]
class OperationsController extends AbstractController
{
    #[Route('/', name:'index')]
    public function index(): Response
    {
        return $this->render('operations/index.html.twig');
    }

    #[Route('/{id}', name: 'details')]
    public function details(Operation $id): Response
    {
        dd($id);
        // dd($id->getDescription());
        return $this->render('operations/details.html.twig');
    }


}