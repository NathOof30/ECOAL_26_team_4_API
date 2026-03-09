<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Seed the users table from the users.txt data file.
     * Password for all users: 1234
     */
    public function run(): void
    {
        $file = database_path('data/users.txt');
        $lines = array_filter(explode("\n", file_get_contents($file)));
        $header = str_getcsv(array_shift($lines)); // Read header row

        foreach ($lines as $line) {
            $data = str_getcsv($line);
            if (count($data) < count($header)) continue;

            $row = array_combine($header, $data);

            DB::table('users')->insert([
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => Hash::make($row['password']), // Hash the password (1234)
                'avatar_url' => $row['avatar_url'] ?: null,
                'nationality' => $row['nationality'] ?: null,
                'is_active' => (bool) $row['is_active'],
                'user_type' => $row['user_type'],
                'created_at' => now(),
            ]);
        }
    }
}
