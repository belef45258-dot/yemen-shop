<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('method', ['jaib','jawali','cash','one_cash','kuraimi','whatsapp','stripe','cod']);
            $table->enum('status', ['pending','processing','completed','failed','refunded'])->default('pending');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('YER');
            $table->string('transaction_id')->nullable()->index();
            $table->string('reference_number')->nullable();
            $table->string('payer_phone', 20)->nullable();
            $table->string('receiver_wallet', 50)->nullable();
            $table->string('screenshot')->nullable();
            $table->json('provider_response')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'status']);
            $table->index(['method', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
