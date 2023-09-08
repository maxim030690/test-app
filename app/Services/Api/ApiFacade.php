<?php
declare(strict_types=1);

namespace App\Services\Api;

use App\Services\Api\Interfaces\AuthInterface;
use App\Services\Api\Interfaces\ValidateInterface;
use Exception;

class ApiFacade
{

    /**
     * @param ValidateInterface $validate
     * @param AuthInterface $auth
     * @param string $clientId
     * @param string $clientSecret
     */
    public function __construct(
        private ValidateInterface $validate,
        public AuthInterface $auth,
        private string $clientId,
        private string $clientSecret
    )
    {
        if ($this->auth->getToken()) {
            return true;
        }

        try {
            $this->signInUser($this->clientId, $this->clientSecret);
        } catch (Exception $exception) {
            error_log('Caught exception: ' . $exception->getMessage());
        }
    }

    /**
     * @param $clientId
     * @param $clientSecret
     * @return mixed
     * @throws Exception
     */
    public function signInUser($clientId, $clientSecret): mixed
    {
        if (!$this->validate->isValid(['client_id' => $clientId, 'client_secret' => $clientSecret])) {
            throw new Exception('Validation failed. Please, check fields and try again!');
        }

        return $this->auth->login($clientId, $clientSecret);
    }
}
