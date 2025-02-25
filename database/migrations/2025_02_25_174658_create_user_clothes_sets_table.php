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
        Schema::create('user_clothes_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_set_id')->constrained('user_sets')->onDelete('cascade');
            $table->foreignId('user_clothe_id')->constrained('user_clothes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_clothes_sets');
    }
};
