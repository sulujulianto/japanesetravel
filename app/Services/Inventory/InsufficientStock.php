<?php

namespace App\Services\Inventory;

use RuntimeException;

class InsufficientStock extends RuntimeException
{
    public function __construct(public readonly int $currentStock)
    {
        parent::__construct('Insufficient stock.');
    }
}
