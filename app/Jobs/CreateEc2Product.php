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
class CreateEc2Product implements ShouldQueue{
    use Queueable;

    public $aws_result;
    public ?string $instanceId = null;

    public function __construct(
        public array $params,
        public int $request_id, //TODO: request should have an assoiated org and user id
        public int $organization_id
    ){}

    public function handle(): void{
        Log::info("Creating new EC2 Product...");

        try{
            $res = new EC2Service()->new($this->params);
            $this->aws_result = $res;
        }
        catch(\Exception $e){
            $this->fail($e);
            return;
        }

        // Extract instance ID from the AWS Result object (support both real and mock shapes)
        $instanceId = $res['Instances'][0]['InstanceId'] ?? ($res['Reservations'][0]['Instances'][0]['InstanceId'] ?? null);
        if ($instanceId === null) {
            throw new \RuntimeException('InstanceId missing from AWS response');
        }
        $this->instanceId = $instanceId;
        Log::info("ec2 product successfully created: " . $instanceId);

        // add EC2 product details to db
        EC2Product::create([
            'organization_id' => $this->organization_id ?? 1,
            'instance_id' => $instanceId,
            'details' => $res->toArray(),
            'status' => 'active',
        ]);

        //update product_request to accepted

        ProductRequest::find($this->request_id)
            ->update([
                'status' => 'accepted',
            ]);
    }

    public function failed(?\Throwable $exception): void {
        //update product_request to fail
        ProductRequest::find($this->request_id)
            ->update([
                'status' => 'declined',
            ]);

        //log fail
        Log::error("Failed to create EC2 Product: " . $exception->getMessage());
        //make sure instance is deleted if we have an instance id
        if (!empty($this->instanceId)) {
            try {
                (new EC2Service())->terminate([$this->instanceId]);
            } catch (\Throwable $t) {
                Log::error('Failed to terminate instance during job failure cleanup: ' . $t->getMessage());
            }
        }
        // Send user notification of failure
        //TODO: send user notification of failure
    }

    public function middleware() {
        return [
           new IsAuthorized,
            // new RateLimited('create-product'),
            // new WithoutOverlapping($this->orginization->id),
        ];
    }
}
