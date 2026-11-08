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
        Schema::create('photobooth_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('email');
            $table->string('phone', 24);
            $table->string('layout', 16);
            $table->unsignedInteger('amount');
            $table->string('payment_status', 24)->default('pending');
            $table->string('midtrans_order_id', 50)->unique();
            $table->string('midtrans_transaction_id')->nullable()->index();
            $table->string('midtrans_payment_type', 40)->nullable();
            $table->string('snap_token')->nullable();
            $table->text('snap_redirect_url')->nullable();
            $table->string('delivery_status', 24)->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['payment_status', 'delivery_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photobooth_orders');
    }
};
