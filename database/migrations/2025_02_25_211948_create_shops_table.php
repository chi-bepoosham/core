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
            $table->string('name')->index()->comment('نام فروشگاه');
            $table->string('uuid')->index()->comment('');
            $table->unsignedBigInteger('main_id')->nullable()->comment('آیدی شعبه مرکزی');
            $table->foreignId('province_id')->constrained('provinces')->onDelete('no action')->onUpdate('no action');
            $table->foreignId('city_id')->constrained('cities')->onDelete('no action');
            $table->string('address')->comment('آدرس');
            $table->string('location_point')->nullable()->comment('نقطه جغرافیایی');
            $table->string('manager_name')->comment('نام مدیریت');
            $table->string('manager_national_code', 10)->comment('کد ملی مدیریت');
            $table->string('mobile', 11)->unique()->comment('موبایل');
            $table->string('password')->comment('پسورد');
            $table->string('brand_name')->nullable()->comment('نام برند');
            $table->string('description')->nullable()->comment('توضیحات');
            $table->string('logo')->nullable()->comment('لوگو');
            $table->boolean('is_active')->default(false)->comment('وضعیت فعال بودن');
            $table->boolean('is_verified')->default(false)->comment('وضعیت احراز شده');
            $table->string('phone', 11)->nullable()->comment('تلفن ثابت');
            $table->string('web_site', 100)->nullable()->comment('وب سایت');
            $table->string('email', 100)->unique()->nullable()->comment('ایمیل');
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
