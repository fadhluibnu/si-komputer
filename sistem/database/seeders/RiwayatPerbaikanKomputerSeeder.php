<?php

namespace Database\Seeders;

use App\Models\Komputer;
use App\Models\RiwayatPerbaikanKomputer;
use Illuminate\Database\Seeder;

class RiwayatPerbaikanKomputerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dapatkan semua aset komputer
        $komputers = Komputer::all();

        // Daftar jenis maintenance
        $maintenanceTypes = [
            'Pembersihan Rutin',
            'Upgrade Hardware',
            'Reinstall OS',
            'Perbaikan Hardware',
            'Penggantian Komponen',
            'Update Driver',
            'Optimasi Kinerja',
            'Pemeriksaan Berkala',
        ];

        // Daftar teknisi
        $technicians = [
            'Ahmad Fauzi',
            'Budi Santoso',
            'Cindy Permata',
            'Dewi Sartika',
            'Eko Prasetyo',
            'Farhan Akbar',
            'Gina Ratnasari',
            'Hendro Wibowo',
        ];

        // Daftar komponen yang mungkin diganti
        $components = [
            'RAM',
            'SSD',
            'HDD',
            'Baterai',
            'Keyboard',
            'Mouse',
            'Monitor',
            'Fan',
            'Power Supply',
            'Motherboard',
            'Thermal Paste',
        ];

        // Daftar hasil maintenance
        $results = [
            'Berhasil diperbaiki',
            'Berhasil diupgrade',
            'Berhasil dibersihkan',
            'Masalah teratasi',
            'Kinerja meningkat',
            'Pemeriksaan selesai',
            'Komponen berhasil diganti',
            'Belum berhasil, perlu tindak lanjut',
            'Perlu penggantian komponen',
        ];

        // Daftar rekomendasi
        $recommendations = [
            'Pemeriksaan rutin setiap 3 bulan',
            'Peningkatan RAM',
            'Penggantian HDD ke SSD',
            'Penggantian baterai dalam 1-2 bulan',
            'Penggantian keyboard',
            'Upgrade sistem operasi',
            'Penggantian unit dalam 1 tahun',
            'Peningkatan kapasitas penyimpanan',
            'Tidak ada rekomendasi khusus',
            'Peningkatan cooling system',
        ];

        // Untuk setiap aset komputer, tambahkan 0-3 riwayat perbaikan
        foreach ($komputers as $komputer) {
            $numMaintenance = rand(0, 3);

            for ($i = 0; $i < $numMaintenance; $i++) {
                // Pilih tipe maintenance acak
                $maintenanceType = $maintenanceTypes[array_rand($maintenanceTypes)];

                // Pilih teknisi acak
                $technician = $technicians[array_rand($technicians)];

                // Acak komponen yang diganti (0-2 komponen)
                $numComponents = rand(0, 2);
                $componentList = [];
                for ($j = 0; $j < $numComponents; $j++) {
                    $componentList[] = $components[array_rand($components)];
                }
                $replacedComponents = count($componentList) ? implode(', ', $componentList) : 'Tidak ada';

                // Pilih hasil maintenance acak
                $maintenanceResult = $results[array_rand($results)];

                // Pilih rekomendasi acak
                $recommendation = $recommendations[array_rand($recommendations)];

                // Acak biaya maintenance antara 0 dan 3 juta (simpan sebagai integer)
                $cost = rand(0, 3000000);

                RiwayatPerbaikanKomputer::create([
                    'asset_id' => $komputer->id,
                    'jenis_maintenance' => $maintenanceType,
                    'keterangan' => 'Maintenance ' . $maintenanceType . ' untuk ' . $komputer->nama_komputer,
                    'teknisi' => $technician,
                    'komponen_diganti' => $replacedComponents,
                    'biaya_maintenance' => $cost,
                    'hasil_maintenance' => $maintenanceResult,
                    'uuid' => \Illuminate\Support\Str::uuid()->toString(),
                    'rekomendasi' => $recommendation,
                ]);
            }
        }
    }
}
