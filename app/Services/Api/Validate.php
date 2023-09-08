<?php

namespace App\Services\Api;

use App\Services\Api\Interfaces\ValidateInterface;

class Validate implements ValidateInterface
{
    /**
     * Fields for validation
     */
    const FIELDS = [
        'client_id',
        'client_secret'
    ];

    /**
     * @param array $data
     * @return bool
     */
    public function isValid(array $data): bool
    {
        return !count(array_diff_key(array_fill_keys(self::FIELDS, ''), $data));
    }
}
