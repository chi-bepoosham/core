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
        Schema::create('visions', function (Blueprint $table) {
            $table->id();
            $table->integer('current_count')->nullable();
            $table->integer('target_count');
            $table->boolean('is_active')->default(false);
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visions');
    }
};
