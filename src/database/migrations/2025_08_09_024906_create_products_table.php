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
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('brand')->nullable(); // ブランド名
            $table->unsignedInteger('price');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable(); // メイン画像
            $table->text('features')->nullable();     
            $table->string('condition', 20)->nullable();  // 商品の状態
            $table->boolean('is_sold')->default(false);
            $table->foreignId('buyer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('user_id');
            $table->index(['brand']);
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
