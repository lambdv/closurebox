<?php
namespace App\Jobs;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Jobs\Middleware\IsAuthorized;
use App\Services\PGDBManagerService;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\PGDBRole;
use App\Models\PGDBProduct;
use App\Models\ProductRequest;

class NewPGDBProduct implements ShouldQueue
{
    use Queueable;

    public string $pgdb_instance_id;
    public string $pgdb_username;
    public string $pgdb_password;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $request_id,
        public string $user_id,
        public string $pgdb_name,
    )
    {
        $this->pgdb_instance_id = PGDBManagerService::generateRandomUniqueDatabaseName($this->user_id);
        $this->pgdb_username = PGDBManagerService::generateRandomUniqueUsername($this->user_id);
        $this->pgdb_password = PGDBManagerService::generateSecurePassword();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $product_request = ProductRequest::find($this->request_id);
            
            if (!$product_request) {
                throw new Exception("Product request not found with ID: {$this->request_id}");
            }

            $pg_manager = new PGDBManagerService();

            $pg_manager->createNewLoginRole($this->pgdb_username, $this->pgdb_password, []);

            $pg_manager->createNewDatabaseForUser($this->pgdb_instance_id, $this->pgdb_username);



            // Create the product record
            $pgdb_product = PGDBProduct::create([
                //'pgdb_role_id' => $pgdb_role->id,
                'user_id' => $this->user_id,
                'name' => $this->pgdb_name,
                'instance_id' => $this->pgdb_instance_id,
                'status' => 'active',
            ]);

            // Create the role record in our database
            $pgdb_role = PGDBRole::create([
                'pgdb_product_id' => $pgdb_product->id,
                'user_id' => $this->user_id,
                'username' => $this->pgdb_username,
                'password' => $this->pgdb_password,
            ]);

            // Update the product request status
            $product_request->update([
                'status' => 'accepted',
            ]);
            Log::info("Successfully created PGDB product for user {$this->user_id} with username {$this->pgdb_username}");
        } catch (Exception $e) {
            Log::error("Error creating PGDB product: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(?\Throwable $exception): void {
        try {
            $product_request = ProductRequest::find($this->request_id);
            
            if ($product_request) {
                $product_request->update([
                    'status' => 'failed',
                ]);
            }

            // Clean up any created resources
            $pg_manager = new PGDBManagerService();
            
            // Delete the role from PostgreSQL cluster (will handle non-existent roles gracefully)
            try {
                $pg_manager->deleteRole($this->pgdb_username);
            } catch (Exception $e) {
                Log::warning("Failed to delete PostgreSQL role {$this->pgdb_username}: " . $e->getMessage());
            }
            
            // Delete the role record from our database
            try {
                PGDBRole::where('username', $this->pgdb_username)->delete();
            } catch (Exception $e) {
                Log::warning("Failed to delete role record {$this->pgdb_username}: " . $e->getMessage());
            }
            
            // Delete the database (will handle non-existent databases gracefully)
            try {
                $pg_manager->deleteDatabase($this->pgdb_instance_id);
            } catch (Exception $e) {
                Log::warning("Failed to delete database {$this->pgdb_instance_id}: " . $e->getMessage());
            }
            
            // Delete any product records
            try {
                PGDBProduct::where('name', $this->pgdb_name)->delete();
            } catch (Exception $e) {
                Log::warning("Failed to delete product record {$this->pgdb_name}: " . $e->getMessage());
            }
            
            Log::info("Cleanup completed for failed PGDB product creation");
            
        } catch (Exception $e) {
            Log::error("Error during cleanup in failed method: " . $e->getMessage());
        }
    }

    public function middleware() {
        return [
           new IsAuthorized,
            // new RateLimited('create-product'),
            // new WithoutOverlapping($this->orginization->id),
        ];
    }
}
