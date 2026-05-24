<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');

            $table->enum('payment_type', ['cash', 'card']); // нал / безнал
            $table->decimal('amount', 12, 2);
            $table->dateTime('paid_at');

            $table->string('kkm_status')->nullable();    // ok / error / pending
            $table->string('kkm_ticket')->nullable();    // номер чека
            $table->text('kkm_raw_response')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
