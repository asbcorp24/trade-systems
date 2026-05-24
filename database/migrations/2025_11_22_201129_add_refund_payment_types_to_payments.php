<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Меняем ENUM напрямую SQL-командой
        DB::statement("
            ALTER TABLE payments
            MODIFY COLUMN payment_type ENUM('cash','card','refund_cash','refund_card')
            NOT NULL DEFAULT 'cash'
        ");
    }

    public function down()
    {
        DB::statement("
            ALTER TABLE payments
            MODIFY COLUMN payment_type ENUM('cash','card')
            NOT NULL DEFAULT 'cash'
        ");
    }
};
