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
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('full_name')->comment('نام کامل گیرنده');
            $table->string('phone',11)->comment('شماره تلفن');
            $table->foreignId('province_id')->constrained('provinces')->onDelete('no action')->onUpdate('no action');
            $table->foreignId('city_id')->constrained('cities')->onDelete('no action');
            $table->string('address')->comment('آدرس');
            $table->string('postal_code',10)->nullable()->comment('کد پستی');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
