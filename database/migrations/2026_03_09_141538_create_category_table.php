<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Creates the category table.
     * Expected entries: 'Mécanisme' (ignition type) and 'Période' (era).
     */
    public function up(): void
    {
        Schema::create('category', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('title'); // Category name (e.g. 'Mechanism : Spark wheel, Piezoelectric, Electric arc, Friction', 'Period : Antique (Pre-1920), Vintage (1920-1970), Modern (1970+)')
            $table->timestamps(); // created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category');
    }
};
