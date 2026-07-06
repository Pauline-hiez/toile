<?php

namespace App\Core;

/**
 * GoogleAuth
 *
 * Gère le flux OAuth 2.0 avec Google via des appels HTTP directs
 * (pas de SDK : google/apiclient embarque des dizaines de milliers de
 * fichiers inutiles pour un simple "Se connecter avec Google").
 */
class GoogleAuth
{
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const USERINFO_URL = 'https://www.googleapis.com/oauth2/v3/userinfo';

    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;

    public function __construct()
    {
        $this->clientId = $_ENV['GOOGLE_CLIENT_ID'];
        $this->clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'];
        $this->redirectUri = $_ENV['GOOGLE_REDIRECT_URI'];
    }

    /**
     * Génère l'URL de redirection vers Google.
     */
    public function getAuthUrl(): string
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'email profile',
            'access_type' => 'online',
            'prompt' => 'select_account',
        ];

        return self::AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * Échange le code reçu de Google contre les infos utilisateur.
     * Renvoie ['id' => string, 'email' => string, 'name' => string]
     * ou null si échec.
     */
    public function getUserInfo(string $code): ?array
    {
        $token = $this->fetchAccessToken($code);

        if ($token === null || !isset($token['access_token'])) {
            return null;
        }

        $userInfo = $this->fetchUserInfo($token['access_token']);

        if ($userInfo === null) {
            return null;
        }

        return [
            'id' => $userInfo['sub'] ?? '',
            'email' => $userInfo['email'] ?? '',
            'name' => $userInfo['name'] ?? '',
        ];
    }

    private function fetchAccessToken(string $code): ?array
    {
        $response = $this->post(self::TOKEN_URL, [
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code',
        ]);

        if ($response === null) {
            return null;
        }

        $data = json_decode($response, true);

        return isset($data['access_token']) ? $data : null;
    }

    private function fetchUserInfo(string $accessToken): ?array
    {
        $ch = curl_init(self::USERINFO_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $status !== 200) {
            return null;
        }

        return json_decode($response, true);
    }

    private function post(string $url, array $fields): ?string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($fields),
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($response !== false && $status === 200) ? $response : null;
    }
}
