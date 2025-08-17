<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\PGDBRole;
use App\Models\PGDBProduct;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();

            //TODO: could have a billing account table for each organization
            //TODO: DON'T KEEP STRIPE DETAILS NULLABLE
            // $table->string('stripe_id')->unique() -> nullable(); //TODO: stripe id
            // $table->string('stripe_email')-> nullable(); //TODO: stripe email
            //spread stripe object details
        });

        // Organization Members (Pivot)
        Schema::create('organization_members', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignIdFor(User::class)
                -> constrained()
                -> onDelete('cascade'); //if user is deleted, delete the organization member

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->onDelete('cascade'); //if organization is deleted, delete the organization member

            $table->enum('role', ['owner', 'member']) //TODO: admin, manager, custom
                ->default('member');
        });



        //tied to a user for now
        //for now a user has 1 role and all their db is tied to that role
            //should have multiple roles for users and store relation between roles and databases and users
        Schema::create('pgdb_products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('restrict'); 

            $table->string(column: 'instance_id'); //name of db in pgdb cluster
            //$table->string('cluster_id')->nullable(); //TODO: for futher use when we have multiple clusters
            $table->string('name')->nullable();


            $table->enum('status',['active', 'terminated'])
                ->default('active');

            // $table->foreignId('pgdb_role_id') //tied to a user TODO: tie to org
            //     ->constrained('pgdb_roles')
            //     ->onDelete('restrict'); //need to delete the pgdb product before deleting the user

            // $table->json('details') //should be dynamic (query the actual database)
            //     ->nullable();

        });

        
        // postgres login roles
        Schema::create('pgdb_roles', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('pgdb_product_id') 
                ->constrained('pgdb_products')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreignId('user_id') 
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('restrict')
                ->nullable();

            $table->string('username');
            $table->string('password');


            //$table->string('cluster_id')->nullable(); //for which cluster?
        });

        Schema::create('ec2_products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->onDelete('restrict'); //if org is deleted, ec2 instances MUST be handled/deleted first

            $table->string('instance_id');
            $table->string('name')->nullable();


            $table->enum('status',['active', 'paused', 'terminated']);

            $table->json('details')
                ->nullable();

            $table->timestamps();
        });


        // Invoices
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->string('type')
                ->nullable()
                ->default("usage"); // subscription, usage, etc.
            $table->decimal('amount', 10, 2);
            $table->date('due');
            $table->enum('status', ['paid', 'unpaid', 'overdue'])
                ->default('unpaid');

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->onDelete('restrict'); //for org to be deleted, invoices must be handled first

            $table->foreignId('ec2_product_id')
                ->nullable()
                ->constrained('ec2_products')
                ->onDelete('restrict'); //for ec2 to be deleted, invoices must be handled first

            $table->timestamps();
        });

        // Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')
                ->nullable()
                ->constrained('invoices')
                ->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['confirmed', 'refunded', 'cancelled'])
                ->default('confirmed');

            $table->timestamps();
            //spread strip payment info
        });

        // product creation requests (for logging and optimistic inserts)
        Schema::create('product_requests', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['ec2', 'pgdb']);
            $table->enum('status', ['pending', 'accepted', 'declined', 'failed'])
                -> default('pending');

            $table->foreignId('organization_id')
                ->nullable()
                ->constrained('organizations')
                ->onDelete('restrict');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('product_requests');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('pgdb_products');
        Schema::dropIfExists('pgdb_roles');

        // Schema::dropIfExists('billing_accounts');
        Schema::dropIfExists('organization_user');
        Schema::dropIfExists('organizations');
    }
};
