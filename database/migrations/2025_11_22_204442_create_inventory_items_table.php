<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_inventory_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryItemsTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_id');
            $table->unsignedBigInteger('product_id');

            $table->decimal('expected_qty', 10, 3)->default(0); // ожидаемый остаток
            $table->decimal('actual_qty', 10, 3)->default(0);   // фактический
            $table->decimal('diff_qty', 10, 3)->default(0);      // отклонение

            $table->decimal('unit_price', 10, 2)->nullable();    // средняя цена
            $table->decimal('diff_value', 10, 2)->default(0);    // сумма отклонения

            $table->timestamps();

            $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');

            $table->unique(['inventory_id','product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_items');
    }
}
