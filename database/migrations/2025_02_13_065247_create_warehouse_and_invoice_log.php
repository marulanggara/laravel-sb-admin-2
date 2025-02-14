<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS log_histories;');

        Schema::create('log_histories.warehouse_log_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->string('action');
            $table->string('old_data')->nullable();
            $table->string('new_data')->nullable();
            $table->timestamps();
        });

        Schema::create('log_histories.invoice_log_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->string('action');
            $table->string('old_data')->nullable();
            $table->string('new_data')->nullable();
            $table->timestamps();
        });

        Schema::create('log_histories.invoice_item_log_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('invoice_item_id')->nullable()->constrained('invoice_items')->onDelete('set null');
            $table->string('action');
            $table->string('old_data')->nullable();
            $table->string('new_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_histories.warehouse_loh_histories');
        Schema::dropIfExists('log_histories.invoice_log_histories');
        Schema::dropIfExists('log_histories.invoice_item_log_histories');
    }
};
