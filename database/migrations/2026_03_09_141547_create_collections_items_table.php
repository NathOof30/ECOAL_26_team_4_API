<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections_items', function (Blueprint $table) {
            $table->foreignId('id_collection')
                ->constrained('collections')
                ->cascadeOnDelete();

            $table->foreignId('id_item')
                ->unique()
                ->constrained('items')
                ->cascadeOnDelete();

            $table->unique(['id_collection', 'id_item']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collections_items');
    }
};
