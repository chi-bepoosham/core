<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clothes_body_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('body_type_id')->nullable();
            $table->string('image');
        });
        DB::unprepared(file_get_contents(database_path('body_type_images.sql')));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clothes_body_types');
    }
};
