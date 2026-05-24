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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();   // pcs, kg, l, m
            $table->string('name');             // Штуки, Килограммы, Литры
            $table->timestamps();
        });

        // Добавим популярные единицы
        DB::table('units')->insert([
            ['code' => 'pcs', 'name' => 'Штуки'],
            ['code' => 'kg',  'name' => 'Килограммы'],
            ['code' => 'l',   'name' => 'Литры'],
            ['code' => 'm',   'name' => 'Метры'],
        ]);
    }




    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('units');
    }
};
