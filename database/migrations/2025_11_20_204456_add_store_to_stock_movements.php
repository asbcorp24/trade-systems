<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('store_id')
                ->nullable()
                ->after('warehouse_id')
                ->constrained('stores');

            $table->index('store_id');
        });
    }

    public function down()
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('store_id');
        });
    }
};
