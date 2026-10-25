<?php

namespace App\Exceptions;

use RuntimeException;

class SlotFullException extends RuntimeException
{
    public function __construct(
        string $message = 'Kapasitas slot untuk jadwal yang dipilih sudah penuh.',
        int $code = 422,
        protected ?array $suggestedSlot = null
    ) {
        parent::__construct($message, $code);
    }

    public function getSuggestedSlot(): ?array
    {
        return $this->suggestedSlot;
    }
}
