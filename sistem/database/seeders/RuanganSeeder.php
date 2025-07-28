<?php

namespace Database\Seeders;

use App\Models\Ruangan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RuanganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ruangans = [
            'Ruang Kepala Dinas',
            'Ruang TU Kepala Dinas',
            'Ruang Sekretaris',
            'Ruang TU Sekretaris',
            'Ruang Kabid Mineral dan Batubara',
            'Ruang Mineral dan Batubara',
            'Ruang Kabid Geologi dan Air Tanah',
            'Ruang Geologi dan Air Tanah',
            'Ruang Kabid Energi Baru Terbarukan',
            'Ruang Energi Baru Terbarukan',
            'Ruang Kabid Ketenagalistrikan',
            'Ruang Ketenagalistrikan',
            'Ruang Kasubag Umum dan Kepegawaian',
            'Ruang Umum dan Kepegawaian',
            'Ruang Kasubag Bagian Program',
            'Ruang Sub Bagian Program',
            'Ruang Kasubag Keuangan',
            'Ruang Sub Bagian Keuangan',
            'Ruang Transit',
            'Ruang Lobby',
            'Gudang Server',
        ];

        foreach ($ruangans as $ruangan) {
            Ruangan::create([
                'nama_ruangan' => $ruangan,
                'slug' => Str::slug($ruangan),
            ]);
        }
    }
}
