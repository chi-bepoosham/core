<?php

use App\Http\Repositories\UserRepository;
use App\Services\AuthenticationsService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function createSystemUser(): void
    {
        (new AuthenticationsService(new UserRepository()))->createUser(
            [
                'first_name' => 'System',
                'last_name' => 'User',
                'mobile' => '09000000000',
            ]
        );
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('mobile',11)->unique()->nullable();
            $table->date('birthday')->nullable();
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('avatar')->nullable();
            $table->tinyInteger('gender')->default(1)->comment(" جنسیت 1: مرد  2: زن  3: دیگر");
            $table->tinyInteger('status')->default(1)->comment("وضعیت فعال بودن 0: غیر فعال  1: فعال");
            $table->softDeletes();
            $table->timestamps();
        });

        $this->createSystemUser();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
