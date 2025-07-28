<?php

namespace App\Service\Komputer;

use App\Models\Komputer;
use Illuminate\Support\Facades\Cache;

class KomputerGetData
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getFilteredKomputers(array $filters, int $perPage)
    {
        $query = Komputer::query();

        if (!empty($filters['keyword'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('nama_komputer', 'like', '%' . $filters['keyword'] . '%')
                    ->orWhere('kode_barang', 'like', '%' . $filters['keyword'] . '%')
                    ->orWhere('merek_komputer', 'like', '%' . $filters['keyword'] . '%');
            });
        }

        if (!empty($filters['kondisi'])) {
            $query->where('kondisi_komputer', $filters['kondisi']);
        }

        if (!empty($filters['ruangan'])) {
            $query->where('ruangan_id', $filters['ruangan']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
    }

    public function getUniqueRuangan()
    {
        return Cache::remember('ruangan_list', now()->addHours(24), function () {
            return \App\Models\Ruangan::orderBy('nama_ruangan')->get();
        });
    }

    public function getByKodeBarang(string $kode_barang)
    {
        return Cache::remember('komputer_' . $kode_barang, now()->addHours(24), function () use ($kode_barang) {
            return Komputer::where('kode_barang', $kode_barang)->with('galleries')->firstOrFail();
        });
    }
    
    public function getByUuid(string $uuid)
    {
        return Komputer::where('uuid', $uuid)->with('galleries')->firstOrFail();
    }
}
