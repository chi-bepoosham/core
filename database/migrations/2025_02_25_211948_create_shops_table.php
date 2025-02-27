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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('uuid')->index();
            $table->unsignedBigInteger('main_id')->nullable();
            $table->foreignId('province_id')->constrained('provinces')->onDelete('no action')->onUpdate('no action');
            $table->foreignId('city_id')->constrained('cities')->onDelete('no action');
            $table->string('address');
            $table->string('location_point')->nullable();
            $table->string('manager_name');
            $table->string('manager_national_code', 10);
            $table->string('mobile', 11)->unique();
            $table->string('password');
            $table->string('brand_name')->nullable();
            $table->string('description')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->string('phone', 11)->nullable();
            $table->string('web_site', 100)->nullable();
            $table->string('email', 100)->unique()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
