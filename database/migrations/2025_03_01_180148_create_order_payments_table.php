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
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('no action');
            $table->enum('payment_method', ['zarinpal', 'cash_on_delivery'])->default('zarinpal')->comment('روش پرداخت');
            $table->enum('status', ['pending', 'failed', 'completed'])->default('pending')->comment('وضعیت پرداخت');
            $table->string('transaction_id')->comment('شناسه تراکنش');
            $table->string('reference_id')->nullable()->comment('کد مرجع تراکنش خريد');
            $table->integer('amount')->comment('مبلغ پرداخت شده');
            $table->json('payment_details')->nullable()->comment('اطلاعات تکمیلی پرداخت');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};
