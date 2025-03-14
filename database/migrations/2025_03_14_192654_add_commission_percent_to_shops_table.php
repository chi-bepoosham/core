<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $commissionPercent = (float)Setting::query()->where('key', 'commission_percent')->first()?->value ?? 0;

        Schema::table('shops', function (Blueprint $table) use ($commissionPercent) {
            $table->decimal('commission_percent')->default($commissionPercent)->comment('درصد پورسانت');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('commission_percent');
        });
    }
};
