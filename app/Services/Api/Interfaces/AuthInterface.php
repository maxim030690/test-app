<?php

namespace App\Services\Api\Interfaces;

/**
 * AuthInterface
 */
interface AuthInterface
{
    /**
     * @param $clientId
     * @param $clientSecret
     * @return mixed
     */
    public function login($clientId, $clientSecret): mixed;

    /**
     * @param $token
     * @return mixed
     */
    public function setToken($token): void;


    /**
     * @param $token
     * @return mixed
     */
    public function getToken(): string|null;
}
