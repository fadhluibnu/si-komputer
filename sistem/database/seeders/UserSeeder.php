<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create superadmin user
        User::create([
            'username' => 'superadmin',
            'password' => Hash::make('superadmin123'),
            'nama_lengkap' => 'Super Administrator',
            'email' => 'superadmin@esdm.go.id',
            'role' => 'superadmin',
            'last_login' => null,
        ]);

        // Create admin user
        User::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'nama_lengkap' => 'Administrator',
            'email' => 'admin@esdm.go.id',
            'role' => 'admin',
            'last_login' => null,
        ]);

        // Array untuk role-role berdasarkan gambar
        $roles = [
            'UPT Laboratorium ESDM',
            'Sekretariat',
            'Bidang Umum dan Kepegawaian',
            'Cabang Dinas ESDM Wilayah Solo',
            'Cabang Dinas ESDM Wilayah Serayu Tengah',
            'Cabang Dinas ESDM Wilayah Merapi',
            'Cabang Dinas ESDM Wilayah Kendeng Selatan',
            'Cabang Dinas ESDM Wilayah Sewu Lawu',
            'Cabang Dinas ESDM Wilayah Slamet Selatan',
            'Cabang Dinas ESDM Wilayah Kendeng Muria',
            'Cabang Dinas ESDM Wilayah Slamet Utara',
            'Cabang Dinas ESDM Wilayah Ungaran Telomoyo',
            'Cabang Dinas ESDM Wilayah Serayu Selatan',
            'Cabang Dinas ESDM Wilayah Serayu Utara',
            'Bidang Energi Baru Terbarukan',
            'Bidang Geologi dan Air Tanah',
            'Bidang Mineral dan Batu Bara',
            'Bidang Ketenagalistrikan',
        ];

        // Buat user untuk setiap role
        foreach ($roles as $index => $role) {
            // Buat username dari role
            $username = strtolower(str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9\s]/', '', $role)));

            // Buat email dari username
            $email = $username . '@esdm.go.id';

            User::create([
                'username' => $username,
                'nama_lengkap' => 'Pengguna ' . $role,
                'email' => $email,
                'password' => Hash::make('password123'),
                'role' => $role,
                'last_login' => now()->subDays(rand(0, 30)),
            ]);
        }
    }
}
