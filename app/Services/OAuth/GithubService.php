<?php

namespace App\Services\OAuth;

use App\Contracts\OAuthContract;
use App\Contracts\SocialContract;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GithubService implements OAuthContract, SocialContract
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.github.com',
            'timeout' => 5.0
        ]);
    }

    public function auth(string $code): array
    {
        $url = "https://github.com/login/oauth/access_token";
        try {
            $response = $this->client->request('POST', $url, [
                'form_params' => [
                    'client_id' => config('providers.github.client_id'),
                    'client_secret' => config('providers.github.secret'),
                    'code' => $code,
                ],
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]);

            $payload = json_decode($response->getBody(), true);

            return [
                'access_token' => $payload['access_token']
            ];
        } catch (GuzzleException $exception) {
            return ["deu merda: " . $exception->getMessage()];
        }
    }

    public function getAuthenticatedUser(string $token): array
    {
        $uri = "/user";
        $response = $this->client->request('GET', $uri, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]
        ]);

        $payload = json_decode($response->getBody(), true);

        return [
            'login' => $payload['login'],
            'name' => $payload['name'],
            'id' => $payload['id'],
            'email' => $payload['email'] ?? 'teste@teste.com.br',
            'avatar_url' => $payload['avatar_url']
        ];
    }

    public function findUser(string $username): array
    {
        $uri = "/users/$username";

        try {
            $response = $this->client->request('GET', $uri);
        } catch (GuzzleException $e) {
            return [];
        }

        $payload = json_decode($response->getBody(), true);
        
        return [    
            'id' => $payload['id'],
            'login' => $payload['login'],
            'avatar_url' => $payload['avatar_url'],
            'email' => $payload['email']
        ];
    }
}
