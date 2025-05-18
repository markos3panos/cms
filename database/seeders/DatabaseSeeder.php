<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Language;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin user if not exists
        User::firstOrCreate(
            ['email' => 'markospapapanos@gmail.com'],
            ['name' => 'Admin']
        );

        // Create Languages if they don't exist
        $languages = [
            ['id' => 1, 'code' => 'el', 'name' => 'Greek'],
            ['id' => 2, 'code' => 'en', 'name' => 'English'],
        ];

        foreach ($languages as $lang) {
            Language::firstOrCreate(['id' => $lang['id']], $lang);
        }
    }
}
