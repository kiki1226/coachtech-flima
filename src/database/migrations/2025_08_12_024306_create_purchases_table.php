<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();     // 購入者
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();  // 対象商品
            $table->foreignId('shipping_address_id')->constrained('addresses')->cascadeOnDelete();
            $table->string('payment_method'); // 'card' 等
            $table->string('status')->default('paid'); // 必要に応じてenum化
            $table->timestamp('purchased_at')->nullable();
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
        Schema::dropIfExists('purchases');
    }
}
