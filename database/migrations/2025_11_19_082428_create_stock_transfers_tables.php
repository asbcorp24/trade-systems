<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
// stock_transfers
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_warehouse_id')->constrained('warehouses');
            $table->foreignId('to_warehouse_id')->constrained('warehouses');
            $table->foreignId('user_id')->constrained('users');
            $table->string('document_number')->unique();
            $table->date('document_date');
            $table->text('comment')->nullable();
            $table->timestamps();
        });

// stock_transfer_items
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->string('barcode', 13)->nullable();
            $table->decimal('quantity', 10, 3);
            $table->decimal('transfer_price', 10, 2);
            $table->date('expiry_date')->nullable();
            $table->string('batch', 50)->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_transfers_tables');
    }
};
