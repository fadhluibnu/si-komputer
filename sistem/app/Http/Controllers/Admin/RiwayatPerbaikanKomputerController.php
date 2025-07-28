<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Komputer;
use App\Models\RiwayatPerbaikanKomputer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Service\Komputer\KomputerGetData;
use App\Service\Komputer\KomputerStore;
use App\Service\Komputer\KomputerUpdate;
use App\Service\Komputer\RiwayatPerbaikan;

class RiwayatPerbaikanKomputerController extends Controller
{

    private $komputerGetData;
    private $komputerStore;
    private $komputerUpdate;
    private $riwayatPerbaikan;

    public function __construct(
        KomputerGetData $komputerGetData,
        KomputerStore $komputerStore,
        KomputerUpdate $komputerUpdate,
        RiwayatPerbaikan $riwayatPerbaikan
    ) {
        $this->komputerGetData = $komputerGetData;
        $this->komputerStore = $komputerStore;
        $this->komputerUpdate = $komputerUpdate;
        $this->riwayatPerbaikan = $riwayatPerbaikan;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $uuid)
    {
        $filter = $this->riwayatPerbaikan->getFilteredRiwayat($request->all(), 10, $uuid);

        // Get unique maintenance types for filter dropdown
        $jenis_maintenance = RiwayatPerbaikanKomputer::where('asset_id', $filter['komputer']->id)
            ->select('jenis_maintenance')
            ->distinct()
            ->pluck('jenis_maintenance');

        return view('admin.riwayat_perbaikan.riwayat', [
            'riwayat' => $filter['riwayats'],
            'komputer' => $filter['komputer'],
            'jenis_maintenance' => $jenis_maintenance,
            'ruangans' => $this->komputerGetData->getUniqueRuangan()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $uuid_komputer)
    {

        DB::beginTransaction();
        try {
            // Create new maintenance record
            $validated = $this->riwayatPerbaikan->validationStore($request);

            $this->riwayatPerbaikan->store($validated, $uuid_komputer);

            DB::commit();
            return redirect()
                ->route('komputer.riwayat.index', $uuid_komputer)
                ->with('success', 'Riwayat perbaikan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Gagal menambahkan riwayat perbaikan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $komputer_uuid, $riwayat_uuid)
    {
        DB::beginTransaction();
        try {

            $validated = $this->riwayatPerbaikan->validationUpdate($request);

            $this->riwayatPerbaikan->update($validated, $riwayat_uuid);

            DB::commit();
            return redirect()
                ->route('komputer.riwayat.index', $komputer_uuid)
                ->with('success', 'Riwayat perbaikan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Gagal memperbarui riwayat perbaikan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($komputer_uuid, $riwayat_uuid)
    {
        try {
            // Use the service to delete the maintenance record
            $this->riwayatPerbaikan->destroy($riwayat_uuid);

            return redirect()
                ->route('komputer.riwayat.index', $komputer_uuid)
                ->with('success', 'Riwayat perbaikan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus riwayat perbaikan: ' . $e->getMessage());
        }
    }

    /**
     * Export maintenance history
     */
    public function export(Request $request, $uuid)
    {
        // Get computer data first with gallery images
        $komputer = Komputer::where('uuid', $uuid)->with('galleries')->firstOrFail();

        // Set explicit default columns for maintenance history
        $defaultColumns = [
            'nomor_urut',
            'jenis_maintenance',
            'tanggal',
            'teknisi',
            'keterangan',
            'komponen_diganti',
            'biaya_maintenance',
            'hasil_maintenance',
            'rekomendasi'
        ];

        // Use default columns if none are provided in the request
        $format = $request->input('format', 'excel');
        $columns = $request->has('columns') ? $request->input('columns') : $defaultColumns;

        // Get all maintenance records for this computer
        $query = RiwayatPerbaikanKomputer::where('asset_id', $komputer->id);

        // Apply filters if any
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('jenis_maintenance', 'like', "%{$keyword}%")
                    ->orWhere('teknisi', 'like', "%{$keyword}%")
                    ->orWhere('keterangan', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_maintenance', $request->input('jenis'));
        }

        // Get sorted data
        $riwayatPerbaikan = $query->orderBy('created_at', 'desc')->get();

        // Format timestamp for filename
        $timestamp = now()->format('Ymd_His');
        $filename = "riwayat_pemeliharaan_{$uuid}_{$timestamp}";

        // Create export based on requested format
        switch ($format) {
            case 'csv':
                return $this->exportToCSV($komputer, $riwayatPerbaikan, $columns, $filename);
            case 'pdf':
                return $this->exportToPDF($komputer, $riwayatPerbaikan, $columns, $filename);
            case 'excel':
            default:
                return $this->exportToExcel($komputer, $riwayatPerbaikan, $columns, $filename);
        }
    }

    /**
     * Helper method to export maintenance history to Excel
     */
    private function exportToExcel($komputer, $riwayatPerbaikan, $columns, $filename)
    {
        // This requires the Laravel Excel package
        // We'll create a new export class for this
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\RiwayatPerbaikanExport($komputer, $riwayatPerbaikan, $columns), "{$filename}.xlsx");
    }

    /**
     * Helper method to export maintenance history to CSV
     */
    private function exportToCSV($komputer, $riwayatPerbaikan, $columns, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function () use ($komputer, $riwayatPerbaikan, $columns) {
            $file = fopen('php://output', 'w');

            // Add komputer details header row
            fputcsv($file, ['DETAIL KOMPUTER']);
            fputcsv($file, ['Kode Barang', $komputer->kode_barang]);
            fputcsv($file, ['Nama Komputer', $komputer->nama_komputer]);
            fputcsv($file, ['Ruangan', $komputer->ruangan ? $komputer->ruangan->nama_ruangan : 'Tidak tersedia']);
            fputcsv($file, ['Pengguna', $komputer->nama_pengguna_sekarang]);
            fputcsv($file, ['Spesifikasi', "Processor: {$komputer->spesifikasi_processor}, RAM: {$komputer->spesifikasi_ram}, Storage: {$komputer->spesifikasi_penyimpanan}"]);
            fputcsv($file, ['Kondisi', $komputer->kondisi_komputer]);
            fputcsv($file, ['Barcode', $komputer->barcode ? basename($komputer->barcode) : 'Tidak ada barcode']);

            // Add gallery images info if any
            if ($komputer->galleries && $komputer->galleries->count() > 0) {
                fputcsv($file, []);
                fputcsv($file, ['FOTO KOMPUTER']);
                foreach ($komputer->galleries as $index => $gallery) {
                    fputcsv($file, ['Foto ' . ($index + 1), basename($gallery->image_path)]);
                }
            }

            // Empty row as separator
            fputcsv($file, []);

            // Add riwayat perbaikan header row
            fputcsv($file, ['RIWAYAT PEMELIHARAAN']);

            // Add columns header row
            $headerRow = $this->getHeaderRow($columns);
            fputcsv($file, $headerRow);

            // Add data rows
            foreach ($riwayatPerbaikan as $index => $riwayat) {
                $row = $this->formatDataRow($riwayat, $columns, $index);
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper method to export maintenance history to PDF
     */
    // private function exportToPDF($komputer, $riwayatPerbaikan, $columns, $filename)
    // {
    //     $headerRow = $this->getHeaderRow($columns);
    //     $data = [];

    //     // Pre-process barcode image for PDF generation
    //     $barcodePath = null;
    //     $barcodeImage = null;

    //     if ($komputer->barcode) {
    //         // Use the helper to find the barcode file
    //         $barcodePath = $this->findBarcodeFile($komputer->barcode);

    //         if ($barcodePath && file_exists($barcodePath)) {
    //             // If found, store base64 encoded image data
    //             $type = pathinfo($barcodePath, PATHINFO_EXTENSION);
    //             $imageData = file_get_contents($barcodePath);
    //             if ($imageData !== false) {
    //                 $barcodeImage = 'data:image/' . $type . ';base64,' . base64_encode($imageData);
    //             }
    //         }
    //     }

    //     // Pre-process gallery images for PDF
    //     $galleryImages = [];

    //     if ($komputer->galleries && $komputer->galleries->count() > 0) {
    //         foreach ($komputer->galleries as $gallery) {
    //             $imagePath = $this->findGalleryFile($gallery->image_path);

    //             if ($imagePath && file_exists($imagePath)) {
    //                 $type = pathinfo($imagePath, PATHINFO_EXTENSION);
    //                 $imageData = file_get_contents($imagePath);
    //                 if ($imageData !== false) {
    //                     $galleryImages[] = 'data:image/' . $type . ';base64,' . base64_encode($imageData);
    //                 }
    //             }
    //         }
    //     }

    //     // Format data rows for maintenance history
    //     foreach ($riwayatPerbaikan as $index => $riwayat) {
    //         $row = $this->formatDataRow($riwayat, $columns, $index);
    //         $data[] = $row;
    //     }

    //     // Create PDF
    //     $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.riwayat_perbaikan.export_pdf', [
    //         'headerRow' => $headerRow,
    //         'data' => $data,
    //         'title' => 'Riwayat Pemeliharaan Komputer',
    //         'komputer' => $komputer,
    //         'barcodeImage' => $barcodeImage,
    //         'galleryImages' => $galleryImages
    //     ]);

    //     // Configure dompdf options
    //     $dompdf = $pdf->getDomPDF();
    //     $options = $dompdf->getOptions();
    //     $options->setIsRemoteEnabled(true);
    //     $options->setIsHtml5ParserEnabled(true);

    //     // Set paper orientation
    //     $pdf->setPaper('a4', 'landscape');

    //     return $pdf->download("{$filename}.pdf");
    // }

    private function exportToPDF($komputer, $riwayatPerbaikan, $columns, $filename)
    {
        $headerRow = $this->getHeaderRow($columns);
        $data = [];

        // 1. Proses Barcode (tidak ada perubahan)
        $barcodeImage = null;
        if ($komputer->barcode) {
            $barcodePath = $this->findFile($komputer->barcode, 'barcode');
            if ($barcodePath) {
                $type = pathinfo($barcodePath, PATHINFO_EXTENSION);
                $imageData = file_get_contents($barcodePath);
                $barcodeImage = 'data:image/' . $type . ';base64,' . base64_encode($imageData);
            }
        }

        // 2. === PERUBAHAN UTAMA ADA DI SINI ===
        // Proses galeri dan tambahkan URL untuk file PDF
        $galleryData = [];
        if ($komputer->galleries->isNotEmpty()) {
            foreach ($komputer->galleries->take(6) as $gallery) {
                $filePath = $this->findFile($gallery->image_path, 'komputers');
                if (!$filePath) continue;

                $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

                if ($extension === 'pdf') {
                    // Jika PDF, tambahkan 'url' ke dalam array
                    $galleryData[] = [
                        'type' => 'pdf',
                        'name' => basename($filePath),
                        'url'  => asset('storage/' . $gallery->image_path), // <-- BARIS INI DITAMBAHKAN
                    ];
                } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    // Logika untuk gambar tetap sama
                    $imageData = file_get_contents($filePath);
                    $galleryData[] = [
                        'type' => 'image',
                        'data' => 'data:image/' . $extension . ';base64,' . base64_encode($imageData),
                    ];
                }
            }
        }

        // 3. Proses data riwayat (tidak ada perubahan)
        foreach ($riwayatPerbaikan as $index => $riwayat) {
            $data[] = $this->formatDataRow($riwayat, $columns, $index);
        }

        // 4. Kirim data ke view (tidak ada perubahan)
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.riwayat_perbaikan.export_pdf', [
            'headerRow'     => $headerRow,
            'data'          => $data,
            'title'         => 'Riwayat Pemeliharaan Komputer',
            'komputer'      => $komputer,
            'barcodeImage'  => $barcodeImage,
            'galleryImages' => $galleryData,
        ]);

        $pdf->setPaper('a4', 'landscape');
        $pdf->getDomPDF()->set_option("isRemoteEnabled", true); // Pastikan remote-loading aktif

        return $pdf->download("{$filename}.pdf");
    }

    /**
     * Helper to get header row based on selected columns
     */
    private function getHeaderRow($columns)
    {
        $map = [
            'nomor_urut' => 'No',
            'jenis_maintenance' => 'Jenis',
            'tanggal' => 'Tanggal',
            'teknisi' => 'Teknisi',
            'keterangan' => 'Keterangan',
            'komponen_diganti' => 'Komponen Diganti',
            'biaya_maintenance' => 'Biaya',
            'hasil_maintenance' => 'Hasil',
            'rekomendasi' => 'Rekomendasi'
        ];
        return array_map(fn($col) => $map[$col] ?? ucfirst($col), $columns);
    }

    /**
     * Helper to format data row based on selected columns
     */
    private function formatDataRow($riwayat, $columns, $index = null)
    {
        $row = [];
        foreach ($columns as $column) {
            switch ($column) {
                case 'nomor_urut':
                    $row[] = $index + 1;
                    break;
                case 'tanggal':
                    $row[] = $riwayat->created_at->format('d M Y');
                    break;
                case 'biaya_maintenance':
                    $row[] = $riwayat->biaya_maintenance ? 'Rp ' . number_format($riwayat->biaya_maintenance, 0, ',', '.') : '-';
                    break;
                default:
                    $row[] = $riwayat->$column ?? '-';
                    break;
            }
        }
        return $row;
    }

    private function findFile($relativePath, $subfolder = '')
    {
        $filename = basename($relativePath);
        $paths = [
            storage_path('app/public/' . $relativePath),
            storage_path('app/public/' . $subfolder . '/' . $filename),
        ];
        foreach ($paths as $path) {
            if (file_exists($path)) return $path;
        }
        return null;
    }

    /**
     * Helper untuk mencari file barcode di beberapa kemungkinan lokasi
     */
    private function findBarcodeFile($relativePath)
    {
        // Log untuk debugging
        \Illuminate\Support\Facades\Log::info("Mencari barcode dengan path relatif: " . $relativePath);

        // Ambil nama file dari path
        if (strpos($relativePath, '/') !== false) {
            $filename = basename($relativePath);
        } else {
            $filename = $relativePath; // Path relatif sudah berupa nama file
        }

        // Daftar kemungkinan path lengkap, dengan prioritas pada lokasi yang Anda sebutkan
        $possiblePaths = [
            // PRIORITAS 1: Path relatif lengkap
            storage_path('app/public/' . $relativePath),
            public_path('storage/' . $relativePath),

            // PRIORITAS 2: Lokasi di direktori barcode
            storage_path('app/public/barcode/' . $filename),
            public_path('storage/barcode/' . $filename),

            // PRIORITAS 3: Path alternatif lain
            storage_path('app/public/barcode/' . $filename),
            public_path('storage/public/barcode/' . $filename),
            storage_path('app/' . $relativePath),
            public_path($relativePath),
            base_path('storage/app/public/' . $relativePath),
            base_path('public/storage/' . $relativePath),
        ];

        foreach ($possiblePaths as $path) {
            \Illuminate\Support\Facades\Log::info("Mencoba path: " . $path);
            if (file_exists($path)) {
                \Illuminate\Support\Facades\Log::info("Barcode ditemukan di: " . $path);
                return $path;
            }
        }

        \Illuminate\Support\Facades\Log::warning("Barcode tidak ditemukan di semua path yang dicek");
        return null;
    }

    /**
     * Helper untuk mencari file gallery di beberapa kemungkinan lokasi
     */
    private function findGalleryFile($relativePath)
    {
        // Log untuk debugging
        \Illuminate\Support\Facades\Log::info("Mencari gallery dengan path relatif: " . $relativePath);

        // Ambil nama file dari path
        if (strpos($relativePath, '/') !== false) {
            $filename = basename($relativePath);
        } else {
            $filename = $relativePath; // Path relatif sudah berupa nama file
        }

        // Daftar kemungkinan path lengkap
        $possiblePaths = [
            // PRIORITAS 1: Path relatif lengkap
            storage_path('app/public/' . $relativePath),
            public_path('storage/' . $relativePath),

            // PRIORITAS 2: Lokasi di direktori komputers (sesuai dengan struktur aktual)
            storage_path('app/public/komputers/' . $filename),
            public_path('storage/komputers/' . $filename),

            // PRIORITAS 3: Path alternatif lain
            storage_path('app/public/gallery/' . $filename),
            public_path('storage/gallery/' . $filename),
            storage_path('app/public/komputer/' . $filename),
            public_path('storage/komputer/' . $filename),
            storage_path('app/public/images/' . $filename),
            public_path('storage/images/' . $filename),
            storage_path('app/' . $relativePath),
            public_path($relativePath),
            base_path('storage/app/public/' . $relativePath),
            base_path('public/storage/' . $relativePath),
        ];

        foreach ($possiblePaths as $path) {
            \Illuminate\Support\Facades\Log::info("Mencoba path gallery: " . $path);
            if (file_exists($path)) {
                \Illuminate\Support\Facades\Log::info("Gallery ditemukan di: " . $path);
                return $path;
            }
        }

        \Illuminate\Support\Facades\Log::warning("Gallery tidak ditemukan di semua path yang dicek");
        return null;
    }
}
