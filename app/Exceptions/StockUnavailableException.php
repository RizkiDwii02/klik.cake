<?php

namespace App\Exceptions;

use RuntimeException;

class StockUnavailableException extends RuntimeException
{
    public function __construct(string $message = 'Stok produk tidak mencukupi untuk jumlah yang diminta.', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
