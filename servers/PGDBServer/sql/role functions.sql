-- create a new user

CREATE OR REPLACE FUNCTION provision_user(p_username text, p_password text)
RETURNS void AS $$
DECLARE
    role_name text := p_username;
    db_name   text := p_username || '_db';
    datname   text;
BEGIN
    -- 1. Create role with login
    EXECUTE format('CREATE ROLE %I WITH LOGIN PASSWORD %L', role_name, p_password);

    -- 2. Create database owned by the new role
    EXECUTE format('CREATE DATABASE %I OWNER %I', db_name, role_name);

    -- 3. Restrict connection to other databases
    FOR datname IN
        SELECT datname
        FROM pg_database
        WHERE datname NOT IN ('template0', 'template1', db_name)
    LOOP
        EXECUTE format('REVOKE CONNECT ON DATABASE %I FROM %I', datname, role_name);
    END LOOP;

    -- 4. Connect to the new database and set schema ownership and default privileges
    EXECUTE format($f$
        ALTER SCHEMA public OWNER TO %I;
        ALTER DEFAULT PRIVILEGES FOR ROLE %I IN SCHEMA public
            GRANT ALL PRIVILEGES ON TABLES TO %I;
        ALTER DEFAULT PRIVILEGES FOR ROLE %I IN SCHEMA public
            GRANT ALL PRIVILEGES ON SEQUENCES TO %I;
        ALTER DEFAULT PRIVILEGES FOR ROLE %I IN SCHEMA public
            GRANT ALL PRIVILEGES ON FUNCTIONS TO %I;
    $f$, role_name, role_name, role_name, role_name, role_name, role_name, role_name);

END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION create_role(p_username text, p_password text)
RETURNS void AS $$
BEGIN
    -- Create a login role with the given password
    EXECUTE format('CREATE ROLE %I WITH LOGIN PASSWORD %L', p_username, p_password);
END;
$$ LANGUAGE plpgsql;


-- change user password

CREATE OR REPLACE FUNCTION change_user_password(p_username text, p_new_password text)
RETURNS void AS $$
BEGIN
    EXECUTE format('ALTER ROLE %I WITH PASSWORD %L', p_username, p_new_password);
END;
$$ LANGUAGE plpgsql;