<?php
namespace App\Jobs;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Jobs\Middleware\IsAuthorized;
use Illuminate\Queue\SerializesModels;
use App\Services\PGDBManagerService;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\PGDBRole;
use App\Models\PGDBProduct;
use App\Models\ProductRequest;

class DeletePGDBProduct implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public PGDBProduct $pgdb_product,
        public PGDBRole $pgdb_role,
        )
        {
        }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $pg_manager = new PGDBManagerService();

        // Try deleting the database first, then the role. Log errors but continue.
        try {
            $pg_manager->deleteDatabase($this->pgdb_product->instance_id);
        } catch (Exception $e) {
            Log::error("Error deleting database in handle method: " . $e->getMessage());
        }

        try {
            $pg_manager->deleteRole($this->pgdb_role->username);
        } catch (Exception $e) {
            Log::error("Error deleting role in handle method: " . $e->getMessage());
        }

        // Always attempt to delete app records, regardless of cluster deletion outcome
        try {
            DB::transaction(function () {
                PGDBRole::where('id', $this->pgdb_role->id)->delete();
                PGDBProduct::where('id', $this->pgdb_product->id)->delete();
            }, attempts: 3);
        } catch (Exception $e) {
            Log::error("Error deleting application records in handle method: " . $e->getMessage());
        }
    }

    public function failed(?\Throwable $exception): void {
        Log::error("Error during cleanup in failed method: " . $exception->getMessage());
    }

    public function middleware() {
        return [
           new IsAuthorized,
            // new RateLimited('create-product'),
            // new WithoutOverlapping($this->orginization->id),
        ];
    }
}
