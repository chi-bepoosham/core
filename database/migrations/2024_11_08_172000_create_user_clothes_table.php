<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_clothes', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->integer('match_percentage')->nullable()->comment('درصد مطابقت');
            $table->tinyInteger('clothes_type')->nullable()->comment('نوع لباس :  1-بالا پوش  2-پایین پوش 3-تمام پوش');
            $table->tinyInteger('process_status')->default(1)->comment('وضعیت پردازش:  1-در حال پردازش  2-پردازش شده');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_clothes');
    }
};
