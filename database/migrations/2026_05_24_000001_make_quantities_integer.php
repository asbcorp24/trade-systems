<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement('UPDATE stock_movements SET quantity = ROUND(quantity)');
        DB::statement('UPDATE goods_receipt_items SET quantity = ROUND(quantity)');
        DB::statement('UPDATE stock_transfer_items SET quantity = ROUND(quantity)');
        DB::statement('UPDATE sale_items SET quantity = ROUND(quantity)');
        DB::statement('UPDATE inventory_items SET expected_qty = ROUND(expected_qty), actual_qty = ROUND(actual_qty), diff_qty = ROUND(diff_qty)');
        DB::statement('UPDATE products SET min_stock = ROUND(min_stock), max_stock = ROUND(max_stock)');

        DB::statement('ALTER TABLE stock_movements MODIFY quantity INT NOT NULL');
        DB::statement('ALTER TABLE goods_receipt_items MODIFY quantity INT NOT NULL');
        DB::statement('ALTER TABLE stock_transfer_items MODIFY quantity INT NOT NULL');
        DB::statement('ALTER TABLE sale_items MODIFY quantity INT NOT NULL');
        DB::statement('ALTER TABLE inventory_items MODIFY expected_qty INT NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE inventory_items MODIFY actual_qty INT NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE inventory_items MODIFY diff_qty INT NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE products MODIFY min_stock INT NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE products MODIFY max_stock INT NOT NULL DEFAULT 0');
    }

    public function down()
    {
        DB::statement('ALTER TABLE stock_movements MODIFY quantity DECIMAL(10,3) NOT NULL');
        DB::statement('ALTER TABLE goods_receipt_items MODIFY quantity DECIMAL(10,3) NOT NULL');
        DB::statement('ALTER TABLE stock_transfer_items MODIFY quantity DECIMAL(10,3) NOT NULL');
        DB::statement('ALTER TABLE sale_items MODIFY quantity DECIMAL(10,3) NOT NULL');
        DB::statement('ALTER TABLE inventory_items MODIFY expected_qty DECIMAL(10,3) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE inventory_items MODIFY actual_qty DECIMAL(10,3) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE inventory_items MODIFY diff_qty DECIMAL(10,3) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE products MODIFY min_stock DECIMAL(10,3) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE products MODIFY max_stock DECIMAL(10,3) NOT NULL DEFAULT 0');
    }
};
