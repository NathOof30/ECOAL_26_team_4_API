<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the items table (lighters/briquets).
     * Collection and category links are managed through pivot tables.
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
