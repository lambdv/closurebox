<?php
namespace App\Jobs;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Cache\RateLimiting\RateLimited;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use App\Models\ProductRequest;
use App\Models\EC2Product;
use App\Services\EC2Service;
use App\Services\MockEC2Service;
use App\Jobs\Middleware\IsAuthorized;

/**
 * Job that handles the request to create a new Ec2 Product for an org
 */
class ProcessCreateProduct implements ShouldQueue{
    use Queueable;

    public function __construct(public int $request_id, public int $organization_id)
    {
    }

    public function handle(): void{
        Log::info("creating new ec2 product...");

        // //insert product_request
        // $this->req->status = 'pending';
        // $this->req->save();

        // //critical section

        try{
            $Ec2Service = new EC2Service();
            $res = $Ec2Service->new([
                'name' => 'Server-' . $this->request_id,
            ]);
        }
        catch(\Exception $e){
            $this->fail($e);
            return;
        }

        // Extract instance ID from the AWS Result object
        $instanceId = $res['Reservations'][0]['Instances'][0]['InstanceId'];
        Log::info("ec2 product successfully created: " . $instanceId);

        // add EC2 product details to db
        EC2Product::create([
            'organization_id' => $this->organization_id ?? 1,
            'instance_id' => $instanceId,
            'details' => $res->toArray(),
            'status' => 'pending',
        ]);

        //update product_request to accepted

        ProductRequest::find($this->request_id)
            ->update([
                'status' => 'accepted',
            ]);
    }

    public function failed(?\Throwable $exception): void {
        ProductRequest::find($this->request_id)
            ->update([
                'status' => 'declined',
            ]);

        //update product_request to fail
        //log fail
        //make sure instance is deleted
        // Send user notification of failure
    }
    
    public function middleware() {
        return [
           new IsAuthorized,
            // new RateLimited('create-product'),
            // new WithoutOverlapping($this->orginization->id),
        ];
    }
}
