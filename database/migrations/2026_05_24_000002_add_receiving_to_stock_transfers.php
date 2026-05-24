<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->integer('received_quantity')->default(0)->after('quantity');
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->timestamp('received_at')->nullable()->after('document_date');
            $table->foreignId('received_user_id')->nullable()->after('user_id')->constrained('users');
        });

        DB::table('stock_transfers')->where('status', 'draft')->update(['status' => 'shipped']);
    }

    public function down()
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('received_user_id');
            $table->dropColumn('received_at');
        });

        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->dropColumn('received_quantity');
        });
    }
};
