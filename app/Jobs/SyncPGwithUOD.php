<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\PGDBManagerService;
use App\Models\PGDBProduct;
use App\Models\PGDBRole;
use Illuminate\Support\Facades\Log;
/**
 * Sync postgres server/cluster roles and databases with UOD PGDB_Products and PGDB_Roles
 */
class SyncPGwithUOD implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $manager = new PGDBManagerService();

        // 1) Ensure all app roles exist on the cluster (loginable, non-superuser)
        $appRoles = PGDBRole::all();
        foreach ($appRoles as $appRole) {
            $roleName = $appRole->username;
            if (!$manager->doesRoleExists($roleName)) {
                try {
                    $manager->createNewLoginRole($roleName, $appRole->password, []);
                    Log::info("Created missing cluster role for app role: {$roleName}");
                } catch (\Throwable $e) {
                    Log::error("Failed creating cluster role {$roleName}: " . $e->getMessage());
                }
            }
        }

        // 2) Ensure all app databases exist on the cluster with correct owner
        $appProducts = PGDBProduct::all();
        foreach ($appProducts as $product) {
            $databaseName = $product->instance_id;
            if (!$databaseName) {
                continue;
            }

            if (!$manager->doesDatabaseExists($databaseName)) {
                // Determine owner role: first role linked to this product
                $ownerRole = PGDBRole::where('pgdb_product_id', $product->id)->first();
                if (!$ownerRole) {
                    Log::warning("No owner role found for product {$product->id} ({$databaseName}); skipping DB creation");
                    continue;
                }

                // Ensure owner role exists before DB creation
                if (!$manager->doesRoleExists($ownerRole->username)) {
                    try {
                        $manager->createNewLoginRole($ownerRole->username, $ownerRole->password, []);
                        Log::info("Created missing cluster role {$ownerRole->username} before creating database {$databaseName}");
                    } catch (\Throwable $e) {
                        Log::error("Failed creating owner role {$ownerRole->username} for database {$databaseName}: " . $e->getMessage());
                        continue;
                    }
                }

                try {
                    $manager->createNewDatabaseForUser($databaseName, $ownerRole->username);
                    Log::info("Created missing database {$databaseName} with owner {$ownerRole->username}");
                } catch (\Throwable $e) {
                    Log::error("Failed creating database {$databaseName}: " . $e->getMessage());
                }
            }
        }

        // 3) Optional visibility: log cluster items that aren't in app UoD (only customer roles/dbs)
        try {
            $clusterRoles = $manager->getUsers();
            $clusterCustomerRoleNames = [];
            foreach ((array) $clusterRoles as $role) {
                // Only loginable, non-superuser roles
                if (($role->rolcanlogin ?? false) && !($role->rolsuper ?? false)) {
                    $clusterCustomerRoleNames[] = $role->rolname;
                }
            }

            $appRoleNames = PGDBRole::pluck('username')->all();
            $extraClusterRoles = array_values(array_diff($clusterCustomerRoleNames, $appRoleNames));
            if (!empty($extraClusterRoles)) {
                Log::warning('Cluster has loginable non-superuser roles not present in app UoD', ['roles' => $extraClusterRoles]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed while listing/logging cluster roles: ' . $e->getMessage());
        }

        try {
            $clusterDatabases = $manager->getAllDatabases();
            $clusterDbNames = [];
            foreach ((array) $clusterDatabases as $db) {
                $name = $db->datname ?? null;
                if (!$name) { continue; }
                // Exclude system templates/common DBs
                if (in_array($name, ['template0', 'template1', 'postgres'])) { continue; }
                $clusterDbNames[] = $name;
            }

            $appDbNames = PGDBProduct::pluck('instance_id')->filter()->all();
            $extraClusterDbs = array_values(array_diff($clusterDbNames, $appDbNames));
            if (!empty($extraClusterDbs)) {
                Log::warning('Cluster has databases not present in app UoD', ['databases' => $extraClusterDbs]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed while listing/logging cluster databases: ' . $e->getMessage());
        }
    }
}
