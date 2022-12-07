<?php

namespace UrbanBrussels\NovaApi;

class NovaConnection
{
    public string $scope;
    public string $endpoint;
    public string $consumer_key;
    public string $consumer_secret;
    public string $token;
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
        $output = shell_exec('curl -k -d "grant_type=client_credentials&scope=' . $this->scope . '" -H "Authorization: Basic ' . base64_encode($this->consumer_key . ":" . $this->consumer_secret) . '" ' . $this->endpoint . 'api/token');
        $exp = explode('"', $output);
        $this->token = $exp[3];
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