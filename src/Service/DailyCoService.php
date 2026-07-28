<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class DailyCoService
{
    public function __construct(
        private HttpClientInterface $client,
        private string $dailyApiKey
    ) {}

    public function createRoom(\DateTimeInterface $dateRendezVous): ?string
    {
        $expiration = \DateTimeImmutable::createFromInterface($dateRendezVous)
            ->modify('+2 hours');

        try {
            // URL corrigée : https://api.daily.co/v1/rooms
            $response = $this->client->request('POST', 'https://api.daily.co/v1/rooms', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->dailyApiKey,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'privacy' => 'public', // Placé à la racine du JSON
                    'properties' => [
                        'exp' => $expiration->getTimestamp(),
                    ]
                ]
            ]);

            $statusCode = $response->getStatusCode();

            // Si succès (200 OK)
            if ($statusCode === 200) {
                $data = $response->toArray();
                return $data['url'];
            }

            // Capture d'erreur API si code HTTP !== 200
            dd([
                'erreur_api' => "L'API Daily.co a refusé la création",
                'code_erreur' => $statusCode,
                'message_daily' => $response->getContent(false),
                'cle_api_recue_par_le_service' => $this->dailyApiKey
            ]);

        } catch (\Exception $e) {
            // Capture d'erreur réseau / SSL WAMP
            dd([
                'erreur_interne' => 'Impossible de joindre le serveur Daily.co depuis WAMP',
                'message' => $e->getMessage()
            ]);
        }

        return null;
    }
}