<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items_categories', function (Blueprint $table) {
            $table->foreignId('id_item')
                ->constrained('items')
                ->cascadeOnDelete();

            $table->foreignId('id_category')
                ->constrained('category')
                ->cascadeOnDelete();

            $table->primary(['id_item', 'id_category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items_categories');
    }
};
