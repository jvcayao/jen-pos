<?php

namespace App\Events;

use App\Models\Order;
use App\Models\Student;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class OrderCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public ?Student $student = null,
        public ?string $walletType = null,
        public float $discountAmount = 0,
    ) {}
}
