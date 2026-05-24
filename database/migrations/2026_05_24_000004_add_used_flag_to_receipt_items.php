<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->boolean('is_used')->default(false)->after('unit_price');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->boolean('is_used')->default(false)->after('unit_price');
        });
    }

    public function down()
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('is_used');
        });

        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->dropColumn('is_used');
        });
    }
};
