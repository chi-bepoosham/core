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
        Schema::table('user_clothes', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('user_sets', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('user_clothes_sets', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_clothes', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('user_sets', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('user_clothes_sets', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
