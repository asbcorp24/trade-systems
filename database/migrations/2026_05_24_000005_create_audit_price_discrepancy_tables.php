<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('event');
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->text('description')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
            $table->index(['auditable_type', 'auditable_id']);
        });

        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('price_type');
            $table->decimal('old_price', 10, 2)->nullable();
            $table->decimal('new_price', 10, 2);
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedTinyInteger('discount_percent')->default(0);
            $table->timestamps();
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('transfer_discrepancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->integer('shipped_quantity');
            $table->integer('received_quantity');
            $table->integer('shortage_quantity')->default(0);
            $table->integer('surplus_quantity')->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transfer_discrepancies');
        Schema::dropIfExists('price_histories');
        Schema::dropIfExists('audit_logs');
    }
};
