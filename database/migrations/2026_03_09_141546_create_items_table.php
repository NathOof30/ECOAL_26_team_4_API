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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->varchar('image_url');
            $table->boolean('status')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->integer('collection_id');
            $table->integer('category1_id');
            $table->integer('category2_id')->nullable();
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
