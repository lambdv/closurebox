<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use App\Models\ProductRequest;

class TestJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $request_id)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ProductRequest::find($this->request_id)
            ->update([
                'status' => 'success',
            ]);
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error("TestJob failed: " . $exception->getMessage());
        ProductRequest::find($this->request_id)
            ->update([
                'status' => 'failed',
            ]);
    }
}
