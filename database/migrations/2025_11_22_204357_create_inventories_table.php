<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_inventories_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoriesTable extends Migration
{
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->unique(); // типа ИНВ-000001
            $table->dateTime('document_date');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->default('draft'); // draft, applied, cancelled
            $table->string('comment')->nullable();
            $table->timestamps();

            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventories');
    }
}
