<?php
declare(strict_types=1);

namespace App\Services\Api;

use App\Services\Api\Interfaces\AuthInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Session\SessionManager;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Http;

class Auth implements AuthInterface
{
    /**
     * @var string
     */
    private string $token;

    /**
     * Name in session
     */
    const TOKEN_NAME = 'api_token';

    /**
     * @param $apiUrl
     */
    public function __construct(private $apiUrl) {}

    /**
     * @param $clientId
     * @param $clientSecret
     * @return mixed
     */
    public function login($clientId, $clientSecret): mixed
    {
        $response = Http::asForm()->post("{$this->apiUrl}/oauth/token", [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret
        ]);

        return $this->handleResponse($response);
    }

    /**
     * @param $clientId
     * @param $clientSecret
     * @return false|Application|SessionManager|Store|mixed
     */
    public function refreshToken($clientId, $clientSecret): mixed
    {
        $response = Http::asForm()->post("{$this->apiUrl}/oauth/token", [
            'grant_type' => 'refresh_token',
            'refresh_token' => 'the-refresh-token',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => '',
        ]);

        return $this->handleResponse($response);
    }

    /**
     * @param $response
     * @return false|Application|SessionManager|Store|mixed
     */
    private function handleResponse($response): mixed
    {
        if ($response->status() !== Response::HTTP_OK) {
            error_log($response->body());

            return false;
        }

        $this->token = $response->json()['access_token'];

        return session([self::TOKEN_NAME => $this->token]);
    }

    /**
     * @param $token
     * @return void
     */
    public function setToken($token): void
    {
        $this->token = $token;
    }

    /**
     * @return string|null
     */
    public function getToken(): string|null
    {
        return $this->token ?? session(self::TOKEN_NAME);
    }
}
