<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\GalleryKomputer;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            RuanganSeeder::class,
            // KomputerSeeder::class,
            // GalleryKomputerSeeder::class,
            // RiwayatPerbaikanKomputerSeeder::class,
        ]);
    }
}