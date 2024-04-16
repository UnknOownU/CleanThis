<?php

namespace App\Service;

use Exception;
use DateTimeImmutable;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LogsService
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function postLog(array $logData): array
    {
        $requestData = [
            'eventTime' => new DateTimeImmutable,
            'loggerName' => $logData['loggerName'],
            'user' => $logData['user'],
            'message' => $logData['message'],
            'level' => $logData['level'],
            'data' => []
            // 'connexion' => $logData['connexion']
        ];

        $requestJson = json_encode($requestData, JSON_THROW_ON_ERROR);

        $response = $this->httpClient->request('POST', 'http://localhost:3000/log', [
            'headers' => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            'body' => $requestJson,
        ]);

        if (201 !== $response->getStatusCode()) {
            throw new Exception('Response status code is different than expected.');
        }

        // ... other checks

        $responseJson = $response->getContent();
        $responseData = json_decode($responseJson, true, 512, JSON_THROW_ON_ERROR);

        return $responseData;
    }
}