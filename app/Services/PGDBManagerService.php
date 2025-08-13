<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service class to manage PGDBs
 */
class PGDBManagerService
{
    private $connection;

    public function __construct()
    {
        // Connect to the main postgres database (not a specific database)
        $this->connection = DB::connection('pg_cluster_1');
    }

    /**
     * Creates a new database on the PGDB cluster for a customer
     * customer represented as a User model tied to a role login
     * @return void
     */
    public function createNewDatabase(){}

    /**
     * creates a new loginable role on the PGDB cluster for a User model
     * @return void
     */
    public function createNewUser(){}

    /**
     * get all users on the PGDB cluster
     * @return void
     */
    public function listUsers(): array {
        $users = $this->connection->select("SELECT * FROM pg_roles");
        return $users;
    }

    /**
     * get all databases on the PGDB cluster
     * @return void
     */
    public function listDatabases(){}




}


