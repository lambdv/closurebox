<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
// use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Str;
/**
 * Service class to manage PGDBs
 */
class PGDBManagerService
{
    private $connection;

    public function __construct(
        string $clusterName = 'pg_cluster_1'
    )
    {
        $this->connection = DB::connection($clusterName);
    }

    /**
     * Creates a new database on the PGDB cluster for a customer
     * customer represented as a User model tied to a role login
     * @return void
     */
    public function createNewDatabaseForUser(
        string $databaseName,
        string $ownerRoleName
    ){
        // Validate database name
        $this->validateDatabaseName($databaseName);
        $this->validateRoleName($ownerRoleName);

        try{
            // Use proper quoting for database and role names
            $quotedDbName = $this->quoteIdentifier($databaseName);
            $quotedOwnerRole = $this->quoteIdentifier($ownerRoleName);
            
            $this->connection->select("
                CREATE DATABASE $quotedDbName WITH OWNER $quotedOwnerRole
            ");
        }
        catch(Exception $e){
            Log::error("Error creating database: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * creates a new loginable role on the PGDB cluster for a customer
     * TODO: at this stage, create roles on sign up and thats it
     * TODO: let users request creating roles. eg: making a login for a specific app
     * TODO: create roles for invited users of an organization
     * least privilege principle: can only view and modify databases explicitly granted
     * baseline: no privileges until assigned
     * creating new databases goes through the app
     * @return void
     */
    public function createNewLoginRole(
        string $username,
        string $password,
        array $permissions = [] //TODO: remove perms
    ){
        // Validate role name
        $this->validateRoleName($username);
        
        // Validate password
        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }

        try{
            $perms = implode(' ', $permissions);
            
            // Use proper quoting for role name
            $quotedUsername = $this->quoteIdentifier($username);

            $this->connection->select("
                CREATE ROLE $quotedUsername WITH LOGIN PASSWORD '$password'
                NOSUPERUSER NOCREATEDB NOCREATEROLE NOINHERIT
            ");
        } 
        catch (Exception $e) {
            Log::error("Error creating user: " . $e->getMessage());
            throw $e;
        }    
    }



    public function deleteRole(
        string $username
    ){
        try{
            // Check if role exists before trying to delete it
            $roleExists = $this->connection->select("SELECT 1 FROM pg_roles WHERE rolname = ?", [$username]);
            
            if (empty($roleExists)) {
                Log::info("Role {$username} does not exist, skipping deletion");
                return;
            }
            
            $this->connection->select("
                DROP ROLE $username
            ");
        }
        catch(Exception $e){
            Log::error("Error deleting role: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteDatabase(string $databaseName){
        try{
            // Check if database exists before trying to delete it
            $dbExists = $this->connection->select("SELECT 1 FROM pg_database WHERE datname = ?", [$databaseName]);
            
            if (empty($dbExists)) {
                Log::info("Database {$databaseName} does not exist, skipping deletion");
                return;
            }
            
            // Use proper quoting for database names that might contain special characters
            $quotedDbName = $this->quoteIdentifier($databaseName);
            $this->connection->select("DROP DATABASE $quotedDbName");
        }
        catch(Exception $e){
            Log::error("Error deleting database: " . $e->getMessage());
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


    public function doesDatabaseExists(string $databaseName): bool {
        $databaseExists = $this->connection->select("SELECT 1 FROM pg_database WHERE datname = ?", [$databaseName]);
        return !empty($databaseExists);
    }

    public function doesRoleExists(string $roleName): bool {
        $roleExists = $this->connection->select("SELECT 1 FROM pg_roles WHERE rolname = ?", [$roleName]);
        return !empty($roleExists);
    }

    public static function formatDatabaseName(string $databaseName, string $userId): string {
        return 'user_' . $userId . '_' . $databaseName;
    }

    public static function formatRoleName(string $roleName, string $userId): string {
        return 'user_' . $userId . '_' . $roleName;
    }
    
    public static function generateSecurePassword(): string {
        return Str::random(12);
    }

    public static function generateRandomUniqueUsername (string $userId): string {
        $found_unique = false;
        while (!$found_unique) {
            $username = self::formatRoleName(Str::random(12), $userId);
            $manager = new PGDBManagerService();
            if (!$manager->doesRoleExists($username)) {
                return $username;
            }
        }
        throw new Exception("Failed to generate a unique username");
    }

    public static function generateRandomUniqueDatabaseName (string $userId): string {
        $found_unique = false;
        while (!$found_unique) {
            $databaseName = self::formatDatabaseName(Str::random(12), $userId);
            $manager = new PGDBManagerService();
            if (!$manager->doesDatabaseExists($databaseName)) {
                return $databaseName;
            }
        }
        throw new Exception("Failed to generate a unique database name");
    }







    // public function assignUserToDatabase(
    //     string $username,
    //     string $databaseName,
    //     string $mode = 'read-only' // allowed values: 'read-only', 'full'
    // ) {
    //     if (!in_array($mode, ['read-only', 'full'])) { //validate
    //         throw new \InvalidArgumentException("Invalid mode: $mode");
    //     }
        
    //     //sanitize 
    //     $safeUsername = $this->quoteIdentifier($username);
    //     $safeDatabase = $this->quoteIdentifier($databaseName);
    //     $schema = 'public'; // assuming public schema; adjust if needed
    //     $safeSchema = $this->quoteIdentifier($schema);
    
    //     try {
    //         // Connect privilege (required for any access)
    //         $this->connection->statement("GRANT CONNECT ON DATABASE $safeDatabase TO $safeUsername;");
    
    //         if ($mode === 'read-only') {
    //             // Read-only privileges
    //             $this->connection->statement("GRANT USAGE ON SCHEMA $safeSchema TO $safeUsername;");
    //             $this->connection->statement("GRANT SELECT ON ALL TABLES IN SCHEMA $safeSchema TO $safeUsername;");
    //             $this->connection->statement("GRANT SELECT ON ALL SEQUENCES IN SCHEMA $safeSchema TO $safeUsername;");
    //             $this->connection->statement("ALTER DEFAULT PRIVILEGES IN SCHEMA $safeSchema GRANT SELECT ON TABLES TO $safeUsername;");
    //             $this->connection->statement("ALTER DEFAULT PRIVILEGES IN SCHEMA $safeSchema GRANT SELECT ON SEQUENCES TO $safeUsername;");
    //         } else {
    //             // Full (read-write) privileges
    //             $this->connection->statement("GRANT USAGE, CREATE ON SCHEMA $safeSchema TO $safeUsername;");
    //             $this->connection->statement("GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA $safeSchema TO $safeUsername;");
    //             $this->connection->statement("GRANT USAGE, SELECT, UPDATE ON ALL SEQUENCES IN SCHEMA $safeSchema TO $safeUsername;");
    //             $this->connection->statement("ALTER DEFAULT PRIVILEGES IN SCHEMA $safeSchema GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO $safeUsername;");
    //             $this->connection->statement("ALTER DEFAULT PRIVILEGES IN SCHEMA $safeSchema GRANT USAGE, SELECT, UPDATE ON SEQUENCES TO $safeUsername;");
    //         }
    //     } 
        
    //     catch (Exception $e) {
    //         Log::error("Error assigning user to database: " . $e->getMessage());
    //         throw $e;
    //     }
    // }


   


    private function quoteIdentifier(string $name): string {
        return '"' . str_replace('"', '""', $name) . '"';
    }

    /**
     * Validate database name according to PostgreSQL naming conventions
     */
    private function validateDatabaseName(string $databaseName): void
    {
        if (empty($databaseName)) {
            throw new Exception("Database name cannot be empty");
        }
        
        if (strlen($databaseName) > 63) {
            throw new Exception("Database name cannot exceed 63 characters");
        }
        
        // Check for spaces and other problematic characters
        if (strpos($databaseName, ' ') !== false) {
            throw new Exception("Database name cannot contain spaces. Use underscores or hyphens instead.");
        }
        
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_-]*$/', $databaseName)) {
            throw new Exception("Database name must start with a letter or underscore and contain only letters, numbers, underscores, and hyphens");
        }
        
        // Check for reserved keywords
        $reservedKeywords = ['all', 'analyse', 'analyze', 'and', 'any', 'array', 'as', 'asc', 'asymmetric', 'authorization', 'binary', 'both', 'case', 'cast', 'check', 'collate', 'column', 'constraint', 'create', 'cross', 'current_date', 'current_role', 'current_time', 'current_timestamp', 'current_user', 'default', 'deferrable', 'desc', 'distinct', 'do', 'else', 'end', 'except', 'false', 'for', 'foreign', 'freeze', 'from', 'full', 'grant', 'group', 'having', 'in', 'initially', 'inner', 'intersect', 'into', 'leading', 'limit', 'localtime', 'localtimestamp', 'natural', 'not', 'null', 'offset', 'on', 'only', 'or', 'order', 'outer', 'overlaps', 'placing', 'primary', 'references', 'select', 'session_user', 'some', 'symmetric', 'table', 'then', 'to', 'trailing', 'true', 'union', 'unique', 'user', 'using', 'when', 'where'];
        
        if (in_array(strtolower($databaseName), $reservedKeywords)) {
            throw new Exception("Database name cannot be a PostgreSQL reserved keyword");
        }
    }

    /**
     * Validate role name according to PostgreSQL naming conventions
     */
    private function validateRoleName(string $roleName): void
    {
        if (empty($roleName)) {
            throw new Exception("Role name cannot be empty");
        }
        
        if (strlen($roleName) > 63) {
            throw new Exception("Role name cannot exceed 63 characters");
        }
        
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $roleName)) {
            throw new Exception("Role name must start with a letter or underscore and contain only letters, numbers, and underscores");
        }
        
        // Check for reserved keywords
        $reservedKeywords = ['all', 'analyse', 'analyze', 'and', 'any', 'array', 'as', 'asc', 'asymmetric', 'authorization', 'binary', 'both', 'case', 'cast', 'check', 'collate', 'column', 'constraint', 'create', 'cross', 'current_date', 'current_role', 'current_time', 'current_timestamp', 'current_user', 'default', 'deferrable', 'desc', 'distinct', 'do', 'else', 'end', 'except', 'false', 'for', 'foreign', 'freeze', 'from', 'full', 'grant', 'group', 'having', 'in', 'initially', 'inner', 'intersect', 'into', 'leading', 'limit', 'localtime', 'localtimestamp', 'natural', 'not', 'null', 'offset', 'on', 'only', 'or', 'order', 'outer', 'overlaps', 'placing', 'primary', 'references', 'select', 'session_user', 'some', 'symmetric', 'table', 'then', 'to', 'trailing', 'true', 'union', 'unique', 'user', 'using', 'when', 'where'];
        
        if (in_array(strtolower($roleName), $reservedKeywords)) {
            throw new Exception("Role name cannot be a PostgreSQL reserved keyword");
        }
    }
}


