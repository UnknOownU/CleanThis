<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Service\AdresseApiService;

class AdresseApiController extends AbstractController {
    private AdresseApiService $adresseApiService;

    public function __construct(AdresseApiService $adresseApiService) {
        $this->adresseApiService = $adresseApiService;
    }

    public function search(Request $request): JsonResponse {
        $query = $request->query->get('query');
        $resultats = $this->adresseApiService->chercherAdresse($query);

        return $this->json($resultats);
    }
}
