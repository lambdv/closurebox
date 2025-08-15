<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Job to process request to get a new PGDB Product
 */
class NewPGDBProduct implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(

    )
    {
        
    }

    /**
     * Execute the job.
     */
    public function handle(
        string $pgdb_request_id,
        string $userid,
        string $pgdb_name,
    ): void
    {
        //get user's oid from PGDB_roles
        //

        //create real database with db name

        //assign user's oid role to the 
    }

    public function failed(?\Throwable $exception): void {
    }

    public function middleware() {
        return [
           new IsAuthorized,
            // new RateLimited('create-product'),
            // new WithoutOverlapping($this->orginization->id),
        ];
    }
}
