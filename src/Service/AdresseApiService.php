<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class AdresseApiService {
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client) {
        $this->client = $client;
    }

    public function chercherAdresse(string $query): array {
        $response = $this->client->request('GET', 'https://api-adresse.data.gouv.fr/search/', [
            'query' => ['q' => $query]
        ]);

        return $response->toArray();
    }
}

?>