<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('عنوان');
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('no action');
            $table->unsignedBigInteger('main_id')->nullable()->comment('آیدی محصول اصلی');
            $table->string('color')->nullable()->comment('رنگ');
            $table->enum('gender', ['male', 'female', 'unisex'])->comment('جنسیت');
            $table->json('sizes')->nullable()->comment('لیست سایز ها');
            $table->string('description')->nullable()->comment('توضیحات');
            $table->integer('price')->default(0)->comment('قیمت');
            $table->boolean('is_available')->default(true)->comment('وضعیت موجود بودن');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
