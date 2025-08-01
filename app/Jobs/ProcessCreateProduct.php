<?php
namespace App\Jobs;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\AWS\EC2Service;
use App\Job\Middleware\IsAuthorized;

/**
 * Job that handles the request to create a new Ec2 Product for an org
 */
class ProcessCreateProduct implements ShouldQueue{
    use Queueable;
    
    public function middleware() {
        return [new IsAuthorized];
    }

    public function __construct(public EC2Service $service,){}

    public function handle(
        Request $req
    ): void{
        // validate auth and request 
        //log
        //insert product_request
        
        //critical section
        {
            // instanceDetails = spawn real ec2 instance
            // add UOD product details to db 
        }
        //update product_request to accepted
    }

    public function failed(?Throwable $exception): void {
        //update product_request to fail
        //log fail
        //make sure instance is deleted
        // Send user notification of failure
    }


}
