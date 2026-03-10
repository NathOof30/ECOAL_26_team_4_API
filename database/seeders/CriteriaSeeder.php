<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CriteriaSeeder extends Seeder
{
    /**
     * Seed the criteria table from the criteria.txt data file.
     * Expected: 'Durabilité', 'Prix', 'Rareté', 'Autonomie'.
     */
    public function run(): void
    {
        $file = database_path('data/criteria_copy.txt');
        $lines = array_filter(explode("\n", file_get_contents($file)));
        $header = str_getcsv(array_shift($lines)); // Read header row

        foreach ($lines as $line) {
            $data = str_getcsv($line);
            if (count($data) < count($header)) continue;

            $row = array_combine($header, $data);

            DB::table('criteria')->insert([
                'name' => $row['name'],
            ]);
        }
    }
}
