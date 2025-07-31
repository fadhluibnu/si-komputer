<?php

namespace App\Service\Komputer;

use App\Models\Komputer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class KomputerUpdate
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Validate the update input.
     */
    public function validateInput(Request $request, $uuid)
    {
        // Find the komputer by UUID first to get the ID
        // $komputer = Komputer::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'kode_barang' => [
                'required',
                'string',
                Rule::unique('komputers')->ignore($uuid, 'uuid')
            ],
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
            'foto.*' => [
                'nullable',
                'file', // Menggunakan aturan 'file' yang lebih umum
                'mimes:jpeg,png,jpg,gif,svg,pdf', // Menambahkan 'pdf' ke tipe file yang diizinkan
                'max:10240' // Maksimal 10MB (10 * 1024 KB)
            ],
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'exists:gallery_komputers,id',
            'uuid' => 'sometimes|nullable|uuid',
        ]);

        return $validated;
    }

    /**
     * Update the computer data.
     */
    public function updateKomputer(Komputer $komputer, array $data)
    {
        // Update the computer data
        $komputer->update($data);

        // Clear cache for this computer (both UUID and kode_barang based caches)
        Cache::forget('komputer_' . $komputer->kode_barang);
        Cache::forget('komputer_uuid_' . $komputer->uuid);
        Cache::forget('ruangan_list');

        return $komputer;
    }

    /**
     * Handle image updates for the computer.
     */
    public function handleGallery(Komputer $komputer, Request $request)
    {
        // Handle image deletions
        if ($request->has('delete_images')) {
            foreach ($request->delete_images as $imageId) {
                $gallery = $komputer->galleries()->find($imageId);
                if ($gallery) {
                    // Delete the image file from storage
                    if (Storage::disk('public')->exists($gallery->image_path)) {
                        Storage::disk('public')->delete($gallery->image_path);
                    }
                    // Delete the gallery record
                    $gallery->delete();
                }
            }
        }

        // Handle new files (images or PDFs)
        if ($request->hasFile('foto')) {
            $files = $request->file('foto');

            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('komputers', $fileName, 'public');

                // Simpan data file ke tabel gallery_komputers
                $komputer->galleries()->create([
                    'image_path' => $path,
                    // Anda bisa menambahkan logika untuk membedakan tipe file jika perlu
                    // 'file_type' => $file->getClientMimeType() 
                    'image_type' => 'front',
                ]);
            }
        }
    }
}
