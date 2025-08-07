<?php
namespace App\Http\Controllers;
use App\Models\EC2Product;
use App\Models\Organization;
use App\Models\OrganizationMember;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Jobs\ProcessCreateProduct;
use Illuminate\Support\Facades\Log;
class EC2ProductController extends Controller
{
    public function createServer(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        try{
            Log::info('Creating server: ' . $request->name);
                            
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
