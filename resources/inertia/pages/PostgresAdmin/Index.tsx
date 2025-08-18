import React, { useState, useRef } from 'react';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { Button } from '@/components/cn/button';
import { Input } from '@/components/cn/input';
import { Label } from '@/components/cn/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/cn/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/cn/tabs';
import { Badge } from '@/components/cn/badge';
import { 
  Database, 
  Play, 
  Table, 
  Columns, 
  CheckCircle, 
  XCircle, 
  Loader2,
  RefreshCw,
  ShieldAlert
} from 'lucide-react';

interface QueryResult {
  type: 'select' | 'modify';
  columns?: string[];
  results?: any[];
  rowCount?: number;
  message?: string;
}

interface TableInfo {
  table_name: string;
  table_type: string;
  table_schema: string;
}

interface ColumnInfo {
  column_name: string;
  data_type: string;
  is_nullable: string;
  column_default: string | null;
  character_maximum_length: number | null;
  numeric_precision: number | null;
  numeric_scale: number | null;
}

const WRITE_REGEX = /^\s*(INSERT|UPDATE|DELETE|ALTER|DROP|CREATE|TRUNCATE|GRANT|REVOKE|REINDEX|VACUUM|ANALYZE|COMMENT|REFRESH|CLUSTER|COPY|CALL|DO)\b/i;

export default function PostgresAdmin() {
  const [connectionString, setConnectionString] = useState('pgsql:host=localhost;port=5432;dbname=user_1_iwzKZvLVbF5v;user=user_1_t8IRM6hPPEyZ;password=Tt3yKoobvcZq');
  const [isConnected, setIsConnected] = useState(false);
  const [connectionStatus, setConnectionStatus] = useState<'idle' | 'connecting' | 'connected' | 'error'>('idle');
  const [connectionMessage, setConnectionMessage] = useState('');

  // Safety controls
  const [safeMode, setSafeMode] = useState<boolean>(true); // read-only by default
  const [maxRows, setMaxRows] = useState<number>(500);

  const [query, setQuery] = useState('SELECT * FROM information_schema.tables LIMIT 10');
  const [queryResult, setQueryResult] = useState<QueryResult | null>(null);
  const [isExecuting, setIsExecuting] = useState(false);
  const [executionMessage, setExecutionMessage] = useState('');
  
  const [tables, setTables] = useState<TableInfo[]>([]);
  const [selectedTable, setSelectedTable] = useState<string>('');
  const [tableStructure, setTableStructure] = useState<ColumnInfo[]>([]);
  const [isLoadingTables, setIsLoadingTables] = useState(false);
  const [isLoadingStructure, setIsLoadingStructure] = useState(false);
  const [environmentInfo, setEnvironmentInfo] = useState<any>(null);

  const queryInputRef = useRef<HTMLTextAreaElement>(null);

  const isWriteQuery = (sql: string) => WRITE_REGEX.test(sql);

  const testConnection = async () => {
    setConnectionStatus('connecting');
    setConnectionMessage('');
    
    try {
      const response = await axios.post('/postgres-admin/connect', {
        connectionString
      });
      
      if (response.data.success) {
        setIsConnected(true);
        setConnectionStatus('connected');
        setConnectionMessage(response.data.message);
        loadTables();
      } else {
        setConnectionStatus('error');
        setConnectionMessage(response.data.message);
      }
    } catch (error: any) {
      setConnectionStatus('error');
      setConnectionMessage(error.response?.data?.message || 'Connection failed');
    }
  };

  const testEnvironment = async () => {
    try {
      const response = await axios.get('/postgres-admin/test-env');
      if (response.data.success) {
        setEnvironmentInfo(response.data);
      }
    } catch (error: any) {
      console.error('Failed to test environment:', error);
    }
  };

  const executeQuery = async () => {
    if (!query.trim()) return;

    const willWrite = isWriteQuery(query);
    if (willWrite && safeMode) {
      setExecutionMessage('Write operations are disabled in Safe Mode. Disable Safe Mode to proceed.');
      return;
    }
    if (willWrite && !safeMode) {
      const confirmed = window.confirm('This query will modify your database. Do you want to proceed?');
      if (!confirmed) return;
    }

    setIsExecuting(true);
    setExecutionMessage('');
    setQueryResult(null);
    
    try {
      const response = await axios.post('/postgres-admin/execute-query', {
        connectionString,
        query: query.trim(),
        allowWrite: !safeMode,
        maxRows: maxRows,
      });
      
      if (response.data.success) {
        setQueryResult(response.data);
        setExecutionMessage(response.data.message || 'Query executed successfully');
      } else {
        setExecutionMessage(response.data.message || 'Query execution failed');
      }
    } catch (error: any) {
      setExecutionMessage(error.response?.data?.message || 'Query execution failed');
    } finally {
      setIsExecuting(false);
    }
  };

  const loadTables = async () => {
    setIsLoadingTables(true);
    try {
      const response = await axios.post('/postgres-admin/tables', {
        connectionString
      });
      
      if (response.data.success) {
        setTables(response.data.tables);
      }
    } catch (error: any) {
      console.error('Failed to load tables:', error);
    } finally {
      setIsLoadingTables(false);
    }
  };

  const loadTableStructure = async (tableName: string) => {
    setSelectedTable(tableName);
    setIsLoadingStructure(true);
    
    try {
      const response = await axios.post('/postgres-admin/table-structure', {
        connectionString,
        tableName
      });
      
      if (response.data.success) {
        setTableStructure(response.data.columns);
      }
    } catch (error: any) {
      console.error('Failed to load table structure:', error);
    } finally {
      setIsLoadingStructure(false);
    }
  };

  const formatValue = (value: any): string => {
    if (value === null || value === undefined) return 'NULL';
    if (typeof value === 'object') return JSON.stringify(value);
    return String(value);
  };

  return (
    <>
    <style>
        {
            `
            .dark .bg-white {
                background-color: #111827;
            }
            *{
                color: #e2dbdb;
                background-color: #131313;
            }
            `
        }
    </style>
      <Head title="PostgreSQL Admin" />
      
      <div className="container mx-auto p-6 space-y-6">
        <div className="flex items-center space-x-2">
          <Database className="h-8 w-8 text-blue-600" />
          <h1 className="text-3xl font-bold">PostgreSQL Admin</h1>
        </div>

        {/* Connection & Safety Section */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center space-x-2">
              <Database className="h-5 w-5" />
              Database Connection
            </CardTitle>
            <CardDescription>
              Connect to your PostgreSQL database using a DSN connection string
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="connection-string">Connection String (DSN)</Label>
              <div className="flex flex-col md:flex-row md:items-center md:space-x-2 gap-2">
                <Input
                  id="connection-string"
                  value={connectionString}
                  onChange={(e) => setConnectionString(e.target.value)}
                  placeholder="pgsql:host=...;port=5432;dbname=...;user=...;password=..."
                  className="flex-1"
                />
                <Button
                  onClick={testConnection}
                  disabled={connectionStatus === 'connecting'}
                  className="min-w-[120px]"
                >
                  {connectionStatus === 'connecting' ? (
                    <Loader2 className="h-4 w-4 animate-spin" />
                  ) : (
                    <Play className="h-4 w-4" />
                  )}
                  {connectionStatus === 'connecting' ? 'Connecting...' : 'Connect'}
                </Button>
                <Button onClick={testEnvironment} variant="outline" className="min-w-[140px]">
                  Test Environment
                </Button>
              </div>
            </div>

            {/* Safe mode controls */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="col-span-2 p-3 rounded-md border bg-white dark:bg-zinc-900 dark:border-zinc-800">
                <div className="flex items-center justify-between">
                  <div className="flex items-center space-x-2">
                    <ShieldAlert className="h-5 w-5 text-amber-600" />
                    <div>
                      <div className="text-sm font-medium text-gray-900">Safe Mode (Read-only)</div>
                      <div className="text-xs text-gray-600">Blocks destructive queries unless disabled</div>
                    </div>
                  </div>
                  <div className="flex items-center space-x-2">
                    <Label htmlFor="safe-mode" className="text-sm">{safeMode ? 'On' : 'Off'}</Label>
                    <input
                      id="safe-mode"
                      type="checkbox"
                      checked={!safeMode ? true : false}
                      onChange={(e) => {
                        const enableWrites = e.target.checked;
                        if (enableWrites) {
                          const ok = window.confirm('Enabling write operations allows queries that modify your database (e.g., DROP/DELETE/ALTER). Proceed?');
                          if (!ok) return;
                        }
                        setSafeMode(!enableWrites ? true : false);
                      }}
                    />
                  </div>
                </div>
              </div>
              <div className="p-3 rounded-md border bg-white dark:bg-zinc-900 dark:border-zinc-800">
                <Label htmlFor="max-rows" className="text-sm">Max rows (SELECT)</Label>
                <Input
                  id="max-rows"
                  type="number"
                  min={1}
                  max={2000}
                  value={maxRows}
                  onChange={(e) => setMaxRows(Number(e.target.value) || 1)}
                />
              </div>
            </div>

            {connectionStatus !== 'idle' && (
              <div className="flex items-center space-x-2">
                {connectionStatus === 'connected' ? (
                  <CheckCircle className="h-5 w-5 text-green-600" />
                ) : connectionStatus === 'error' ? (
                  <XCircle className="h-5 w-5 text-red-600" />
                ) : (
                  <Loader2 className="h-5 w-5 animate-spin" />
                )}
                <span className={connectionStatus === 'connected' ? 'text-green-600' : connectionStatus === 'error' ? 'text-red-600' : 'text-gray-600'}>
                  {connectionMessage || 'Testing connection...'}
                </span>
              </div>
            )}

            {environmentInfo && (
              <div className="mt-2 p-4 bg-blue-50 border border-blue-200 rounded-md">
                <h3 className="text-sm font-medium text-blue-900 mb-2">Environment Information</h3>
                <div className="text-xs text-blue-800 space-y-1">
                  <div><strong>PHP Version:</strong> {environmentInfo.php_version}</div>
                  <div><strong>OS:</strong> {environmentInfo.os}</div>
                  <div><strong>PDO Extension:</strong> {environmentInfo.extensions.pdo ? '✅' : '❌'}</div>
                  <div><strong>PDO PostgreSQL:</strong> {environmentInfo.extensions.pdo_pgsql ? '✅' : '❌'}</div>
                  <div><strong>PostgreSQL Extension:</strong> {environmentInfo.extensions.pgsql ? '✅' : '❌'}</div>
                  <div><strong>Available PDO Drivers:</strong> {environmentInfo.pdo_drivers.join(', ') || 'None'}</div>
                </div>
              </div>
            )}
          </CardContent>
        </Card>

        {isConnected && (
          <Tabs defaultValue="query" className="space-y-4">
            <TabsList className="grid w-full grid-cols-3">
              <TabsTrigger value="query">Query Editor</TabsTrigger>
              <TabsTrigger value="tables">Tables</TabsTrigger>
              <TabsTrigger value="structure">Table Structure</TabsTrigger>
            </TabsList>

            {/* Query Editor Tab */}
            <TabsContent value="query" className="space-y-4">
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center space-x-2">
                    <Play className="h-5 w-5" />
                    SQL Query Editor
                  </CardTitle>
                  <CardDescription>
                    Execute SQL queries against your database
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  {isWriteQuery(query) && safeMode && (
                    <div className="p-3 rounded-md bg-amber-50 text-amber-800 border border-amber-200">
                      This query appears to modify data. Safe Mode is ON and will block this execution. Disable Safe Mode to proceed.
                    </div>
                  )}

                  <div className="space-y-2">
                    <Label htmlFor="sql-query">SQL Query</Label>
                    <textarea
                      ref={queryInputRef}
                      id="sql-query"
                      value={query}
                      onChange={(e) => setQuery(e.target.value)}
                      placeholder="Enter your SQL query here..."
                      className="w-full h-32 p-3 border border-gray-300 dark:border-zinc-700 rounded-md font-mono text-sm resize-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-zinc-900 text-gray-900 dark:text-white"
                    />
                  </div>
                  
                  <div className="flex flex-wrap gap-2 items-center">
                    <Button
                      onClick={executeQuery}
                      disabled={isExecuting || !query.trim()}
                      className="min-w-[120px]"
                    >
                      {isExecuting ? (
                        <Loader2 className="h-4 w-4 animate-spin" />
                      ) : (
                        <Play className="h-4 w-4" />
                      )}
                      {isExecuting ? 'Executing...' : 'Execute Query'}
                    </Button>
                    
                    <Button
                      variant="outline"
                      onClick={() => {
                        setQuery('SELECT * FROM information_schema.tables LIMIT 10');
                        setQueryResult(null);
                        setExecutionMessage('');
                      }}
                    >
                      Clear
                    </Button>

                    <div className="text-xs text-gray-600">
                      Safe Mode: <span className={safeMode ? 'text-green-700' : 'text-red-700'}>{safeMode ? 'ON (writes blocked)' : 'OFF (writes allowed)'}</span> · Max Rows: {maxRows}
                    </div>
                  </div>

                  {executionMessage && (
                    <div className={`p-3 rounded-md ${
                      executionMessage.includes('successfully') 
                        ? 'bg-green-50 text-green-800 border border-green-200' 
                        : 'bg-red-50 text-red-800 border border-red-200'
                    }`}>
                      {executionMessage}
                    </div>
                  )}

                  {queryResult && (
                    <div className="space-y-4">
                      <div className="flex items-center justify-between">
                        <h3 className="text-lg font-semibold">Results</h3>
                        {queryResult.type === 'select' && queryResult.results && (
                          <Badge variant="secondary">
                            {queryResult.rowCount} row(s)
                          </Badge>
                        )}
                      </div>

                      {queryResult.type === 'select' && queryResult.results && (
                        <div className="border border-gray-300 dark:border-zinc-700 rounded-md overflow-hidden bg-white dark:bg-zinc-900">
                          <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                              <thead className="bg-gray-100 border-b border-gray-300">
                                <tr>
                                  {queryResult.columns?.map((column, index) => (
                                    <th key={index} className="px-4 py-2 text-left font-medium text-gray-900">
                                      {column}
                                    </th>
                                  ))}
                                </tr>
                              </thead>
                              <tbody className="divide-y divide-gray-200">
                                {queryResult.results.map((row, rowIndex) => (
                                  <tr key={rowIndex} className="hover:bg-gray-50">
                                    {queryResult.columns?.map((column, colIndex) => (
                                      <td key={colIndex} className="px-4 py-2 text-gray-900">
                                        {formatValue(row[column])}
                                      </td>
                                    ))}
                                  </tr>
                                ))}
                              </tbody>
                            </table>
                          </div>
                        </div>
                      )}
                    </div>
                  )}
                </CardContent>
              </Card>
            </TabsContent>

            {/* Tables Tab */}
            <TabsContent value="tables" className="space-y-4">
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center space-x-2">
                    <Table className="h-5 w-5" />
                    Database Tables
                  </CardTitle>
                  <CardDescription>
                    Browse available tables in your database
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="flex justify-between items-center mb-4">
                    <Button
                      onClick={loadTables}
                      disabled={isLoadingTables}
                      variant="outline"
                      size="sm"
                    >
                      {isLoadingTables ? (
                        <Loader2 className="h-4 w-4 animate-spin" />
                      ) : (
                        <RefreshCw className="h-4 w-4" />
                      )}
                      Refresh Tables
                    </Button>
                  </div>

                  {isLoadingTables ? (
                    <div className="flex items-center justify-center py-8">
                      <Loader2 className="h-6 w-6 animate-spin" />
                      <span className="ml-2">Loading tables...</span>
                    </div>
                  ) : (
                    <div className="space-y-2">
                      {tables.map((table, index) => (
                        <div
                          key={index}
                          className="flex items-center justify-between p-3 border rounded-md hover:bg-gray-50 dark:hover:bg-zinc-800 cursor-pointer bg-white dark:bg-zinc-900 dark:border-zinc-800"
                          onClick={() => loadTableStructure(table.table_name)}
                        >
                          <div className="flex items-center space-x-2">
                            <Table className="h-4 w-4 text-blue-600" />
                            <span className="font-medium text-gray-900">{table.table_name}</span>
                            <Badge variant="outline" className="text-xs">
                              {table.table_type}
                            </Badge>
                            {table.table_schema !== 'public' && (
                              <Badge variant="secondary" className="text-xs">
                                {table.table_schema}
                              </Badge>
                            )}
                          </div>
                          <span className="text-gray-500 text-sm">Click to view structure</span>
                        </div>
                      ))}
                    </div>
                  )}
                </CardContent>
              </Card>
            </TabsContent>

            {/* Table Structure Tab */}
            <TabsContent value="structure" className="space-y-4">
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center space-x-2">
                    <Columns className="h-5 w-5" />
                    Table Structure
                  </CardTitle>
                  <CardDescription>
                    {selectedTable ? `Structure of table: ${selectedTable}` : 'Select a table to view its structure'}
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  {!selectedTable ? (
                    <div className="text-center py-8 text-gray-500">
                      Select a table from the Tables tab to view its structure
                    </div>
                  ) : isLoadingStructure ? (
                    <div className="flex items-center justify-center py-8">
                      <Loader2 className="h-6 w-6 animate-spin" />
                      <span className="ml-2">Loading table structure...</span>
                    </div>
                  ) : (
                    <div className="space-y-4">
                      <div className="flex items-center justify-between">
                        <h3 className="text-lg font-semibold text-gray-900">{selectedTable}</h3>
                        <Badge variant="secondary">
                          {tableStructure.length} column(s)
                        </Badge>
                      </div>

                      <div className="border border-gray-300 dark:border-zinc-700 rounded-md overflow-hidden bg-white dark:bg-zinc-900">
                        <table className="w-full text-sm">
                          <thead className="bg-gray-100 border-b border-gray-300">
                            <tr>
                              <th className="px-4 py-2 text-left font-medium text-gray-900">Column</th>
                              <th className="px-4 py-2 text-left font-medium text-gray-900">Type</th>
                              <th className="px-4 py-2 text-left font-medium text-gray-900">Nullable</th>
                              <th className="px-4 py-2 text-left font-medium text-gray-900">Default</th>
                              <th className="px-4 py-2 text-left font-medium text-gray-900">Constraints</th>
                            </tr>
                          </thead>
                          <tbody className="divide-y divide-gray-200">
                            {tableStructure.map((column, index) => (
                              <tr key={index} className="hover:bg-gray-50">
                                <td className="px-4 py-2 font-medium text-gray-900">
                                  {column.column_name}
                                </td>
                                <td className="px-4 py-2 text-gray-900">
                                  {column.data_type}
                                  {column.character_maximum_length && `(${column.character_maximum_length})`}
                                  {column.numeric_precision && column.numeric_scale && 
                                    `(${column.numeric_precision},${column.numeric_scale})`}
                                </td>
                                <td className="px-4 py-2">
                                  <Badge variant={column.is_nullable === 'YES' ? 'outline' : 'secondary'}>
                                    {column.is_nullable === 'YES' ? 'NULL' : 'NOT NULL'}
                                  </Badge>
                                </td>
                                <td className="px-4 py-2 text-gray-900">
                                  {column.column_default || '-'}
                                </td>
                                <td className="px-4 py-2 text-gray-900">
                                  {/* Additional constraints could be added here */}
                                </td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      </div>
                    </div>
                  )}
                </CardContent>
              </Card>
            </TabsContent>
          </Tabs>
        )}
      </div>
    </>
  );
}
