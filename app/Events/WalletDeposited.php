<?php

namespace App\Events;

use App\Models\Student;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class WalletDeposited
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Student $student,
        public float $amount,
        public string $walletType,
        public float $newBalance,
        public ?string $description = null,
    ) {}
}
