<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('barcode', 13)->nullable()->unique();
            $table->string('name');
            $table->enum('unit', ['pcs', 'l', 'm', 'kg', 'other'])->default('pcs');
            $table->string('photo_path')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories');
            $table->decimal('base_price', 10, 2)->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
