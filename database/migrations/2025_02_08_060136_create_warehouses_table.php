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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('set null'); // relasi dengan product
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('set null'); // relasi dengan supplier
            $table->foreignId('unit_id')->constrained('units')->onDelete('set null'); // relasi dengan unit
            $table->integer('quantity'); // jumlah stok product
            $table->bigInteger('price'); // harga per unit
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
