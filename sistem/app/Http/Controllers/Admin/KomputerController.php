<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Komputer;
use App\Service\Komputer\KomputerGetData;
use App\Service\Komputer\KomputerStore;
use App\Service\Komputer\KomputerUpdate;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Exports\KomputerExport;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Barryvdh\DomPDF\PDF;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as FacadesExcel;

class KomputerController extends Controller
{

    private $komputerGetData;
    private $komputerStore;
    private $komputerUpdate;

    public function __construct(KomputerGetData $komputerGetData, KomputerStore $komputerStore, KomputerUpdate $komputerUpdate)
    {
        $this->komputerGetData = $komputerGetData;
        $this->komputerStore = $komputerStore;
        $this->komputerUpdate = $komputerUpdate;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $komputers = $this->komputerGetData->getFilteredKomputers($request->all(), 10);

        $ruangan = $this->komputerGetData->getUniqueRuangan();

        return view(
            'admin.komputer.daftar',
            [
                'komputers' => $komputers,
                'ruangan' => $ruangan,
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Menampilkan form untuk menambahkan komputer baru
        return view('admin.komputer.tambah', [
            'ruangans' => $this->komputerGetData->getUniqueRuangan(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // validasi data
            $validated = $this->komputerStore->validateInput($request);
            
            // Generate UUID first
            $uuid = \Illuminate\Support\Str::uuid()->toString();
            $validated['uuid'] = $uuid;
            
            // Get ruangan name for barcode
            $ruangan = \App\Models\Ruangan::find($validated['ruangan_id']);
            $validated['ruangan_name'] = $ruangan ? $ruangan->nama_ruangan : 'Tidak ditentukan';
            
            // generate barcode using UUID and komputer data
            $barcode = $this->komputerStore->generateQRCode($uuid, $validated);
            $validated['barcode'] = $barcode;
            
            // Add user ID from the session or use a default value
            $validated['user_id'] = auth()->check() ? auth()->user()->id : 1;

            // simpan data komputer
            $komputer = $this->komputerStore->storeKomputer($validated);

            // simpan galeri foto
            $this->komputerStore->storeGallery($komputer, $request);

            DB::commit();

            return redirect()
                ->route('komputer.index')
                ->with('success', 'Data komputer berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Change withErrors to with to match the check in the view
            return redirect()
                ->back()
                ->with('error', 'Gagal menambahkan data komputer: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid)
    {
        return view('admin.komputer.detail', [
            'komputer' => $this->komputerGetData->getByUuid($uuid),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $uuid)
    {
        return view('admin.komputer.edit', [
            'komputer' => $this->komputerGetData->getByUuid($uuid),
            'ruangans' => $this->komputerGetData->getUniqueRuangan()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $uuid)
    {
        DB::beginTransaction();
        try {
            $validated = $this->komputerUpdate->validateInput($request, $uuid);

            $komputer = Komputer::where('uuid', $uuid)->firstOrFail();
            
            // Generate barcode with UUID - for updates, we use the existing data
            $barcode = $this->komputerStore->generateQRCode($uuid, $validated);
            $validated['barcode'] = $barcode;

            $komputer = $this->komputerUpdate->updateKomputer($komputer, $validated);

            $this->komputerUpdate->handleGallery($komputer, $request);

            DB::commit();

            return redirect()
                ->route('komputer.index')
                ->with('success', 'Data komputer berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();

            // Change withErrors to with to match the check in the view
            return redirect()
                ->back()
                ->with('error', 'Gagal memperbarui data komputer: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        DB::beginTransaction();
        try {
            $komputer = Komputer::where('uuid', $uuid)->firstOrFail();

            // Delete all gallery images associated with this computer
            foreach ($komputer->galleries as $gallery) {
                if (Storage::disk('public')->exists($gallery->image_path)) {
                    Storage::disk('public')->delete($gallery->image_path);
                }
                $gallery->delete();
            }

            // Delete the computer record
            $komputer->delete();

            // Clear caches
            Cache::forget('ruangan_list');

            DB::commit();

            return redirect()
                ->route('komputer.index')
                ->with('success', 'Data komputer berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus data komputer: ' . $e->getMessage());
        }
    }

    /**
     * Export data to specified format
     */
    public function export(Request $request)
    {
        // Set explicit default columns including maintenance history
        $defaultColumns = [
            'nomor_urut',
            'kode_barang', 
            'nama_komputer', 
            'ruangan', 
            'nama_pengguna', 
            'spesifikasi', 
            'kondisi', 
            'penggunaan',
            'tanggal_pengadaan',
            'latest_maintenance_date',
            'latest_maintenance_type',
            'latest_maintenance_technician',
            'latest_maintenance_result',
            'latest_maintenance_cost',
            'barcode'
        ];
        
        // Use default columns if none are provided in the request
        $format = $request->input('format', 'excel');
        $columns = $request->has('columns') ? $request->input('columns') : $defaultColumns;
        
        // Ensure proper eager loading of relationships with constraints
        $query = Komputer::with([
            'ruangan',
            'maintenanceHistories' => function($query) {
                $query->orderBy('created_at', 'desc')->limit(1);
            }
        ]);
        
        // Apply filters
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function($q) use ($keyword) {
                $q->where('kode_barang', 'like', "%{$keyword}%")
                  ->orWhere('nama_komputer', 'like', "%{$keyword}%")
                  ->orWhere('nama_pengguna_sekarang', 'like', "%{$keyword}%");
            });
        }
        
        if ($request->filled('ruangan')) {
            $query->where('ruangan_id', $request->input('ruangan'));
        }
        
        if ($request->filled('kondisi')) {
            $query->where('kondisi_komputer', $request->input('kondisi'));
        }
        
        // Get data
        $komputers = $query->get();
        
        // Format timestamp for filename
        $timestamp = now()->format('Ymd_His');
        $filename = "data_komputer_{$timestamp}";
        
        // Create export based on requested format
        switch ($format) {
            case 'csv':
                return $this->exportToCSV($komputers, $columns, $filename);
            case 'pdf':
                return $this->exportToPDF($komputers, $columns, $filename);
            case 'excel':
            default:
                return $this->exportToExcel($komputers, $columns, $filename);
        }
    }

    /**
     * Helper method to export to Excel
     */
    private function exportToExcel($komputers, $columns, $filename)
    {
        // This requires the Laravel Excel package
        // composer require maatwebsite/excel
        
        return FacadesExcel::download(new KomputerExport($komputers, $columns), "{$filename}.xlsx");
    }

    /**
     * Helper method to export to CSV
     */
    private function exportToCSV($komputers, $columns, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];
        
        $callback = function() use ($komputers, $columns) {
            $file = fopen('php://output', 'w');
            
            // Add header row
            $headerRow = $this->getHeaderRow($columns);
            fputcsv($file, $headerRow);
            
            // Add data rows
            foreach ($komputers as $index => $komputer) {
                $row = $this->formatDataRow($komputer, $columns, 'csv', $index);
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper method to export to PDF
     */
    private function exportToPDF($komputers, $columns, $filename)
    {
        // This requires a PDF package like dompdf
        // composer require barryvdh/laravel-dompdf
        
        $headerRow = $this->getHeaderRow($columns);
        $data = [];
        
        // Pre-process barcode images for PDF generation
        $barcodeImages = [];
        
        foreach ($komputers as $index => $komputer) {
            // Format the data row for this komputer
            $row = $this->formatDataRow($komputer, $columns, 'pdf', $index);
            $data[] = $row;
            
            // Handle barcode images separately
            if ($komputer->barcode && in_array('barcode', $columns)) {
                // Use the consistent findBarcodeFile helper for path resolution
                $barcodePath = $this->findBarcodeFile($komputer->barcode);
                
                if ($barcodePath && file_exists($barcodePath)) {
                    // If found, store base64 encoded image data
                    $type = pathinfo($barcodePath, PATHINFO_EXTENSION);
                    $imageData = file_get_contents($barcodePath);
                    if ($imageData !== false) {
                        $barcodeImages[$index] = 'data:image/' . $type . ';base64,' . base64_encode($imageData);
                    } else {
                        $barcodeImages[$index] = null; // Error reading file
                        \Illuminate\Support\Facades\Log::error("Tidak dapat membaca file barcode: " . $barcodePath);
                    }
                } else {
                    $barcodeImages[$index] = null; // No image found
                    \Illuminate\Support\Facades\Log::warning("File barcode tidak ditemukan untuk komputer ID: " . $komputer->id . " dengan path " . $komputer->barcode);
                }
            }
        }
        
        // Create PDF with correct options for Laravel-DomPDF v3.1
        // First create the PDF instance
        $pdf = FacadePdf::loadView('admin.komputer.export_pdf', [
            'headerRow' => $headerRow,
            'data' => $data,
            'title' => 'Data Komputer ESDM',
            'komputers' => $komputers,
            'barcodeImages' => $barcodeImages // Pass pre-processed barcode images
        ]);
        
        // Then configure the dompdf options
        $dompdf = $pdf->getDomPDF();
        $options = $dompdf->getOptions();
        $options->setIsRemoteEnabled(true);
        $options->setIsHtml5ParserEnabled(true);
        
        // Set paper orientation
        $pdf->setPaper('a4', 'landscape');
        
        // Log PDF export information
        $this->logPdfExportInfo('Mengekspor data komputer ke PDF', [
            'jumlah_komputers' => count($komputers),
            'filename' => "{$filename}.pdf",
        ]);
        
        return $pdf->download("{$filename}.pdf");
    }

    /**
     * Helper method for logging PDF export process
     */
    private function logPdfExportInfo($message, $data = [])
    {
        \Illuminate\Support\Facades\Log::channel('daily')->info('PDF Export: ' . $message, $data);
    }

    /**
     * Helper to get header row based on selected columns
     */
    private function getHeaderRow($columns)
    {
        $headerMap = [
            'nomor_urut' => 'No',
            'kode_barang' => 'Kode Barang',
            'nama_komputer' => 'Nama Komputer',
            'ruangan' => 'Ruangan',
            'nama_pengguna' => 'Pengguna',
            'spesifikasi' => 'Spesifikasi',
            'kondisi' => 'Kondisi',
            'penggunaan' => 'Penggunaan',
            'tanggal_pengadaan' => 'Tanggal Pengadaan',
            'latest_maintenance_date' => 'Tanggal Maintenance Terakhir',
            'latest_maintenance_type' => 'Jenis Maintenance Terakhir',
            'latest_maintenance_technician' => 'Teknisi Terakhir',
            'latest_maintenance_result' => 'Hasil Maintenance Terakhir',
            'latest_maintenance_cost' => 'Biaya Maintenance Terakhir',
            'barcode' => 'Barcode'
        ];
        
        $headers = [];
        foreach ($columns as $column) {
            if (isset($headerMap[$column])) {
                $headers[] = $headerMap[$column];
            }
        }
        
        return $headers;
    }

    /**
     * Helper to format data row based on selected columns
     * @param object $komputer The komputer object
     * @param array $columns The columns to include
     * @param string $format The export format (excel, pdf, csv)
     * @return array
     */
    private function formatDataRow($komputer, $columns, $format = 'excel', $index = null)
    {
        $row = [];
        
        // Get the latest maintenance record if it exists
        $latestMaintenance = $komputer->maintenanceHistories->first();
        
        foreach ($columns as $column) {
            switch ($column) {
                case 'nomor_urut':
                    // If index is provided, use it for row number
                    // Otherwise just use 0 as placeholder (will be replaced later)
                    $row[] = isset($index) ? ($index + 1) : '—'; 
                    break;
                case 'kode_barang':
                    $row[] = $komputer->kode_barang;
                    break;
                case 'nama_komputer':
                    $row[] = $komputer->nama_komputer;
                    break;
                case 'ruangan':
                    $row[] = $komputer->ruangan ? $komputer->ruangan->nama_ruangan : 'Tidak tersedia';
                    break;
                case 'nama_pengguna':
                    $row[] = $komputer->nama_pengguna_sekarang;
                    break;
                case 'spesifikasi':
                    $row[] = "Processor: {$komputer->spesifikasi_processor}, RAM: {$komputer->spesifikasi_ram}, Storage: {$komputer->spesifikasi_penyimpanan}";
                    break;
                case 'kondisi':
                    $row[] = $komputer->kondisi_komputer;
                    break;
                case 'penggunaan':
                    $row[] = $komputer->penggunaan_sekarang;
                    break;
                case 'tanggal_pengadaan':
                    $row[] = $komputer->tahun_pengadaan;
                    break;
                case 'latest_maintenance_date':
                    $row[] = $latestMaintenance ? $latestMaintenance->created_at->format('d M Y') : 'Belum pernah';
                    break;
                case 'latest_maintenance_type':
                    $row[] = $latestMaintenance ? $latestMaintenance->jenis_maintenance : 'Belum pernah';
                    break;
                case 'latest_maintenance_technician':
                    $row[] = $latestMaintenance ? $latestMaintenance->teknisi : 'Belum pernah';
                    break;
                case 'latest_maintenance_result':
                    $row[] = $latestMaintenance ? $latestMaintenance->hasil_maintenance : 'Belum pernah';
                    break;
                case 'latest_maintenance_cost':
                    $row[] = $latestMaintenance ? 
                        ($latestMaintenance->biaya_maintenance ? $latestMaintenance->biaya_maintenance : 'Tidak ada biaya') : 
                        'Belum pernah';
                    break;
                case 'barcode':
                    // Handle barcode differently for each format
                    if ($komputer->barcode) {
                        if ($format === 'csv') {
                            // Untuk CSV hanya tampilkan nama file tanpa path
                            $row[] = 'Barcode: ' . basename($komputer->barcode);
                        } else if ($format === 'pdf') {
                            // Untuk PDF, path lengkap akan diproses di view
                            $row[] = $komputer->barcode;
                        } else {
                            // Untuk Excel dan format lain
                            $row[] = basename($komputer->barcode);
                        }
                    } else {
                        $row[] = 'Tidak ada barcode';
                    }
                    break;
            }
        }
        
        return $row;
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
     * Show computer details from barcode scan
     * This is a public route that doesn't require authentication
     * 
     * @param string $uuid
     * @return \Illuminate\View\View
     */
    public function scan(string $uuid)
    {
        // Get computer data with related models (galleries, riwayat_perbaikan, ruangan)
        $komputer = Komputer::where('uuid', $uuid)
            ->with(['galleries', 'ruangan', 'riwayatPerbaikan' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->firstOrFail(); // This will automatically throw 404 if not found
        
        // Generate QR code text content for display
        $qrTextContent = "Cek Online: " . config('app.url') . "/scan/{$uuid}\n\n";
        
        // Detail Komputer
        $qrTextContent .= "DETAIL KOMPUTER\n";
        $qrTextContent .= "Nama Komputer: {$komputer->nama_komputer}\n";
        $qrTextContent .= "Kode Aset: {$komputer->kode_barang}\n";
        $qrTextContent .= "Merek: {$komputer->merek_komputer}\n";
        $qrTextContent .= "Pengguna: " . ($komputer->nama_pengguna_sekarang ?? 'Tidak ditentukan') . "\n";
        $qrTextContent .= "Ruangan: {$komputer->ruangan->nama_ruangan}\n\n";
        
        // Kondisi Komputer
        $qrTextContent .= "KONDISI KOMPUTER\n";
        $qrTextContent .= "Kondisi: {$komputer->kondisi_komputer}\n";
        $qrTextContent .= "Keterangan: {$komputer->keterangan_kondisi}\n";
        $qrTextContent .= "Processor: {$komputer->spesifikasi_processor}\n";
        $qrTextContent .= "RAM: {$komputer->spesifikasi_ram}\n";
        $qrTextContent .= "Storage: {$komputer->spesifikasi_penyimpanan}\n\n";
        
        // Riwayat Pemeliharaan
        $qrTextContent .= "RIWAYAT PEMELIHARAAN\n";
        if ($komputer->riwayatPerbaikan->count() > 0) {
            foreach ($komputer->riwayatPerbaikan->take(2) as $index => $riwayat) {
                $qrTextContent .= "# Pemeliharaan " . ($index + 1) . "\n";
                $qrTextContent .= "Tanggal: " . $riwayat->created_at->format('d/m/Y') . "\n";
                $qrTextContent .= "Jenis: {$riwayat->jenis_maintenance}\n";
                $qrTextContent .= "Teknisi: {$riwayat->teknisi}\n";
                $qrTextContent .= "Hasil: {$riwayat->hasil_maintenance}\n";
                $qrTextContent .= "\n";
            }
        } else {
            $qrTextContent .= "Belum ada riwayat pemeliharaan\n";
        }
        
        return view('admin.komputer.scan', [
            'komputer' => $komputer,
            'qrTextContent' => $qrTextContent,
        ]);
    }

    /**
     * Regenerate QR code for a komputer.
     */
    public function regenerateQrCode(Request $request, $uuid)
    {
        $komputer = \App\Models\Komputer::where('uuid', $uuid)->firstOrFail();
        // Regenerate QR code with latest data
        $barcode = $this->komputerStore->generateQRCode($uuid);
        $komputer->barcode = $barcode;
        $komputer->save();
        return back()->with('success', 'QR code berhasil digenerate ulang.');
    }

    public function scanQR()
    {
        return view('scan-barcode');
    }
}
