<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the criteria table for evaluating items.
     * Expected entries: 'Durabilité', 'Prix', 'Rareté', 'Autonomie'.
     */
    public function up(): void
    {
        Schema::create('criteria', function (Blueprint $table) {
            $table->id('id_criteria'); // Custom primary key name to match the SQL schema
            $table->string('name'); // Criterion name (e.g. 'Durabilité', 'Prix')
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criteria');
    }
};
