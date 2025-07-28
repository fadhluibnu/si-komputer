<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Log;

class RiwayatPerbaikanExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithDrawings, WithEvents, WithTitle, WithCustomStartCell
{
    protected $komputer;
    protected $riwayatPerbaikan;
    protected $columns;
    protected $headerRow;
    protected $startRow;

    public function __construct($komputer, $riwayatPerbaikan, $columns)
    {
        $this->komputer = $komputer;
        $this->riwayatPerbaikan = $riwayatPerbaikan;
        $this->columns = $columns;
        $this->headerRow = $this->getHeaderRow($columns);
        // Menentukan baris awal tabel riwayat
        $this->startRow = $this->komputer->galleries->isNotEmpty() ? 12 : 10;
    }

    public function startCell(): string
    {
        return 'A' . $this->startRow;
    }

    public function title(): string
    {
        return 'Riwayat Pemeliharaan';
    }

    public function collection()
    {
        return collect($this->riwayatPerbaikan)->map(function ($riwayat, $index) {
            return $this->formatDataRow($riwayat, $this->columns, $index);
        });
    }

    public function headings(): array
    {
        return $this->headerRow;
    }

    public function styles(Worksheet $sheet)
    {
        // Header Utama
        $sheet->mergeCells('A1:H1')->setCellValue('A1', 'LAPORAN RIWAYAT PERBAIKAN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Detail Komputer
        $details = [
            'A3' => 'Kode Barang',
            'B3' => $this->komputer->kode_barang,
            'A4' => 'Nama Komputer',
            'B4' => $this->komputer->nama_komputer,
            'A5' => 'Ruangan',
            'B5' => $this->komputer->ruangan->nama_ruangan ?? 'N/A',
            'A6' => 'Pengguna',
            'B6' => $this->komputer->nama_pengguna_sekarang,
            'A7' => 'Kondisi',
            'B7' => $this->komputer->kondisi_komputer,
        ];
        foreach ($details as $cell => $value) $sheet->setCellValue($cell, $value);
        $sheet->getStyle('A3:A7')->getFont()->setBold(true);

        // Header Galeri & Riwayat
        if ($this->komputer->galleries->isNotEmpty()) {
            $sheet->mergeCells('A9:H9')->setCellValue('A9', 'FOTO KOMPUTER');
            $sheet->getStyle('A9')->getFont()->setBold(true)->setSize(12);
        }
        $sheet->mergeCells('A' . ($this->startRow - 1) . ':H' . ($this->startRow - 1))
            ->setCellValue('A' . ($this->startRow - 1), 'RIWAYAT PEMELIHARAAN');
        $sheet->getStyle('A' . ($this->startRow - 1))->getFont()->setBold(true)->setSize(12);

        return [$this->startRow => ['font' => ['bold' => true]]];
    }

    public function drawings()
    {
        $drawings = [];
        $i = 0;
        $galleryColumns = ['A', 'B', 'C', 'D', 'E', 'F'];

        // 1. Barcode
        if ($this->komputer->barcode) {
            $barcodePath = $this->findFile($this->komputer->barcode, 'barcode');
            if ($barcodePath) {
                $drawing = new Drawing();
                $drawing->setPath($barcodePath)->setHeight(60)->setCoordinates('E3');
                $drawings[] = $drawing;
            }
        }

        // 2. Galeri (hanya untuk gambar)
        foreach ($this->komputer->galleries as $gallery) {
            if ($i >= 6) break; // Batasi 6 file
            $filePath = $this->findFile($gallery->image_path, 'komputers');
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if ($filePath && in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $drawing = new Drawing();
                $drawing->setName('Foto ' . ($i + 1));
                $drawing->setPath($filePath);
                $drawing->setHeight(75); // Tinggi gambar
                $drawing->setCoordinates($galleryColumns[$i] . '10'); // Semua di baris 10
                $drawings[] = $drawing;
                $i++;
            }
        }
        return $drawings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Menambahkan teks untuk file PDF di galeri
                $i = 0;
                $galleryColumns = ['A', 'B', 'C', 'D', 'E', 'F'];
                foreach ($this->komputer->galleries as $gallery) {
                    if ($i >= 6) break;
                    $filePath = $this->findFile($gallery->image_path, 'komputers');
                    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    if ($extension === 'pdf') {
                        $cell = $galleryColumns[$i] . '10';
                        $sheet->setCellValue($cell, "File PDF:\n" . basename($filePath));
                        // Membuat hyperlink ke file
                        $sheet->getCell($cell)->getHyperlink()->setUrl(asset('storage/' . $gallery->image_path));
                        $sheet->getStyle($cell)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE));
                        $sheet->getStyle($cell)->getAlignment()->setWrapText(true);
                    }
                    $i++;
                }

                // Atur tinggi baris
                $sheet->getRowDimension(3)->setRowHeight(60); // Baris barcode
                if ($this->komputer->galleries->isNotEmpty()) {
                    $sheet->getRowDimension(10)->setRowHeight(80); // Baris galeri
                }
            },
        ];
    }

    // Helper methods (columnWidths, findFile, getExcelColumn, getHeaderRow, formatDataRow)
    // Sebaiknya sama dengan yang ada di KomputerExport atau dipindahkan ke Trait
    public function columnWidths(): array
    {
        return ['A' => 15, 'B' => 15, 'C' => 15, 'D' => 25, 'E' => 15, 'F' => 15];
    }
    private function findFile($relativePath, $subfolder = '')
    {
        $filename = basename($relativePath);
        $possiblePaths = [
            storage_path('app/public/' . $relativePath),
            storage_path('app/public/' . $subfolder . '/' . $filename),
        ];
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) return $path;
        }
        return null;
    }
    private function getExcelColumn($index)
    {
        $c = '';
        $i = $index + 1;
        while ($i > 0) {
            $m = ($i - 1) % 26;
            $c = chr(65 + $m) . $c;
            $i = (int)(($i - $m) / 26);
        }
        return $c;
    }
    private function getHeaderRow($columns)
    {
        $map = ['nomor_urut' => 'No', 'jenis_maintenance' => 'Jenis', 'tanggal' => 'Tanggal', 'keterangan' => 'Keterangan', 'biaya_maintenance' => 'Biaya'];
        return array_map(fn($col) => $map[$col] ?? ucfirst($col), $columns);
    }
    private function formatDataRow($riwayat, $columns, $index = 0)
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
                case 'jenis_maintenance':
                    $row[] = $riwayat->jenis_maintenance;
                    break;
                case 'keterangan':
                    $row[] = $riwayat->keterangan;
                    break;
                case 'biaya_maintenance':
                    $row[] = $riwayat->biaya_maintenance ? 'Rp ' . number_format($riwayat->biaya_maintenance, 0, ',', '.') : 'N/A';
                    break;
                default:
                    $row[] = $riwayat->$column ?? '';
                    break;
            }
        }
        return $row;
    }
}
// class RiwayatPerbaikanExport implements
//     FromCollection,
//     WithHeadings,
//     ShouldAutoSize,
//     WithStyles,
//     WithDrawings,
//     WithColumnWidths,
//     WithEvents,
//     WithTitle,
//     WithCustomStartCell
// {
//     protected $komputer;
//     protected $riwayatPerbaikan;
//     protected $columns;
//     protected $headerRow;

//     public function __construct($komputer, $riwayatPerbaikan, $columns)
//     {
//         $this->komputer = $komputer;
//         $this->riwayatPerbaikan = $riwayatPerbaikan;
//         $this->columns = $columns;
//         $this->headerRow = $this->getHeaderRow($columns);
//     }

//     /**
//      * Start at row 10 to leave space for computer details and gallery
//      */
//     public function startCell(): string
//     {
//         // Jika ada gallery, kita perlu menyesuaikan posisi mulai untuk tabel riwayat
//         // Gallery akan ditampilkan di baris 8
//         return $this->komputer->galleries && $this->komputer->galleries->count() > 0 ? 'A12' : 'A10';
//     }

//     /**
//      * Set sheet title
//      */
//     public function title(): string
//     {
//         return 'Riwayat Pemeliharaan';
//     }

//     /**
//      * Return maintenance history data
//      */
//     public function collection()
//     {
//         return collect($this->riwayatPerbaikan)->map(function ($riwayat, $index) {
//             return $this->formatDataRow($riwayat, $this->columns, $index);
//         });
//     }

//     /**
//      * Return headings for maintenance history
//      */
//     public function headings(): array
//     {
//         return $this->headerRow;
//     }

//     /**
//      * Style the worksheet
//      */    public function styles(Worksheet $sheet)
//     {
//         // Add title at the top
//         $sheet->mergeCells('A1:H1');
//         $sheet->setCellValue('A1', 'DATA KOMPUTER');
//         $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
//         $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
//         // Add computer details (tata letak seperti pada gambar)
//         $sheet->setCellValue('A3', 'Kode Barang');
//         $sheet->setCellValue('B3', $this->komputer->kode_barang);
//         $sheet->setCellValue('A4', 'Nama Komputer');
//         $sheet->setCellValue('B4', $this->komputer->nama_komputer);
//         $sheet->setCellValue('A5', 'Ruangan');
//         $sheet->setCellValue('B5', $this->komputer->ruangan ? $this->komputer->ruangan->nama_ruangan : 'Tidak tersedia');
//         $sheet->setCellValue('A6', 'Pengguna');
//         $sheet->setCellValue('B6', $this->komputer->nama_pengguna_sekarang);
//         $sheet->setCellValue('A7', 'Kondisi');
//         $sheet->setCellValue('B7', $this->komputer->kondisi_komputer);
        
//         // Atur border untuk detail komputer
//         $sheet->getStyle('A3:B7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
//         // Add gallery section if applicable
//         $startRow = 12;
//         if ($this->komputer->galleries && $this->komputer->galleries->count() > 0) {
//             // Add title for gallery seperti pada gambar
//             $sheet->mergeCells('A9:D9');
//             $sheet->setCellValue('A9', 'FOTO KOMPUTER');
//             $sheet->getStyle('A9')->getFont()->setBold(true)->setSize(12);
//             $sheet->getStyle('A9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
//             // Adjust start row for maintenance history
//             $startRow = 12;
//         }

//         // Add title for maintenance history
//         $sheet->mergeCells('A' . ($startRow - 1) . ':H' . ($startRow - 1));
//         $sheet->setCellValue('A' . ($startRow - 1), 'RIWAYAT PEMELIHARAAN');
//         $sheet->getStyle('A' . ($startRow - 1))->getFont()->setBold(true)->setSize(12);
//         $sheet->getStyle('A' . ($startRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

//         // Style the headings and details
//         $sheet->getStyle('A3:A7')->getFont()->setBold(true);
//         $sheet->getStyle('A' . $startRow . ':' . $this->getLastColumn() . $startRow)->getFont()->setBold(true);

//         // Set border for computer details
//         $sheet->getStyle('A3:B7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

//         // Style cells for maintenance history data
//         $lastRow = $startRow + count($this->riwayatPerbaikan);
//         if (count($this->riwayatPerbaikan) > 0) {
//             $sheet->getStyle('A' . $startRow . ':' . $this->getLastColumn() . $lastRow)
//                 ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
//         }

//         return [
//             $startRow => ['font' => ['bold' => true]],
//         ];
//     }

//     /**
//      * Add barcode and gallery images to the excel sheet
//      */
//     public function drawings()
//     {
//         $drawings = [];

//         // Add barcode image if it exists
//         if ($this->komputer->barcode) {
//             $barcodePath = $this->findBarcodeFile($this->komputer->barcode);

//             if ($barcodePath) {
//                 Log::info('File barcode ditemukan di: ' . $barcodePath);

//                 $drawing = new Drawing();
//                 $drawing->setName('Barcode ' . $this->komputer->kode_barang);
//                 $drawing->setDescription('Barcode ' . $this->komputer->kode_barang);
//                 $drawing->setPath($barcodePath);
//                 $drawing->setHeight(60);
//                 $drawing->setWidth(120);
//                 $drawing->setCoordinates('E3'); // Posisi di kolom E, baris 3 - sesuai gambar
//                 $drawing->setOffsetX(10);
//                 $drawing->setOffsetY(5);

//                 $drawings[] = $drawing;
//             }
//         }        // Add gallery images if they exist
//         if ($this->komputer->galleries && $this->komputer->galleries->count() > 0) {
//             // Menata foto gallery seperti pada gambar contoh (cell-cell bersebelahan)
//             $columns = ['A', 'B', 'C'];
//             $rows = [10, 10, 10]; // Baris pertama
//             $i = 0;
            
//             foreach ($this->komputer->galleries as $gallery) {
//                 if ($i >= 6) break; // Limit to 6 images (2 baris, 3 kolom)
                
//                 $imagePath = $this->findGalleryFile($gallery->image_path);
                
//                 if ($imagePath && file_exists($imagePath)) {
//                     Log::info('File gallery ditemukan di: ' . $imagePath);
                    
//                     $colIndex = $i % 3; // 0, 1, 2 untuk kolom A, B, C
//                     $rowIndex = (int)($i / 3); // 0 untuk baris pertama, 1 untuk baris kedua
//                     $currentRow = $rows[0] + ($rowIndex * 1); // Jarak antar baris
                    
//                     $drawing = new Drawing();
//                     $drawing->setName('Foto Komputer ' . ($i + 1));
//                     $drawing->setDescription('Foto Komputer ' . ($i + 1));
//                     $drawing->setPath($imagePath);
//                     $drawing->setHeight(60);
//                     $drawing->setWidth(80);
//                     $drawing->setCoordinates($columns[$colIndex] . $currentRow); // Cell position
//                     $drawing->setOffsetX(5);
//                     $drawing->setOffsetY(5);
                    
//                     $drawings[] = $drawing;
//                     $i++;
//                 }
//             }
//         }

//         return $drawings;
//     }

//     /**
//      * Set column widths
//      */
//     public function columnWidths(): array
//     {
//         // Determine column widths based on content
//         $widths = [];

//         // Set specific widths for certain columns
//         foreach ($this->columns as $index => $column) {
//             $columnLetter = $this->getExcelColumn($index);

//             if ($column === 'nomor_urut') {
//                 $widths[$columnLetter] = 8;
//             } elseif ($column === 'keterangan' || $column === 'hasil_maintenance' || $column === 'rekomendasi') {
//                 $widths[$columnLetter] = 35;
//             } elseif ($column === 'komponen_diganti') {
//                 $widths[$columnLetter] = 25;
//             } elseif ($column === 'biaya_maintenance') {
//                 $widths[$columnLetter] = 15;
//             } elseif ($column === 'teknisi') {
//                 $widths[$columnLetter] = 15;
//             } elseif ($column === 'jenis_maintenance') {
//                 $widths[$columnLetter] = 18;
//             }
//         }

//         // Pengaturan lebar kolom untuk tata letak yang optimal seperti pada gambar
//         $widths['A'] = 16; // Kolom kode barang
//         $widths['B'] = 30; // Nilai-nilai kolom kanan

//         return $widths;
//     }

//     /**
//      * Register sheet events
//      */
//     public function registerEvents(): array
//     {
//         return [
//             AfterSheet::class => function (AfterSheet $event) {
//                 // Get the sheet
//                 $sheet = $event->sheet->getDelegate();

//                 // Set height for barcode row
//                 $sheet->getRowDimension(3)->setRowHeight(60);

//                 // Set height for gallery rows if applicable
//                 $startRow = 12;
//                 if ($this->komputer->galleries && $this->komputer->galleries->count() > 0) {
//                     // Pengaturan tinggi baris untuk gambar gallery (muat 2 baris x 3 kolom)
//                     $sheet->getRowDimension(10)->setRowHeight(70);
//                     $sheet->getRowDimension(11)->setRowHeight(70);
                    
//                     // Atur lebar kolom untuk gallery
//                     $sheet->getColumnDimension('A')->setWidth(15);
//                     $sheet->getColumnDimension('B')->setWidth(15);
//                     $sheet->getColumnDimension('C')->setWidth(15);
                    
//                     // Atur lebar kolom barcode
//                     $sheet->getColumnDimension('E')->setWidth(18);
//                     $sheet->getColumnDimension('F')->setWidth(18);
//                 }

//                 // Wrap text in all data cells
//                 $lastRow = $startRow + count($this->riwayatPerbaikan);
//                 if (count($this->riwayatPerbaikan) > 0) {
//                     $sheet->getStyle('A' . $startRow . ':' . $this->getLastColumn() . $lastRow)
//                         ->getAlignment()->setWrapText(true);
//                 }
                
//                 // Menyesuaikan styling komputer detail seperti pada gambar
//                 $sheet->getStyle('A3:B7')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
//                 $sheet->getStyle('A3:A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
//             },
//         ];
//     }

//     /**
//      * Helper method to find the barcode file
//      */
//     private function findBarcodeFile($relativePath)
//     {
//         // Log untuk debugging
//         Log::info("Mencari barcode dengan path relatif: " . $relativePath);

//         // Ambil nama file dari path
//         if (strpos($relativePath, '/') !== false) {
//             $filename = basename($relativePath);
//         } else {
//             $filename = $relativePath; // Path relatif sudah berupa nama file
//         }

//         // Daftar kemungkinan path lengkap, dengan prioritas pada lokasi yang Anda sebutkan
//         $possiblePaths = [
//             // PRIORITAS 1: Path relatif lengkap
//             storage_path('app/public/' . $relativePath),
//             public_path('storage/' . $relativePath),

//             // PRIORITAS 2: Lokasi di direktori barcode
//             storage_path('app/public/barcode/' . $filename),
//             public_path('storage/barcode/' . $filename),

//             // PRIORITAS 3: Path alternatif lain
//             storage_path('app/public/barcode/' . $filename),
//             public_path('storage/public/barcode/' . $filename),
//             storage_path('app/' . $relativePath),
//             public_path($relativePath),
//             base_path('storage/app/public/' . $relativePath),
//             base_path('public/storage/' . $relativePath),
//         ];

//         foreach ($possiblePaths as $path) {
//             Log::info("Mencoba path: " . $path);
//             if (file_exists($path)) {
//                 Log::info("Barcode ditemukan di: " . $path);
//                 return $path;
//             }
//         }

//         Log::warning("Barcode tidak ditemukan di semua path yang dicek");
//         return null;
//     }

//     /**
//      * Helper method to find the gallery file
//      */
//     private function findGalleryFile($relativePath)
//     {
//         // Log untuk debugging
//         Log::info("Mencari gallery dengan path relatif: " . $relativePath);

//         // Ambil nama file dari path
//         if (strpos($relativePath, '/') !== false) {
//             $filename = basename($relativePath);
//         } else {
//             $filename = $relativePath; // Path relatif sudah berupa nama file
//         }

//         // Daftar kemungkinan path lengkap
//         $possiblePaths = [
//             // PRIORITAS 1: Path relatif lengkap
//             storage_path('app/public/' . $relativePath),
//             public_path('storage/' . $relativePath),

//             // PRIORITAS 2: Lokasi di direktori komputers (sesuai dengan struktur aktual)
//             storage_path('app/public/komputers/' . $filename),
//             public_path('storage/komputers/' . $filename),

//             // PRIORITAS 3: Path alternatif lain
//             storage_path('app/public/gallery/' . $filename),
//             public_path('storage/gallery/' . $filename),
//             storage_path('app/public/komputer/' . $filename),
//             public_path('storage/komputer/' . $filename),
//             storage_path('app/public/images/' . $filename),
//             public_path('storage/images/' . $filename),
//             storage_path('app/' . $relativePath),
//             public_path($relativePath),
//             base_path('storage/app/public/' . $relativePath),
//             base_path('public/storage/' . $relativePath),
//         ];

//         foreach ($possiblePaths as $path) {
//             Log::info("Mencoba path gallery: " . $path);
//             if (file_exists($path)) {
//                 Log::info("Gallery ditemukan di: " . $path);
//                 return $path;
//             }
//         }

//         Log::warning("Gallery tidak ditemukan di semua path yang dicek");
//         return null;
//     }

//     /**
//      * Get last column letter based on columns count
//      */
//     private function getLastColumn()
//     {
//         return $this->getExcelColumn(count($this->columns) - 1);
//     }

//     /**
//      * Convert numeric index to Excel column letter
//      */
//     private function getExcelColumn($index)
//     {
//         $column = '';
//         $index++;

//         while ($index > 0) {
//             $modulo = ($index - 1) % 26;
//             $column = chr(65 + $modulo) . $column;
//             $index = (int)(($index - $modulo) / 26);
//         }

//         return $column;
//     }

//     /**
//      * Get header row mapping
//      */
//     private function getHeaderRow($columns)
//     {
//         $headerMap = [
//             'nomor_urut' => 'No',
//             'jenis_maintenance' => 'Jenis Pemeliharaan',
//             'tanggal' => 'Tanggal',
//             'teknisi' => 'Teknisi',
//             'keterangan' => 'Keterangan',
//             'komponen_diganti' => 'Komponen Diganti',
//             'biaya_maintenance' => 'Biaya',
//             'hasil_maintenance' => 'Hasil',
//             'rekomendasi' => 'Rekomendasi'
//         ];

//         $headers = [];
//         foreach ($columns as $column) {
//             if (isset($headerMap[$column])) {
//                 $headers[] = $headerMap[$column];
//             }
//         }

//         return $headers;
//     }

//     /**
//      * Format data row for export
//      */
//     private function formatDataRow($riwayat, $columns, $index = null)
//     {
//         $row = [];

//         foreach ($columns as $column) {
//             switch ($column) {
//                 case 'nomor_urut':
//                     $row[] = isset($index) ? ($index + 1) : '—';
//                     break;
//                 case 'jenis_maintenance':
//                     $row[] = $riwayat->jenis_maintenance;
//                     break;
//                 case 'tanggal':
//                     $row[] = $riwayat->created_at->format('d M Y');
//                     break;
//                 case 'teknisi':
//                     $row[] = $riwayat->teknisi;
//                     break;
//                 case 'keterangan':
//                     $row[] = $riwayat->keterangan;
//                     break;
//                 case 'komponen_diganti':
//                     $row[] = $riwayat->komponen_diganti ?: 'Tidak ada';
//                     break;
//                 case 'biaya_maintenance':
//                     $row[] = $riwayat->biaya_maintenance ? 'Rp ' . number_format($riwayat->biaya_maintenance, 0, ',', '.') : 'Tidak ada biaya';
//                     break;
//                 case 'hasil_maintenance':
//                     $row[] = $riwayat->hasil_maintenance;
//                     break;
//                 case 'rekomendasi':
//                     $row[] = $riwayat->rekomendasi ?: 'Tidak ada';
//                     break;
//             }
//         }

//         return $row;
//     }
// }