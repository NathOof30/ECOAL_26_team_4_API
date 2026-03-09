<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemCriteriaSeeder extends Seeder
{
    /**
     * Seed the item_criteria pivot table from the item_criteria.txt data file.
     * Each item gets 4 scores (one per criterion), value = 0 (Low), 1 (Medium), 2 (High).
     */
    public function run(): void
    {
        $file = database_path('data/item_criteria.txt');
        $lines = array_filter(explode("\n", file_get_contents($file)));
        $header = str_getcsv(array_shift($lines)); // Read header row

        foreach ($lines as $line) {
            $data = str_getcsv($line);
            if (count($data) < count($header)) continue;

            $row = array_combine($header, $data);

            DB::table('item_criteria')->insert([
                'id_item' => (int) $row['id_item'],
                'id_criteria' => (int) $row['id_criteria'],
                'value' => (int) $row['value'],
            ]);
        }
    }
}
