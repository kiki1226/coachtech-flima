<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('price');      // 価格は負数NG
            $table->text('description')->nullable();
            $table->string('image_path')->nullable(); // メイン画像
            $table->text('features')->nullable();          // 任意の特徴
            $table->string('condition', 20)->nullable();   // 'new','used' など（後でenum化も可）

            // 在庫や販売状態を使うなら
            $table->unsignedSmallInteger('stock')->default(1);
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            // よく使うキーはインデックス
            $table->index(['user_id', 'price']);
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
}
