<?php

namespace App\Service\Komputer;

use App\Models\Komputer;
use App\Models\RiwayatPerbaikanKomputer;
use Illuminate\Http\Request;

class RiwayatPerbaikan
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getFilteredRiwayat(array $filters, int $perPage, $uuid)
    {
        // Ambil data komputer sekali saja
        $komputer = Komputer::where('uuid', $uuid)->firstOrFail();

        // Ambil semua riwayat milik komputer ini
        $query = RiwayatPerbaikanKomputer::where('asset_id', $komputer->id);

        // Filter keyword
        if (!empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('jenis_maintenance', 'like', "%{$keyword}%")
                    ->orWhere('teknisi', 'like', "%{$keyword}%")
                    ->orWhere('keterangan', 'like', "%{$keyword}%");
            });
        }

        // Filter jenis maintenance
        if (!empty($filters['jenis'])) {
            $query->where('jenis_maintenance', $filters['jenis']);
        }

        // Ambil riwayat dengan pagination
        $riwayat = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Kembalikan komputer + riwayat
        return [
            'komputer' => $komputer,
            'riwayats' => $riwayat,
        ];
    }

    public function validationStore(Request $request)
    {
        return $request->validate([
            'jenis_maintenance' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'teknisi' => 'required|string|max:255',
            'komponen_diganti' => 'nullable|string|max:255',
            'biaya_maintenance' => 'nullable|numeric|min:0',
            'hasil_maintenance' => 'nullable|string|max:500',
            'rekomendasi' => 'nullable|string|max:500',
        ]);
    }

    public function validationUpdate(Request $request)
    {
        return $request->validate([
            'jenis_maintenance' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'teknisi' => 'required|string|max:255',
            'komponen_diganti' => 'nullable|string|max:255',
            'biaya_maintenance' => 'nullable|numeric|min:0',
            'hasil_maintenance' => 'nullable|string|max:500',
            'rekomendasi' => 'nullable|string|max:500',
        ]);
    }

    public function store($data, $uuid_komputer)
    {
        // Find komputer by uuid
        $komputer = Komputer::where('uuid', $uuid_komputer)->firstOrFail();
        $data['asset_id'] = $komputer->id;

        $riwayat = RiwayatPerbaikanKomputer::create($data);
        return $riwayat;
    }

    public function update($data, $uuid_riwayat)
    {
        $riwayat = RiwayatPerbaikanKomputer::where('uuid', $uuid_riwayat)->firstOrFail();
        $riwayat->update($data);
        return $riwayat;
    }
    
    /**
     * Delete a maintenance record
     * 
     * @param string $uuid_riwayat The UUID of the maintenance record to delete
     * @return bool True if deletion was successful
     */
    public function destroy($uuid_riwayat)
    {
        $riwayat = RiwayatPerbaikanKomputer::where('uuid', $uuid_riwayat)->firstOrFail();
        return $riwayat->delete();
    }
}
