<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('password');
            $table->string('avatar')->nullable()->after('google_id');
            $table->string('phone', 20)->nullable()->after('avatar');
            $table->enum('role', ['customer', 'admin'])->default('customer')->after('phone');
            $table->string('locale', 5)->default('ar')->after('role');
            $table->boolean('is_active')->default(true)->after('locale');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'avatar', 'phone', 'role', 'locale', 'is_active']);
        });
    }
};
