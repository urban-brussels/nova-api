<?php

namespace UrbanBrussels\NovaApi;

use Symfony\Component\HttpClient\HttpClient;

class NovaConnection
{
    public string $scope;
    public string $endpoint;
    public string $consumer_key;
    public string $consumer_secret;
    public string $token;
    public int $tokenExpiresAt;
    public string $jwt_key;
    public string $user_key;
    public string $user_briam_key;

    public function __construct(string $endpoint, string $consumer_key, string $consumer_secret, string $scope)
    {
        $this->scope = $scope;
        $this->endpoint = $endpoint;
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
        $this->setToken();
    }

    private function setToken(): void
    {
        $currentTime = time();
        if (!empty($this->token) && $this->tokenExpiresAt > $currentTime) {
            return;
        }

        $client = HttpClient::create([
            'timeout' => 7.0,
            'verify_peer' => false,
            'verify_host' => false,
        ]);

        $response = $client->request('POST', $this->endpoint . 'api/token', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->consumer_key . ":" . $this->consumer_secret),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'grant_type' => 'client_credentials',
                'scope' => $this->scope,
            ]
        ]);

        $content = $response->getContent();
        $data = json_decode($content, true);

        $this->token = $data['access_token'];
        $this->tokenExpiresAt = $currentTime + $data['expires_in'];
    }

    public function setJwtKey(string $jwt_key): void
    {
        $this->jwt_key = $jwt_key;
    }

    public function setUserKey(string $user_key): void
    {
        $this->user_key = $user_key;
    }

    public function setUserBriamKey(string $user_briam_key): void
    {
        $this->user_briam_key = $user_briam_key;
    }
}