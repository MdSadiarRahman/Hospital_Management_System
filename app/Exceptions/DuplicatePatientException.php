<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Collection;

class DuplicatePatientException extends Exception
{
    protected Collection $duplicates;

    public function __construct(Collection $duplicates, string $message = "Potential duplicate patient records detected.", int $code = 409)
    {
        parent::__construct($message, $code);
        $this->duplicates = $duplicates;
    }

    public function getDuplicates(): Collection
    {
        return $this->duplicates;
    }
}
