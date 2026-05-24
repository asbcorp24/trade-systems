<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // 1. Удаляем FK
            $table->dropForeign(['warehouse_id']);
        });

        // 2. Меняем столбец на nullable
        DB::statement('ALTER TABLE stock_movements MODIFY warehouse_id BIGINT UNSIGNED NULL;');

        // 3. Добавляем FK назад
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
        });
    }

    public function down()
    {
        // обратно в NOT NULL
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
        });

        DB::statement('ALTER TABLE stock_movements MODIFY warehouse_id BIGINT UNSIGNED NOT NULL;');

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
        });
    }
};
