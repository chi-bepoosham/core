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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('no action');
            $table->foreignId('user_address_id')->nullable()->constrained('user_address')->onDelete('no action');
            $table->enum('delivery_type', ['store', 'shipping'])->comment('نوع ارسال');
            $table->string('tracking_number')->unique()->comment('شماره پیگیری');
            $table->enum('status', ['inProgress', 'delivered', 'returned', 'canceled'])->default('inProgress')->comment('وضعیت سفارش');
            $table->enum('progress_status', ['pendingForPayment', 'waitingForConform', 'waitingForPacking', 'readyForDelivery', 'waitingForConfirmReturning', 'waitingForProcessReturning', 'delivered', 'returned', 'canceled', 'canceledSystemically'])->default('pendingForPayment')->comment('وضعیت پردازش');
            $table->integer('total_price')->comment('قیمت کل بدون تخفیف و مالیات');
            $table->integer('discount')->default(0)->comment('مقدار تخفیف');
            $table->integer('vat')->default(0)->comment('ارزش افزوده');
            $table->integer('shipping_fee')->default(0)->comment('هزینه حمل و نقل');
            $table->integer('final_price')->comment('قیمت کل نهایی');
            $table->text('description')->nullable()->comment('توضیحات');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
