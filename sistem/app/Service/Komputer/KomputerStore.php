<?php

namespace App\Service\Komputer;

use App\Models\Komputer;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Exception;

class KomputerStore
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function validateInput(Request $request)
    {
        $validated = $request->validate([
            'kode_barang' => 'required|string|unique:komputers,kode_barang',
            'nama_komputer' => 'nullable|string',
            'merek_komputer' => 'required|string',
            'tahun_pengadaan' => 'required|integer|min:2000|max:' . date('Y'),
            'spesifikasi_ram' => 'required|string',
            'spesifikasi_vga' => 'nullable|string',
            'spesifikasi_processor' => 'required|string',
            'spesifikasi_penyimpanan' => 'required|string',
            'sistem_operasi' => 'required|string',
            'nama_pengguna_sekarang' => 'nullable|string',
            'kesesuaian_pc' => 'required|string',
            'kondisi_komputer' => 'required|string',
            'keterangan_kondisi' => 'required|string',
            'penggunaan_sekarang' => 'required|string',
            'ruangan_id' => 'required|exists:ruangans,id',
            'barcode' => 'nullable|string|unique:komputers,barcode',
            'uuid' => 'nullable|uuid',
            'foto' => 'required|array',
            'foto.*' => [
                'file', // Menambahkan validasi tipe file
                'mimes:jpeg,png,jpg,gif,svg,pdf', // Menambahkan 'pdf' ke tipe yang diizinkan
                'max:10240' // Maksimal 10MB
            ],
        ]);

        return $validated;
    }

    public function generateQRCode($uuid, $komputerData = null)
    {
        // Path yang akan digunakan untuk menyimpan QR code

        // if ($komputerData === null) {
        // } else {
        //     $filename = "{$komputerData['nama_pengguna_sekarang']}-{$komputerData['kode_barang']}-{$uuid}.jpg";
        // }

        // For existing computers, get the data from database
        // For new computers, use the data provided in $komputerData
        if ($komputerData === null) {
            $komputer = \App\Models\Komputer::where('uuid', $uuid)->first();
            $filename = "{$komputer->nama_pengguna_sekarang}-{$komputer->kode_barang}-{$uuid}.jpg";
        } else {
            $filename = "{$komputerData['nama_pengguna_sekarang']}-{$komputerData['kode_barang']}-{$uuid}.jpg";
        }
        $directory = 'barcode';
        $path = "{$directory}/{$filename}";

        // For existing computers, get the data from database
        // For new computers, use the data provided in $komputerData
        if ($komputerData === null) {
            $komputer = \App\Models\Komputer::where('uuid', $uuid)
                ->with(['ruangan', 'riwayatPerbaikan' => function ($query) {
                    $query->orderBy('created_at', 'desc')->limit(2);
                }])
                ->first();

            if (!$komputer) {
                // Create simple QR code with only URL
                $qrTextContent = "Cek Online: " . config('app.url') . "/scan/{$uuid}";

                // Konfigurasi QR code sederhana
                $barcodeOptions = new QROptions([
                    "outputType" => QROutputInterface::GDIMAGE_PNG,
                    "eccLevel" => EccLevel::L,
                    "scale" => 10,
                    "imageBase64" => false,
                ]);

                // Buat objek QR code
                $qrCode = new QRCode($barcodeOptions);

                // Generate QR code sebagai string
                $qrCodeImage = $qrCode->render($qrTextContent);

                // Simpan file menggunakan Storage
                Storage::put($path, $qrCodeImage);

                // Return path publik yang bisa diakses browser
                return $path;
            }
        } else {
            // Use the provided data (for new computer)
            $komputer = (object) $komputerData;

            // We don't have ruangan or riwayat perbaikan for new computers
            $komputer->ruangan = (object) ['nama_ruangan' => $komputerData['ruangan_name'] ?? 'Tidak ditentukan'];
            $komputer->riwayatPerbaikan = collect();
        }

        // Format the text content for the QR code
        $qrTextContent = "Cek Online: " . config('app.url') . "/scan/{$uuid}\n\n";

        // Detail Komputer
        // $qrTextContent .= "DETAIL KOMPUTER\n";
        // $qrTextContent .= "Nama Komputer: {$komputer->nama_komputer}\n";
        // $qrTextContent .= "Kode Aset: {$komputer->kode_barang}\n";
        // $qrTextContent .= "Merek: {$komputer->merek_komputer}\n";
        // $qrTextContent .= "Pengguna: " . ($komputer->nama_pengguna_sekarang ?? 'Tidak ditentukan') . "\n";
        // $qrTextContent .= "Ruangan: {$komputer->ruangan->nama_ruangan}\n\n";

        // // Kondisi Komputer
        // $qrTextContent .= "KONDISI KOMPUTER\n";
        // $qrTextContent .= "Kondisi: {$komputer->kondisi_komputer}\n";
        // $qrTextContent .= "Keterangan: {$komputer->keterangan_kondisi}\n";
        // $qrTextContent .= "Processor: {$komputer->spesifikasi_processor}\n";
        // $qrTextContent .= "RAM: {$komputer->spesifikasi_ram}\n";
        // $qrTextContent .= "Storage: {$komputer->spesifikasi_penyimpanan}\n\n";

        // // Riwayat Pemeliharaan
        // $qrTextContent .= "RIWAYAT PEMELIHARAAN\n";
        // if (isset($komputer->riwayatPerbaikan) && $komputer->riwayatPerbaikan->count() > 0) {
        //     foreach ($komputer->riwayatPerbaikan as $index => $riwayat) {
        //         $qrTextContent .= "# Pemeliharaan " . ($index + 1) . "\n";
        //         $qrTextContent .= "Tanggal: " . $riwayat->created_at->format('d/m/Y') . "\n";
        //         $qrTextContent .= "Jenis: {$riwayat->jenis_maintenance}\n";
        //         $qrTextContent .= "Teknisi: {$riwayat->teknisi}\n";
        //         $qrTextContent .= "Hasil: {$riwayat->hasil_maintenance}\n";
        //         $qrTextContent .= "\n";
        //     }
        // } else {
        //     $qrTextContent .= "Belum ada riwayat pemeliharaan\n";
        // }

        // Konfigurasi QR code - Meningkatkan error correction untuk menampung lebih banyak teks
        $barcodeOptions = new QROptions([
            "outputType" => QROutputInterface::GDIMAGE_PNG,
            "eccLevel" => EccLevel::H, // Gunakan EccLevel::H untuk konten teks yang lebih panjang
            "scale" => 10,
            "imageBase64" => false,
            "moduleValues" => [
                // Warna untuk blok QR
                1536 => [0, 0, 0], // marker dark
                6    => [0, 0, 0], // dark
                // Warna untuk background QR
                5632 => [255, 255, 255], // marker light
                7    => [255, 255, 255], // light
            ],
        ]);

        // Buat objek QR code
        $qrCode = new QRCode($barcodeOptions);

        // Try to generate QR code with all info, but fallback to simpler content if too large
        try {
            // Generate QR code sebagai string dengan semua informasi komputer
            $qrCodeImage = $qrCode->render($qrTextContent);
        } catch (\Exception $e) {
            // If we get an error (likely due to content being too large), generate a simpler QR code
            \Illuminate\Support\Facades\Log::warning("QR code content too large, falling back to URL only: " . $e->getMessage());

            // Create simple QR code with only URL and basic info
            $simpleQrContent = "Cek Online: " . config('app.url') . "/scan/{$uuid}\n\n";
            $simpleQrContent .= "Komputer: {$komputer->nama_komputer}\n";
            $simpleQrContent .= "Kode: {$komputer->kode_barang}\n";

            $qrCodeImage = $qrCode->render($simpleQrContent);
        }

        // Simpan file menggunakan Storage
        Storage::put($path, $qrCodeImage);

        // Return path publik yang bisa diakses browser
        return $path;
    }

    public function storeKomputer($datas)
    {
        // Simpan data komputer ke database
        $komputer = Komputer::create([
            'user_id' => $datas['user_id'] ?? 1, // Use user_id from input or default to 1
            ...$datas  // UUID is now passed in $datas from the controller
        ]);

        return $komputer;
    }

    public function storeGallery(Komputer $komputer, Request $request)
    {
        if ($request->hasFile('foto')) {
            $files = $request->file('foto');

            // Ensure $files is always treated as an array
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('komputers', $fileName, 'public');

                // Simpan data foto ke tabel gallery_komputers
                $komputer->galleries()->create([
                    'image_path' => $path,
                    'image_type' => 'front',
                ]);
            }
        }
    }
}
