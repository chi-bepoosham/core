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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->unsignedBigInteger('main_id')->nullable();
            $table->foreignId('city_id')->constrained('cities');
            $table->string('address');
            $table->string('location_lat')->nullable();
            $table->string('location_lng')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('manager_name');
            $table->string('manager_national_code',10);
            $table->string('password');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_authenticated')->default(false);
            $table->string('phone',11)->nullable();
            $table->string('mobile',11)->nullable();
            $table->string('web_site',100)->nullable();
            $table->string('email',100)->unique()->nullable();
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
