# PostgreSQL Admin Interface

This document describes how to use the PostgreSQL Admin interface built with Inertia React.

## Overview

The PostgreSQL Admin interface provides a web-based tool for:
- Connecting to PostgreSQL databases
- Executing SQL queries
- Browsing database tables
- Viewing table structures

## Features

### 1. Database Connection
- Connect using standard PostgreSQL connection strings
- Test connection before proceeding
- Secure connection handling

### 2. SQL Query Editor
- Execute SELECT, INSERT, UPDATE, DELETE queries
- View results in formatted tables
- Handle both data retrieval and modification queries
- Real-time query execution feedback

### 3. Table Browser
- List all available tables in the database
- Filter by schema and table type
- Click to view table structure

### 4. Table Structure Viewer
- Display column information
- Show data types, constraints, and defaults
- Helpful for understanding database schema

## Usage

### Accessing the Interface
1. Navigate to `/postgres-admin` in your application
2. Ensure you're authenticated (login required)
3. The interface will be available in the sidebar navigation

### Connecting to a Database
1. Enter your PostgreSQL connection string in the format:
   ```
   postgresql://username:password@host:port/database
   ```
2. Click "Connect" to test the connection
3. Once connected, all features become available

### Example Connection String
```
postgresql://user_1_t8IRM6hPPEyZ:Tt3yKoobvcZq@localhost:5432/user_1_iwzKZvLVbF5v
```

### Executing Queries
1. Go to the "Query Editor" tab
2. Enter your SQL query in the text area
3. Click "Execute Query" to run it
4. View results in the formatted table below

### Browsing Tables
1. Go to the "Tables" tab
2. Click "Refresh Tables" to load the current table list
3. Click on any table to view its structure

### Viewing Table Structure
1. Select a table from the Tables tab
2. Go to the "Table Structure" tab
3. View detailed column information including:
   - Column names
   - Data types
   - Nullability
   - Default values
   - Constraints

## Security Features

- Authentication required for access
- Connection strings are not stored permanently
- All database operations are logged
- Input validation and sanitization

## Technical Details

### Backend
- Laravel controller with PDO connections
- RESTful API endpoints for database operations
- Proper error handling and validation

### Frontend
- React component with TypeScript
- Inertia.js for seamless Laravel-React integration
- Tailwind CSS for styling
- Responsive design for mobile and desktop

### API Endpoints
- `POST /postgres-admin/connect` - Test database connection
- `POST /postgres-admin/execute-query` - Execute SQL queries
- `POST /postgres-admin/tables` - Get list of tables
- `POST /postgres-admin/table-structure` - Get table structure

## Troubleshooting

### Connection Issues
- Verify your connection string format
- Ensure the database server is running
- Check network connectivity
- Verify username/password credentials

### Query Errors
- Check SQL syntax
- Ensure proper permissions for the database user
- Verify table and column names exist

### Performance Issues
- Limit large result sets in SELECT queries
- Use appropriate WHERE clauses
- Consider indexing for frequently queried columns

## Best Practices

1. **Security**: Never share connection strings with sensitive credentials
2. **Performance**: Use LIMIT clauses for large datasets
3. **Backup**: Always backup data before running destructive queries
4. **Testing**: Test queries on development databases first
5. **Monitoring**: Monitor query performance and execution times

## Dependencies

- Laravel (Backend framework)
- Inertia.js (Frontend integration)
- React + TypeScript (Frontend framework)
- Tailwind CSS (Styling)
- PDO (Database connections)
- Axios (HTTP client)

## Future Enhancements

- Query history and favorites
- Export results to CSV/JSON
- Visual query builder
- Database backup/restore functionality
- User permission management
- Query performance analysis
