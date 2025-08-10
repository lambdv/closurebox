<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

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
            $table->string('stripe_id')->unique() -> nullable(); //TODO: stripe id
            $table->string('stripe_email')-> nullable(); //TODO: stripe email
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

        // Schema::create('pgdb_products', function (Blueprint $table) {
        //     $table->id();

        //     $table->foreignId('organization_id')
        //         ->constrained()
        //         ->onDelete('cascade');

        //     $table->string('instance_id');

        //     $table->enum('status',['active', 'stopped', 'terminated'])
        //         ->default('active');

        //     $table->json('details')
        //         ->nullable();

        //     $table->timestamps();
        // });

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

        /**
         * Status Check Result	Meaning
            2/2 checks passed	Both system and instance checks succeeded. The instance is healthy.
            1/2 checks passed (System check failed)	Instance is running, but AWS infrastructure has an issue (e.g., hardware failure, host networking issue).
            1/2 checks passed (Instance check failed)	Instance is running, but the OS/network configuration is preventing reachability (e.g., kernel panic, network misconfiguration, firewall rules).
            0/2 checks passed	Both system and instance checks failed — AWS infrastructure problem and instance-level problem.
            Initializing	Instance checks haven’t completed yet (shortly after launch, reboot, or status reset).
            Insufficient data	AWS doesn’t yet have enough information to report a check status (often after starting/stopping or in rare API delays).
         */

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
            $table->enum('status', ['pending', 'accepted', 'declined'])
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
        Schema::dropIfExists('billing_accounts');
        Schema::dropIfExists('organization_user');
        Schema::dropIfExists('organizations');
    }
};
