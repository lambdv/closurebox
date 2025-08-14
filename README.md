# ClosureBox
a cloud service platform for serverless databases.

## Install
```bash
composer install
npm run build # or dev
php artisan migrate:fresh --seed # for testing envorinemnt
php artisan serve
```

## Project Layout
```bash
/app/Http/Controllers # http controllers for API endpoints related to views and high level buisness logic
/app/Jobs # async background tasks
/app/Models  # eleqent models for database
/app/Services # classes with methods for lower level domain logic
/database/migrations # database schemas
/resources/views # frontend views in blade templates
/resources/inertia # frontend views in react
/routes/web.php # router for web endpoints
/servers/PGDBServer # docker image to run the postgres database server/cluster
/docs # for user manual and project documentation
```
