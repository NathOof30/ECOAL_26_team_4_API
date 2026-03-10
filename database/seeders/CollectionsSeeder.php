<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CollectionsSeeder extends Seeder
{
    /**
     * Seed the collections table from the collections.txt data file.
     */
    public function run(): void
    {
        $file = database_path('data/collections_copy.txt');
        $lines = array_filter(explode("\n", file_get_contents($file)));
        $header = str_getcsv(array_shift($lines)); // Read header row

        foreach ($lines as $line) {
            $data = str_getcsv($line);
            if (count($data) < count($header)) continue;

            $row = array_combine($header, $data);

            DB::table('collections')->insert([
                'title' => $row['title'],
                'description' => $row['description'] ?: null,
                'user_id' => (int) $row['user_id'],
            ]);
        }
    }
}
