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
        Schema::table('supplier_product', function (Blueprint $table) {
            // Drop foreign key yang lama pada supplier_id
            $table->dropForeign(['supplier_id']);

            // Tambahkan foreign key baru dengan onDelete('set null')
            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Jika rollback, kembalikan ke aturan semula (misalnya 'restrict')
        Schema::table('supplier_product', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers')
                ->onDelete('restrict');
        });
    }
};
