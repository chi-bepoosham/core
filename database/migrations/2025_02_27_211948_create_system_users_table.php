<?php

use App\Http\Repositories\SystemUserRepository;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function createSuperAdminSystemUser(): void
    {
        (new SystemUserRepository())->firstOrCreate(
            [
                'full_name' => 'Super Admin',
                'username' => 'super-admin',
                'password' => \Illuminate\Support\Facades\Hash::make("1234"),
            ]
        );
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->comment('نام کامل');
            $table->string('username')->unique()->comment('نام کاربری');
            $table->string('password')->comment('پسورد');
            $table->boolean('status')->default(true)->comment('وضعیت فعال بودن');
            $table->string('avatar')->nullable()->comment('تصویر پروفایل');
            $table->timestamps();
        });

        $this->createSuperAdminSystemUser();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_users');
    }
};
