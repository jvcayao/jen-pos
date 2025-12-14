<?php

namespace App\Listeners;

use App\Events\WalletDeposited;
use App\Events\WalletWithdrawn;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogWalletTransaction implements ShouldQueue
{
    public function handle(WalletDeposited|WalletWithdrawn $event): void
    {
        $type = $event instanceof WalletDeposited ? 'deposit' : 'withdrawal';

        Log::info('Wallet transaction', [
            'type' => $type,
            'student_id' => $event->student->id,
            'student_name' => $event->student->full_name,
            'amount' => $event->amount,
            'wallet_type' => $event->walletType,
            'new_balance' => $event->newBalance,
            'description' => $event->description,
            'order_id' => $event instanceof WalletWithdrawn ? $event->orderId : null,
        ]);
    }
}
