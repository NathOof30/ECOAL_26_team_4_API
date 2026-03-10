<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the items table (lighters/briquets in the collection).
     * Each item belongs to one collection and references up to two categories.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key

            $table->string('title'); // Item name (e.g. 'Zippo 1941 Replica')
            $table->text('description')->nullable(); // Optional detailed description
            $table->string('image_url')->nullable(); // URL to the item's image
            $table->boolean('status')->default(false); // Publication status (false = private, true = public)
            $table->timestamp('created_at')->useCurrent(); // Creation timestamp

            // Foreign key to collections table — each item belongs to a collection
            $table->foreignId('collection_id')
                ->constrained()
                ->onDelete('cascade');

            // Foreign key to category table — primary category (e.g. Mécanisme)
            $table->foreignId('category1_id')
                ->constrained('category')
                ->onDelete('cascade');

            // Foreign key to category table — optional secondary category (e.g. Période)
            $table->foreignId('category2_id')
                ->nullable()
                ->constrained('category')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
