

CREATE ROLE 
    customer1  
    LOGIN 
    PASSWORD 'password123';

CREATE DATABASE customer1db
    OWNER admin;

REVOKE CONNECT ON DATABASE postgres FROM PUBLIC;
REVOKE CONNECT ON DATABASE template1 FROM PUBLIC;
REVOKE CONNECT ON DATABASE customer1db FROM PUBLIC;


\c customer1db
REVOKE CREATE ON SCHEMA public FROM PUBLIC;
GRANT CREATE, USAGE ON SCHEMA public TO customer1;


ALTER ROLE customer1 SET statement_timeout = '30s';
ALTER ROLE customer1 SET work_mem = '4MB';
ALTER ROLE customer1 CONNECTION LIMIT 5;

//testing

psql -U customer1 -d customer1db -h localhost
