<?php

namespace App\Http\Exception;

class InvalidResponseException extends \Exception
{
    protected $errors = [];

    public function __construct(array $errors)
    {
        parent::__construct(sprintf("Error calling Kodi:\n %s", implode('. ', $errors)), 500);
    }

    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}