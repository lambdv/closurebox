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

class CreatePGDBRole implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $user_id,
        public string $username,
        public string $password,
        public array $permissions = []
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try{
            $pg_manager = new PGDBManagerService();
            $role_name = $this->user_id . '_' . $this->username;
            $pg_manager->createNewLoginRole($role_name, $this->password, $this->permissions);

            PGDBRole::create([
                'user_id' => $this->user_id,
                'username' => $role_name,
                'password' => $this->password,
            ]);
        }
        catch (Exception $e) {
            Log::error("Error creating role: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(?\Throwable $exception): void {
        //delete the role from cluster and database
        $pg_manager = new PGDBManagerService();
        $pg_manager->deleteRole($this->user_id . '_' . $this->username);
        PGDBRole::where('username', $this->user_id . '_' . $this->username)->delete();
    }

    public function middleware() {
        return [
           new IsAuthorized,
            // new RateLimited('create-product'),
            // new WithoutOverlapping($this->orginization->id),
        ];
    }
}
