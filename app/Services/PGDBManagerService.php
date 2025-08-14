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
    public function createNewDatabase(  
        string $databaseName,
        string $owner
    ){
        try{
            $this->connection->select("CREATE DATABASE $databaseName WITH OWNER $owner");
        }
        catch(Exception $e){
            Log::error("Error creating database: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * creates a new loginable role on the PGDB cluster for a User model
     * @return void
     */
    public function createNewUser(
        string $username,
        string $password
    ){
       try{
        $this->connection->select("CREATE ROLE $username WITH LOGIN PASSWORD '$password'");
       } 
       catch (Exception $e) {
        Log::error("Error creating user: " . $e->getMessage());
        throw $e;
       }    
    }

    /**
     * get all users on the PGDB cluster
     * @return void
     */
    public function getUsers(): array {
        $users = $this->connection->select("SELECT * FROM pg_roles");
        return $users;
    }

    /**
     * get all databases on the PGDB cluster
     * @return void
     */
    public function getAllDatabases(): array {
        $databases = $this->connection->select("SELECT * FROM pg_database");
        return $databases;
    }

    public function getDatabasesForUser() {}



}


