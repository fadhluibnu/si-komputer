<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Komputer;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $komputer = Komputer::with('ruangan')->get();

        $total_komputer = $komputer->count();
        $kondisi_baik = $komputer->whereIn('kondisi_komputer', ['Sangat Baik', 'Baik'])->count();
        $kondisi_perlu_perhatian = $komputer->whereIn('kondisi_komputer', ['Cukup', 'Kurang'])->count();
        $kondisi_rusak = $komputer->where('kondisi_komputer', 'Rusak')->count(); 

        $komputers = $komputer->whereIn('kondisi_komputer', ['Kurang', 'Rusak'])
                             ->sortByDesc('kondisi_komputer')
                             ->values();
                             
        return view('admin.dashboard', [
            'total_komputer' => $total_komputer,
            'kondisi_baik' => $kondisi_baik,
            'kondisi_perlu_perhatian' => $kondisi_perlu_perhatian,
            'kondisi_rusak' => $kondisi_rusak,
            'komputers' => $komputers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
