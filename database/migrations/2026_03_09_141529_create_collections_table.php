<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the collections table. Each collection belongs to one user.
     * A user can only have one collection (enforced by unique constraint).
     */
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key

            $table->string('title'); // Collection name
            $table->text('description')->nullable(); // Optional description of the collection

            // Foreign key to users table — unique ensures one collection per user
            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
