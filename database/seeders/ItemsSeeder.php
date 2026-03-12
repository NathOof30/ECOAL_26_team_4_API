<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemsSeeder extends Seeder
{
    /**
     * Seed the items table from the items.txt data file.
     * Contains 12 lighters spread across 5 collections.
     */
    public function run(): void
    {
        $file = database_path('data/items.txt');
        $lines = array_filter(explode("\n", file_get_contents($file)));
        $header = str_getcsv(array_shift($lines)); // Read header row

        foreach ($lines as $line) {
            $data = str_getcsv($line);
            if (count($data) < count($header)) {
                continue;
            }

            $row = array_combine($header, $data);

            $itemId = DB::table('items')->insertGetId([
                'title' => $row['title'],
                'description' => $row['description'] ?: null,
                'image_url' => $row['image_url'] ?: null,
                'status' => (bool) $row['status'],
                'created_at' => now(),
            ]);

            DB::table('collections_items')->insert([
                'id_collection' => (int) $row['collection_id'],
                'id_item' => $itemId,
            ]);

            foreach (array_filter(explode('|', (string) $row['category_ids'])) as $categoryId) {
                DB::table('items_categories')->insert([
                    'id_item' => $itemId,
                    'id_category' => (int) $categoryId,
                ]);
            }
        }
    }
}
