<?php
namespace App\Jobs;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Cache\RateLimiting\RateLimited;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use App\Models\ProductRequest;
use App\Models\EC2Product;

use App\Services\EC2Service;
use App\Jobs\Middleware\IsAuthorized;

/**
 * Job that handles the request to create a new Ec2 Product for an org
 */
class ProcessCreateProduct implements ShouldQueue{
    use Queueable;
    public function __construct(
        //public EC2Service $Ec2Service,
    ){}

    public function handle(
        Request $request
    ): void{
        // // validate auth and request
        if(!Auth::user()){
            throw new \Exception('User not authenticated');
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Log::info("creating new ec2 product...");

        //insert product_request
        $req = ProductRequest::create(['type' => 'ec2',]);

        //critical section

        //$Ec2Service = new EC2Service();

        //res = spawn real ec2 instance
        // $res = $Ec2Service->new([
        //     'name' => $request->name,
        // ]);


        $res = [
            'instance_id' => "001",

        ];

        Log::info("ec2 product successfuly created {}", $res['instance_id']);


        // add UOD product details to db
        EC2Product::create([
            'instance_id' => $res['instance_id'],
            'details' => json_encode($res), //fix
        ]);

        //update product_request to accepted
        $req->status = 'accepted';
        $req->save();

        //redirect()->route('servers')->with('success', 'Product created successfully!');
    }

    public function failed(?\Throwable $exception): void {
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
