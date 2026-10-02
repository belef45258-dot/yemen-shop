<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('slug')->unique();
            $table->string('name_ar');
            $table->string('name_en');
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->string('currency', 3)->default('YER');
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('low_stock_alert')->default(5);
            $table->decimal('weight', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['category_id', 'is_active']);
            $table->index(['is_featured', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
