<?php

namespace App\Services\Api\Interfaces;

interface ValidateInterface
{
    /**
     * @param array $data
     * @return bool
     */
    public function isValid(array $data): bool;
}
