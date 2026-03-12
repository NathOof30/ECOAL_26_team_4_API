<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the users table with custom fields for the ECOAL application.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key

            $table->string('name'); // User's display name
            $table->string('email')->unique(); // Unique email for login
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password'); // Hashed password
            $table->rememberToken();
            $table->text('avatar_url')->nullable(); // Optional hosted avatar URL
            $table->string('nationality')->nullable(); // Optional nationality info

            $table->boolean('is_active')->default(true); // Whether the account is active

            // Timestamps: created_at defaults to current time, updated_at is nullable
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            // User role: 'admin', 'editor', or 'user'
            $table->string('user_type')->default('user')->comment('admin, editor, user');
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
