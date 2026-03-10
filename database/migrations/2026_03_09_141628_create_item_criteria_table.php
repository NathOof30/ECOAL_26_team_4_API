<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the item_criteria pivot table linking items to their criteria scores.
     * Each item has one row per criterion, with a value of 0 (Low), 1 (Medium), or 2 (High).
     */
    public function up(): void
    {
        Schema::create('item_criteria', function (Blueprint $table) {
            // Foreign key to items table
            $table->unsignedBigInteger('id_item');
            $table->foreign('id_item')
                ->references('id')
                ->on('items')
                ->onDelete('cascade');

            // Foreign key to criteria table
            $table->unsignedBigInteger('id_criteria');
            $table->foreign('id_criteria')
                ->references('id_criteria')
                ->on('criteria')
                ->onDelete('cascade');

            // Score value for this criterion: 0 = Low, 1 = Medium, 2 = High
            $table->integer('value')->comment('0: Low, 1: Medium, 2: High');

            // Composite primary key ensures one score per item-criterion pair
            $table->primary(['id_item', 'id_criteria']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_criteria');
    }
};
