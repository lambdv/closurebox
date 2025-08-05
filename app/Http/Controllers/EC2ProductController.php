<?php
namespace App\Http\Controllers;
use App\Models\EC2Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Jobs\ProcessCreateProduct;
use App\Services\EC2Service;

class EC2ProductController extends Controller {

    /**
     * view all serverd for a user
     */
    public function viewServers(){
        $ec2Products = EC2Product::all();
        return Inertia::render('dashboard/servers/page', [
            'servers' => $ec2Products,
        ]);
    }

    /**
     * endpoint to create a new server
     */
    public function createServer(
        Request $request, 
        //EC2Service $ec2Service
    ){
        // $request->validate([
        //     'name' => 'required|string|max:255',
        // ]);
        try{

            //call dispatch job
            ProcessCreateProduct::dispatch() -> onQueue('products');

            return redirect()
                ->route('servers')
                ->with('success', 'Server created successfully');
        }
        catch(\Exception $e){
            return redirect()
                ->route('servers')
                ->withErrors(['errors' => 'Failed to create server: ' . $e->getMessage()]);
        }

    }
}
