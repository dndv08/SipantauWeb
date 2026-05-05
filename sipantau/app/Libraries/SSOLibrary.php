<?php

namespace App\Libraries;

class SSOLibrary
{

    private $urlBase = 'https://sso.bps.go.id/auth/';
    private $clientId = '11400-riaubo-er4'; // Ganti dengan client_id Anda
    private $clientSecret = 'bdf48c82-24b5-487e-8a28-cccd39773f4e'; // Ganti dengan client_secret Anda
    private $redirectUri = 'http://riau.web.bps.go.id/sipantau/login/sso/callback'; // Ganti sesuai URL callback Anda

    //private $urlBase = 'https://sso.bps.go.id/auth/';
    // private $clientId = '11400-malaka-ef3'; // Ganti dengan client_id Anda
    //private $clientSecret = '7a6def09-1c70-4687-8313-d818ea20566f'; // Ganti dengan client_secret Anda
    // private $redirectUri = 'https://riau.web.bps.go.id/malaka/sso/callback'; // Ganti sesuai URL callback Anda
    //private $redirectUri = 'http://localhost/siri/sso/callback'; // Ganti sesuai URL callback Anda


    public function getLoginUrl()
    {
        $urlLogin = $this->urlBase . 'realms/pegawai-bps/protocol/openid-connect/auth';
        return $urlLogin . '?' . http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
        ]);
    }

    public function getAccessToken($code)
    {
        $urlToken = $this->urlBase . 'realms/pegawai-bps/protocol/openid-connect/token';

        $ch = curl_init($urlToken);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
        ]));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        }
        curl_close($ch);

        $jsonResponse = json_decode($response, true);
        return $jsonResponse['access_token'] ?? null;
    }

    public function getUserInfo($accessToken)
    {
        $urlUserInfo = $this->urlBase . 'realms/pegawai-bps/protocol/openid-connect/userinfo';

        $ch = curl_init($urlUserInfo);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        }
        curl_close($ch);

        return json_decode($response, true);
    }

    public function getLogoutUrl(string $redirectUrl)
    {
        $urlLogout = $this->urlBase . 'realms/pegawai-bps/protocol/openid-connect/logout';

        return $urlLogout . '?' . http_build_query([
            'client_id' => $this->clientId,
            'post_logout_redirect_uri' => $redirectUrl,
        ]);
    }

}
