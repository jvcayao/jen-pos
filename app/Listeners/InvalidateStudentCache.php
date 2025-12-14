<?php

namespace App\Listeners;

use App\Events\StudentCreated;
use App\Services\CacheService;
use App\Events\WalletDeposited;
use App\Events\WalletWithdrawn;
use Illuminate\Contracts\Queue\ShouldQueue;

class InvalidateStudentCache implements ShouldQueue
{
    public function __construct(
        protected CacheService $cacheService,
    ) {}

    public function handle(StudentCreated|WalletDeposited|WalletWithdrawn $event): void
    {
        $student = $event->student;

        $this->cacheService->invalidateStudent($student->id);
        $this->cacheService->invalidateStudentSearch($student->store_id);
    }
}
