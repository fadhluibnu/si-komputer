<?php

namespace Database\Seeders;

use App\Models\GalleryKomputer;
use App\Models\Komputer;
use Illuminate\Database\Seeder;

class GalleryKomputerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dapatkan semua aset komputer
        $komputers = Komputer::all();

        // Daftar tipe gambar
        $imageTypes = ['front', 'back', 'side', 'detail', 'keyboard', 'screen', 'ports', 'inside'];
        
        // Daftar file gambar komputer yang tersedia di storage/public/komputers
        $komputerImages = [
            '1752858283_wallpaperflare.com_wallpaper(1).jpg',
            '1752858283_wallpaperflare.com_wallpaper.jpg',
            '1752859581_wallpaperflare.com_wallpaper(1).jpg',
            '1752859581_wallpaperflare.com_wallpaper.jpg',
            '1752860208_Desktop - 6.png',
            '1752860208_wallpaperflare.com_wallpaper(1).jpg',
            '1752860208_wallpaperflare.com_wallpaper.jpg',
            '1752860721_Desktop - 6.png',
            '1752860721_wallpaperflare.com_wallpaper(1).jpg',
            '1752860721_wallpaperflare.com_wallpaper.jpg',
            '1752957715_wallpaperflare.com_wallpaper(1).jpg',
            '1752957715_wallpaperflare.com_wallpaper.jpg',
            '1752958560_wallpaperflare.com_wallpaper(1).jpg',
            '1752958560_wallpaperflare.com_wallpaper.jpg',
            'msi_1_ports_rxysAvI5.jpg'
        ];

        // Untuk setiap aset komputer, tambahkan 1-4 gambar
        foreach ($komputers as $komputer) {
            $numImages = rand(1, 4);

            for ($i = 0; $i < $numImages; $i++) {
                // Pilih tipe gambar acak
                $imageType = $imageTypes[array_rand($imageTypes)];
                
                // Pilih file gambar acak
                $imageName = $komputerImages[array_rand($komputerImages)];
                
                // Gunakan format path yang diminta
                $imagePath = 'komputers/' . $imageName;

                GalleryKomputer::create([
                    'asset_id' => $komputer->id,
                    'image_path' => $imagePath,
                    'image_type' => $imageType,
                    'uuid' => \Illuminate\Support\Str::uuid()->toString(),
                ]);
            }
        }
    }
}
