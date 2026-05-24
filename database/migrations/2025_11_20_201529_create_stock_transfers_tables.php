<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Шапка документа
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('from_warehouse_id')->nullable()->constrained('warehouses');
            $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses');

            $table->foreignId('from_store_id')->nullable()->constrained('stores');
            $table->foreignId('to_store_id')->nullable()->constrained('stores');

            $table->foreignId('user_id')->constrained('users');

            $table->string('document_number')->unique();
            $table->date('document_date');

            $table->string('comment')->nullable();

            $table->timestamps();
        });

        // Строки документа
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');

            $table->decimal('quantity', 10, 3);
            $table->decimal('unit_price', 10, 2)->nullable();

            $table->date('expiry_date')->nullable();
            $table->string('batch', 50)->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
    }
};
