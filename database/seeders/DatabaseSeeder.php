<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Order matters: users first, then collections, categories, criteria, items, and finally item_criteria.
     */
    public function run(): void
    {
        $this->call([
            UsersSeeder::class,        // 1. Users (no dependencies)
            CollectionsSeeder::class,   // 2. Collections (depends on users)
            CategorySeeder::class,      // 3. Categories (no dependencies)
            CriteriaSeeder::class,      // 4. Criteria (no dependencies)
            ItemsSeeder::class,         // 5. Items (depends on collections + categories)
            ItemCriteriaSeeder::class,  // 6. Item-Criteria scores (depends on items + criteria)
        ]);
    }
}
