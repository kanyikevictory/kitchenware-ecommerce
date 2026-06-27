<?php

namespace App\Payments\Data;

final readonly class PaymentData
{
    public function __construct(
        public string $method,
        public ?string $provider,
        public string $transactionId,
        public string $status,
        public array $meta = [],
    ) {}
}
