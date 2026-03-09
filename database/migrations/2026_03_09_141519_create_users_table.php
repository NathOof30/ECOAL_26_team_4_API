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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->varchar('name');
            $table->varchar('email')->unique();
            $table->varchar('password');
            $table->varchar('avatar_url')->nullable();
            $table->varchar('nationality')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps('created_at')->useCurrent();
            $table->varchar('user_type')->default('user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
