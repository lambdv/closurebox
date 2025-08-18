<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use PDO;
use PDOException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\PGDBProduct;

class PostgresAdminController extends Controller
{
	public function index(): Response
	{
		return Inertia::render('PostgresAdmin/Index');
	}

	public function testEnvironment(): JsonResponse
	{
		$extensions = [
			'pdo' => extension_loaded('pdo'),
			'pdo_pgsql' => extension_loaded('pdo_pgsql'),
			'pgsql' => extension_loaded('pgsql'),
		];
		
		$drivers = PDO::getAvailableDrivers();
		
		return response()->json([
			'success' => true,
			'extensions' => $extensions,
			'pdo_drivers' => $drivers,
			'php_version' => PHP_VERSION,
			'os' => PHP_OS,
		]);
	}

	private function assertOwnedDatabase(string $databaseName): void
	{
		$owned = PGDBProduct::where('instance_id', $databaseName)
			->where('user_id', Auth::id())
			->exists();
		if (!$owned) {
			abort(403, 'You do not have access to this database.');
		}
	}

	private function enforceAllowedHost(string $host): void
	{
		$allowed = array_map('trim', explode(',', (string) env('PG_ALLOWED_HOSTS', 'localhost,127.0.0.1')));
		if (!in_array($host, $allowed, true)) {
			abort(403, 'Host is not allowed.');
		}
	}

	private function buildPdoDsn(string $connectionString, ?array &$parsedOut = null): string
	{
		$dsn = $connectionString;
		$parsed = parse_url($connectionString);
		if ($parsed && isset($parsed['scheme']) && $parsed['scheme'] === 'postgresql') {
			$dsn = sprintf(
				'pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s',
				$parsed['host'] ?? 'localhost',
				$parsed['port'] ?? '5432',
				ltrim($parsed['path'] ?? '', '/'),
				$parsed['user'] ?? '',
				$parsed['pass'] ?? ''
			);
		}
		if ($parsedOut !== null) {
			$parsedOut = $parsed ?: [];
		}
		return $dsn;
	}

	private function extractConnectionMeta(string $connectionString): array
	{
		// Returns ['host' => string, 'port' => string, 'dbname' => string]
		$parsed = parse_url($connectionString);
		if ($parsed && isset($parsed['scheme']) && $parsed['scheme'] === 'postgresql') {
			return [
				'host' => $parsed['host'] ?? 'localhost',
				'port' => (string) ($parsed['port'] ?? '5432'),
				'dbname' => ltrim($parsed['path'] ?? '', '/'),
			];
		}
		// Handle DSN format: pgsql:host=...;port=...;dbname=...;user=...;password=...
		if (is_string($connectionString) && str_starts_with($connectionString, 'pgsql:')) {
			$kv = substr($connectionString, strlen('pgsql:'));
			$parts = array_filter(array_map('trim', explode(';', $kv)));
			$data = [];
			foreach ($parts as $part) {
				[$k, $v] = array_pad(explode('=', $part, 2), 2, null);
				if ($k !== null && $v !== null) {
					$data[strtolower($k)] = $v;
				}
			}
			return [
				'host' => $data['host'] ?? 'localhost',
				'port' => (string) ($data['port'] ?? '5432'),
				'dbname' => (string) ($data['dbname'] ?? ''),
			];
		}
		// Fallback
		return [
			'host' => 'localhost',
			'port' => '5432',
			'dbname' => '',
		];
	}

	private function applySessionGuards(PDO $pdo): void
	{
		$timeoutMs = (int) env('PG_STMT_TIMEOUT_MS', '5000');
		if ($timeoutMs > 0) {
			$pdo->exec('SET statement_timeout TO ' . $timeoutMs);
		}
	}

	public function connect(Request $request): JsonResponse
	{
		$request->validate([
			'connectionString' => 'required|string',
		]);

		try {
			$connectionString = $request->input('connectionString');

			if (!extension_loaded('pdo_pgsql')) {
				return response()->json([
					'success' => false,
					'message' => 'PostgreSQL PDO driver not available. Please install pdo_pgsql extension.'
				], 500);
			}

			$parsed = [];
			$pdoConnectionString = $this->buildPdoDsn($connectionString, $parsed);

			$meta = $this->extractConnectionMeta($connectionString);
			$host = $meta['host'];
			$dbName = $meta['dbname'];
			$this->enforceAllowedHost($host);
			$this->assertOwnedDatabase($dbName);

			$pdo = new PDO($pdoConnectionString);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->applySessionGuards($pdo);

			$stmt = $pdo->query('SELECT 1 as test');
			$result = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($result && $result['test'] == 1) {
				return response()->json([
					'success' => true,
					'message' => 'Connection successful to ' . $host . ':' . ($meta['port'] ?? '5432') . '/' . $dbName
				]);
			} else {
				return response()->json([
					'success' => false,
					'message' => 'Connection test failed - unexpected response'
				], 500);
			}
		} catch (PDOException $e) {
			$errorMessage = $e->getMessage();
			if (str_contains($errorMessage, 'could not find driver')) {
				return response()->json([
					'success' => false,
					'message' => 'PostgreSQL driver not found. Please ensure pdo_pgsql extension is installed and enabled.'
				], 500);
			}
			return response()->json([
				'success' => false,
				'message' => 'Connection failed: ' . $errorMessage
			], 500);
		} catch (\Exception $e) {
			return response()->json([
				'success' => false,
				'message' => 'Unexpected error: ' . $e->getMessage()
			], 500);
		}
	}

	public function executeQuery(Request $request): JsonResponse
	{
		$request->validate([
			'connectionString' => 'required|string',
			'query' => 'required|string',
			'allowWrite' => 'sometimes|boolean',
			'maxRows' => 'sometimes|integer|min:1|max:' . (int) env('PG_MAX_ROWS', 2000),
		]);

		try {
			$connectionString = $request->input('connectionString');
			$query = $request->input('query');
			$allowWrite = (bool) $request->boolean('allowWrite', true);
			$maxRows = (int) ($request->input('maxRows', (int) env('PG_DEFAULT_ROWS', 500)));
			$maxRows = max(1, min($maxRows, (int) env('PG_MAX_ROWS', 2000)));

			$pdoConnectionString = $this->buildPdoDsn($connectionString, $parsed);
			$meta = $this->extractConnectionMeta($connectionString);
			$host = $meta['host'];
			$dbName = $meta['dbname'];
			$this->enforceAllowedHost($host);
			$this->assertOwnedDatabase($dbName);

			$isWrite = (bool) preg_match('/^\s*(INSERT|UPDATE|DELETE|ALTER|DROP|CREATE|TRUNCATE|GRANT|REVOKE|REINDEX|VACUUM|ANALYZE|COMMENT|REFRESH|CLUSTER|COPY|CALL|DO)\b/i', $query);
			if ($isWrite && !$allowWrite) {
				return response()->json([
					'success' => false,
					'message' => 'Write operations are disabled in safe mode. Enable write to proceed.'
				], 403);
			}

			$isSelect = (bool) preg_match('/^\s*(WITH\s+.*?\)\s*)?SELECT\b/is', $query);
			if ($isSelect && !preg_match('/\blimit\s+\d+/i', $query) && !preg_match('/\bfetch\s+first\s+\d+/i', $query)) {
				$trimmed = rtrim($query, "; \t\n\r");
				$query = $trimmed . ' LIMIT ' . $maxRows;
			}

			$pdo = new PDO($pdoConnectionString);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->applySessionGuards($pdo);

			$start = microtime(true);
			$stmt = $pdo->query($query);
			$durationMs = (int) round((microtime(true) - $start) * 1000);

			Log::info('PG query executed', [
				'user_id' => Auth::id(),
				'database' => $dbName,
				'is_write' => $isWrite,
				'duration_ms' => $durationMs,
			]);

			if ($stmt->columnCount() > 0) {
				$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$columns = [];
				if (count($results) > 0) {
					$columns = array_keys($results[0]);
				}
				return response()->json([
					'success' => true,
					'type' => 'select',
					'columns' => $columns,
					'results' => $results,
					'rowCount' => count($results)
				]);
			} else {
				$rowCount = $stmt->rowCount();
				return response()->json([
					'success' => true,
					'type' => 'modify',
					'rowCount' => $rowCount,
					'message' => "Query executed successfully. {$rowCount} row(s) affected."
				]);
			}
		} catch (PDOException $e) {
			return response()->json([
				'success' => false,
				'message' => 'Query execution failed: ' . $e->getMessage()
			], 500);
		}
	}

	public function getTables(Request $request): JsonResponse
	{
		$request->validate([
			'connectionString' => 'required|string',
		]);

		try {
			$connectionString = $request->input('connectionString');

			$pdoConnectionString = $this->buildPdoDsn($connectionString, $parsed);
			$meta = $this->extractConnectionMeta($connectionString);
			$host = $meta['host'];
			$dbName = $meta['dbname'];
			$this->enforceAllowedHost($host);
			$this->assertOwnedDatabase($dbName);

			$pdo = new PDO($pdoConnectionString);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->applySessionGuards($pdo);
			
			$query = "
				SELECT 
					table_name,
					table_type,
					table_schema
				FROM information_schema.tables 
				WHERE table_schema NOT IN ('information_schema', 'pg_catalog')
				ORDER BY table_schema, table_name
			";
			
			$stmt = $pdo->query($query);
			$tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			return response()->json([
				'success' => true,
				'tables' => $tables
			]);
		} catch (PDOException $e) {
			return response()->json([
				'success' => false,
				'message' => 'Failed to fetch tables: ' . $e->getMessage()
			], 500);
		}
	}

	public function getTableStructure(Request $request): JsonResponse
	{
		$request->validate([
			'connectionString' => 'required|string',
			'tableName' => 'required|string',
		]);

		try {
			$connectionString = $request->input('connectionString');
			$tableName = $request->input('tableName');

			$pdoConnectionString = $this->buildPdoDsn($connectionString, $parsed);
			$meta = $this->extractConnectionMeta($connectionString);
			$host = $meta['host'];
			$dbName = $meta['dbname'];
			$this->enforceAllowedHost($host);
			$this->assertOwnedDatabase($dbName);
			
			$pdo = new PDO($pdoConnectionString);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->applySessionGuards($pdo);
			
			$query = "
				SELECT 
					column_name,
					data_type,
					is_nullable,
					column_default,
					character_maximum_length,
					numeric_precision,
					numeric_scale
				FROM information_schema.columns 
				WHERE table_name = ?
				ORDER BY ordinal_position
			";
			
			$stmt = $pdo->prepare($query);
			$stmt->execute([$tableName]);
			$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			return response()->json([
				'success' => true,
				'columns' => $columns
			]);
		} catch (PDOException $e) {
			return response()->json([
				'success' => false,
				'message' => 'Failed to fetch table structure: ' . $e->getMessage()
			], 500);
		}
	}
}
