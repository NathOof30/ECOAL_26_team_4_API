<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Seed the category table from the category.txt data file.
     * Expected: 'Mécanisme' and 'Période'.
     */
    public function run(): void
    {
        $file = database_path('data/category.txt');
        $lines = array_filter(explode("\n", file_get_contents($file)));
        $header = str_getcsv(array_shift($lines)); // Read header row

        foreach ($lines as $line) {
            $data = str_getcsv($line);
            if (count($data) < count($header)) continue;

            $row = array_combine($header, $data);

            DB::table('category')->insert([
                'title' => $row['title'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
