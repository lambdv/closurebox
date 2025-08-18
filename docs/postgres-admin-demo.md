# PostgreSQL Admin Interface Demo

This guide demonstrates how to use the PostgreSQL Admin interface with practical examples.

## Getting Started

1. **Navigate to the PostgreSQL Admin page**
   - Go to `/postgres-admin` in your application
   - Or click "Open PostgreSQL Admin" from any database details page

2. **Connect to your database**
   - Use the connection string: `postgresql://user_1_t8IRM6hPPEyZ:Tt3yKoobvcZq@localhost:5432/user_1_iwzKZvLVbF5v`
   - Click "Connect" to test the connection

## Sample Queries to Try

### 1. Basic SELECT Queries

**View all tables in the database:**
```sql
SELECT table_name, table_type, table_schema 
FROM information_schema.tables 
WHERE table_schema NOT IN ('information_schema', 'pg_catalog')
ORDER BY table_schema, table_name;
```

**View table columns:**
```sql
SELECT column_name, data_type, is_nullable, column_default
FROM information_schema.columns 
WHERE table_name = 'your_table_name'
ORDER BY ordinal_position;
```

**Count rows in a table:**
```sql
SELECT COUNT(*) FROM your_table_name;
```

### 2. Data Exploration Queries

**View first 10 rows of a table:**
```sql
SELECT * FROM your_table_name LIMIT 10;
```

**View specific columns:**
```sql
SELECT id, name, created_at 
FROM your_table_name 
ORDER BY created_at DESC 
LIMIT 20;
```

**Search for specific data:**
```sql
SELECT * FROM your_table_name 
WHERE name ILIKE '%search_term%';
```

### 3. Schema Information Queries

**List all schemas:**
```sql
SELECT schema_name 
FROM information_schema.schemata 
ORDER BY schema_name;
```

**View table constraints:**
```sql
SELECT 
    tc.constraint_name,
    tc.constraint_type,
    kcu.column_name,
    ccu.table_name AS foreign_table_name,
    ccu.column_name AS foreign_column_name
FROM information_schema.table_constraints tc
JOIN information_schema.key_column_usage kcu 
    ON tc.constraint_name = kcu.constraint_name
LEFT JOIN information_schema.constraint_column_usage ccu 
    ON ccu.constraint_name = tc.constraint_name
WHERE tc.table_name = 'your_table_name';
```

**View indexes:**
```sql
SELECT 
    indexname,
    indexdef
FROM pg_indexes 
WHERE tablename = 'your_table_name';
```

### 4. Performance Queries

**View table sizes:**
```sql
SELECT 
    schemaname,
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) as size
FROM pg_tables 
WHERE schemaname NOT IN ('information_schema', 'pg_catalog')
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;
```

**View slow queries (if pg_stat_statements is enabled):**
```sql
SELECT 
    query,
    calls,
    total_time,
    mean_time,
    rows
FROM pg_stat_statements 
ORDER BY total_time DESC 
LIMIT 10;
```

### 5. User and Permission Queries

**List database users:**
```sql
SELECT usename, usesuper, usecreatedb, usebypassrls
FROM pg_user
ORDER BY usename;
```

**View user permissions:**
```sql
SELECT 
    grantee,
    table_name,
    privilege_type
FROM information_schema.role_table_grants 
WHERE grantee = 'your_username';
```

## Using the Table Browser

1. **Go to the "Tables" tab**
2. **Click "Refresh Tables"** to load the current table list
3. **Click on any table** to view its structure
4. **Switch to "Table Structure" tab** to see column details

## Best Practices for the Admin Interface

### 1. Always Use LIMIT
```sql
-- Good: Limits results to prevent overwhelming the interface
SELECT * FROM large_table LIMIT 100;

-- Avoid: Could return thousands of rows
SELECT * FROM large_table;
```

### 2. Test Queries First
```sql
-- Test with a small dataset first
SELECT COUNT(*) FROM your_table WHERE condition;

-- Then run the full query
SELECT * FROM your_table WHERE condition;
```

### 3. Use Descriptive Column Names
```sql
-- Good: Clear column aliases
SELECT 
    id as user_id,
    name as user_name,
    created_at as registration_date
FROM users;

-- Avoid: Unclear column names
SELECT id, name, created_at FROM users;
```

### 4. Handle NULL Values
```sql
-- Good: Explicit NULL handling
SELECT 
    COALESCE(name, 'Unknown') as user_name,
    COALESCE(email, 'No email') as user_email
FROM users;

-- Avoid: NULL values might not display clearly
SELECT name, email FROM users;
```

## Troubleshooting Common Issues

### Connection Problems
- **Error**: "Connection refused"
  - **Solution**: Check if PostgreSQL server is running
  - **Command**: `sudo systemctl status postgresql`

- **Error**: "Authentication failed"
  - **Solution**: Verify username/password in connection string
  - **Check**: Database user permissions

### Query Issues
- **Error**: "Table does not exist"
  - **Solution**: Check table name spelling and schema
  - **Query**: `SELECT table_name FROM information_schema.tables;`

- **Error**: "Permission denied"
  - **Solution**: Check user permissions for the table
  - **Query**: `SELECT * FROM information_schema.role_table_grants;`

### Performance Issues
- **Slow queries**: Add LIMIT clauses
- **Large results**: Use WHERE clauses to filter data
- **Memory issues**: Break large queries into smaller parts

## Security Considerations

1. **Never share connection strings** with sensitive credentials
2. **Use read-only users** for exploration when possible
3. **Limit database access** to necessary users only
4. **Monitor query logs** for suspicious activity
5. **Use prepared statements** for user input (when implementing custom queries)

## Advanced Features

### Custom Query Templates
Create reusable query templates for common operations:

```sql
-- Template: Find tables by pattern
SELECT table_name 
FROM information_schema.tables 
WHERE table_name ILIKE '%pattern%'
AND table_schema = 'public';

-- Template: Check table row counts
SELECT 
    schemaname,
    tablename,
    n_tup_ins as inserts,
    n_tup_upd as updates,
    n_tup_del as deletes
FROM pg_stat_user_tables 
WHERE schemaname = 'public'
ORDER BY n_tup_ins DESC;
```

### Export Results
While the interface doesn't currently support direct export, you can:
1. Copy results to clipboard
2. Use the browser's developer tools to extract data
3. Implement custom export functionality in the future

## Next Steps

1. **Explore your database schema** using the table browser
2. **Run sample queries** to understand your data
3. **Create custom queries** for your specific needs
4. **Monitor performance** using the provided queries
5. **Document common queries** for your team

The PostgreSQL Admin interface provides a powerful way to interact with your database directly from the web interface, making database administration more accessible and efficient.
