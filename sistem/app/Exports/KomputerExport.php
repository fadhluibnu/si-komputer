<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Support\Facades\Log;

class KomputerExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithDrawings, WithColumnWidths, WithEvents
{
    protected $komputers;
    protected $columns;
    protected $headerRow;

    public function __construct($komputers, $columns)
    {
        $this->komputers = $komputers;
        $this->columns = $columns;
        $this->headerRow = $this->getHeaderRow($columns);
    }

    public function collection()
    {
        return collect($this->komputers)->map(function ($komputer, $index) {
            return $this->formatDataRow($komputer, $this->columns, $index);
        });
    }

    public function headings(): array
    {
        return $this->headerRow;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function drawings()
    {
        $drawings = [];
        $hasBarcode = in_array('barcode', $this->columns);
        
        if ($hasBarcode) {
            // Tentukan posisi kolom barcode
            $barcodeColumnIndex = array_search('barcode', $this->columns);
            
            Log::info('Kolom barcode ditemukan pada indeks: ' . $barcodeColumnIndex);
            
            // Buat drawing untuk setiap komputer
            foreach ($this->komputers as $index => $komputer) {
                // Hanya tambahkan gambar jika komputer memiliki barcode
                if ($komputer->barcode) {
                    Log::info('Memproses barcode untuk komputer ID: ' . $komputer->id . ', kode barang: ' . $komputer->kode_barang . ', barcode: ' . $komputer->barcode);
                    
                    // Gunakan fungsi helper untuk mencari file barcode
                    $barcodeFile = $this->findBarcodeFile($komputer->barcode);
                    
                    if ($barcodeFile) {
                        Log::info('File barcode ditemukan di: ' . $barcodeFile);
                        
                        $drawing = new Drawing();
                        $drawing->setName('Barcode ' . $komputer->kode_barang);
                        $drawing->setDescription('Barcode ' . $komputer->kode_barang);
                        $drawing->setPath($barcodeFile);
                        // Set ukuran yang lebih kecil agar pas di dalam sel
                        $drawing->setHeight(45);
                        $drawing->setWidth(90);
                        
                        // Pastikan koordinat tepat untuk sel yang dituju
                        // barcodeColumnIndex sesuai dengan posisi barcode di array columns
                        $column = $this->getExcelColumn($barcodeColumnIndex);
                        $row = $index + 2; // +2 karena header berada di baris 1
                        $cellCoordinate = $column . $row;
                        
                        $drawing->setCoordinates($cellCoordinate);
                        // Sesuaikan offset untuk posisi dalam sel - posisi di tengah sel
                        $drawing->setOffsetX(10);
                        $drawing->setOffsetY(5);
                        // Atur tipe koordinat untuk barcode agar tetap dalam sel
                        $drawing->setResizeProportional(true);
                        
                        Log::info("Barcode drawing akan diletakkan pada sel: {$cellCoordinate}");
                        
                        $drawings[] = $drawing;
                    } else {
                        // Log bahwa file tidak ditemukan
                        Log::warning('File barcode tidak ditemukan untuk komputer ID: ' . $komputer->id . ' dengan path ' . $komputer->barcode);
                    }
                } else {
                    Log::info('Komputer ID ' . $komputer->id . ' tidak memiliki data barcode');
                }
            }
        }
        
        return $drawings;
    }
    
    /**
     * Konversi indeks numerik (0, 1, 2, ...) ke huruf kolom Excel (A, B, C, ...)
     */
    private function getExcelColumn($index)
    {
        $column = '';
        $index++;
        
        while ($index > 0) {
            $modulo = ($index - 1) % 26;
            $column = chr(65 + $modulo) . $column;
            $index = (int)(($index - $modulo) / 26);
        }
        
        return $column;
    }
    
    public function columnWidths(): array
    {
        // Menentukan lebar kolom untuk semua kolom yang perlu disesuaikan
        $widths = [];
        
        // Atur lebar untuk kolom umum yang mungkin terlalu sempit
        foreach ($this->columns as $index => $column) {
            $columnLetter = $this->getExcelColumn($index);
            
            // Atur lebar berdasarkan jenis kolom
            if ($column === 'nomor_urut') {
                // Kolom nomor cukup sempit
                $widths[$columnLetter] = 8;
            } else if ($column === 'barcode') {
                // Lebar khusus untuk kolom barcode agar gambar muat dengan baik
                $widths[$columnLetter] = 25;
            } elseif ($column === 'spesifikasi') {
                // Kolom spesifikasi biasanya berisi teks panjang
                $widths[$columnLetter] = 40;
            } elseif ($column === 'latest_maintenance_result') {
                // Kolom hasil maintenance juga bisa panjang
                $widths[$columnLetter] = 30;
            }
        }
        
        return $widths;
    }
    
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
            'pemeliharaan_terakhir' => 'Pemeliharaan Terakhir',
            'latest_maintenance_date' => 'Tanggal Pemeliharaan Terakhir',
            'latest_maintenance_type' => 'Jenis Pemeliharaan Terakhir',
            'latest_maintenance_technician' => 'Teknisi Pemeliharaan Terakhir',
            'latest_maintenance_result' => 'Hasil Pemeliharaan Terakhir',
            'latest_maintenance_cost' => 'Biaya Pemeliharaan Terakhir',
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

    private function formatDataRow($komputer, $columns, $index = null)
    {
        $row = [];
        
        // Get the latest maintenance record once for multiple columns
        $latestMaintenance = $komputer->maintenanceHistories()->latest()->first();
        
        foreach ($columns as $column) {
            switch ($column) {
                case 'nomor_urut':
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
                case 'pemeliharaan_terakhir':
                    if ($latestMaintenance) {
                        $row[] = "Tanggal: " . $latestMaintenance->created_at->format('d-m-Y') . 
                               ", Jenis: " . $latestMaintenance->jenis_maintenance . 
                               ", Hasil: " . $latestMaintenance->hasil_maintenance;
                    } else {
                        $row[] = 'Belum ada data pemeliharaan';
                    }
                    break;
                case 'latest_maintenance_date':
                    $row[] = $latestMaintenance ? $latestMaintenance->created_at->format('d-m-Y') : 'Belum ada';
                    break;
                case 'latest_maintenance_type':
                    $row[] = $latestMaintenance ? $latestMaintenance->jenis_maintenance : 'Belum ada';
                    break;
                case 'latest_maintenance_technician':
                    $row[] = $latestMaintenance ? $latestMaintenance->teknisi : 'Belum ada';
                    break;
                case 'latest_maintenance_result':
                    $row[] = $latestMaintenance ? $latestMaintenance->hasil_maintenance : 'Belum ada';
                    break;
                case 'latest_maintenance_cost':
                    $row[] = $latestMaintenance ? 'Rp ' . number_format($latestMaintenance->biaya_maintenance, 0, ',', '.') : 'Belum ada';
                    break;
                case 'barcode':
                    // Untuk kolom barcode, gunakan spasi kosong karena gambar akan ditempatkan dengan WithDrawings
                    if ($komputer->barcode) {
                        $row[] = ''; // Sel kosong karena gambar ditambahkan dengan WithDrawings
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
        Log::info("Mencari barcode dengan path relatif: " . $relativePath);
        
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
            Log::info("Mencoba path: " . $path);
            if (file_exists($path)) {
                Log::info("Barcode ditemukan di: " . $path);
                return $path;
            }
        }
        
        Log::warning("Barcode tidak ditemukan di semua path yang dicek");
        return null;
    }
    
    /**
     * Implements AfterSheet to apply additional formatting to barcode column
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\AfterSheet::class => function(\Maatwebsite\Excel\Events\AfterSheet $event) {
                if (in_array('barcode', $this->columns)) {
                    $barcodeIndex = array_search('barcode', $this->columns);
                    $barcodeColumn = $this->getExcelColumn($barcodeIndex);
                    
                    // Get the worksheet
                    $sheet = $event->sheet->getDelegate();
                    
                    // Set row height for all rows with barcode images to ensure they fit
                    for ($i = 2; $i <= count($this->komputers) + 1; $i++) {
                        // Atur tinggi baris yang lebih besar untuk mengakomodasi gambar barcode
                        $sheet->getRowDimension($i)->setRowHeight(70);
                    }
                    
                    // Set text alignment for barcode column
                    $lastRow = count($this->komputers) + 1;
                    $range = $barcodeColumn . '2:' . $barcodeColumn . $lastRow;
                    
                    $sheet->getStyle($range)->getAlignment()
                          ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                          ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}
