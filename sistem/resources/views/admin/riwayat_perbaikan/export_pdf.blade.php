{{--
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: auto;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
        }

        td {
            padding: 6px;
            word-wrap: break-word;
            max-width: 150px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        h1 {
            font-size: 18px;
            text-align: center;
            margin-bottom: 15px;
        }

        h2 {
            font-size: 16px;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .komputer-detail {
            width: 100%;
            margin-bottom: 20px;
        }

        .komputer-detail td,
        .komputer-detail th {
            padding: 5px;
        }

        .detail-label {
            width: 25%;
            font-weight: bold;
        }

        .detail-value {
            width: 75%;
        }

        .barcode-container {
            text-align: center;
            margin: 15px 0;
            width: 100%;
        }

        .barcode-image {
            max-width: 180px;
            max-height: 90px;
            margin: 0 auto;
            display: block;
        }

        .gallery-container {
            display: flex;
            flex-wrap: wrap;
            margin: 10px 0;
            gap: 10px;
        }

        .gallery-item {
            width: 32%;
            margin-bottom: 10px;
            text-align: center;
            display: inline-block;
        }

        .gallery-image {
            width: 100%;
            height: 90px;
            object-fit: contain;
            border: 1px solid #ddd;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
        }

        .footer {
            margin-top: 20px;
            font-size: 9px;
            text-align: center;
            color: #777;
        }

        /* Mengoptimalkan tampilan untuk landscape */
        @page {
            size: landscape;
            margin: 15mm 10mm 15mm 10mm;
            /* Top, Right, Bottom, Left */
        }
    </style>
</head>

<body>
    <h1>{{ $title }}</h1>
    <div class="header">
        <div>
            <p>Tanggal Export: {{ now()->format('d F Y, H:i') }}</p>
        </div>
    </div>

    <!-- Komputer Details Section -->
    <h2>Detail Komputer</h2>
    <table class="komputer-detail">
        <tr>
            <td class="detail-label">Kode Barang</td>
            <td class="detail-value">{{ $komputer->kode_barang }}</td>
        </tr>
        <tr>
            <td class="detail-label">Nama Komputer</td>
            <td class="detail-value">{{ $komputer->nama_komputer }}</td>
        </tr>
        <tr>
            <td class="detail-label">Ruangan</td>
            <td class="detail-value">
                {{ $komputer->ruangan ? $komputer->ruangan->nama_ruangan : 'Tidak tersedia' }}
            </td>
        </tr>
        <tr>
            <td class="detail-label">Pengguna</td>
            <td class="detail-value">{{ $komputer->nama_pengguna_sekarang }}</td>
        </tr>
        <tr>
            <td class="detail-label">Spesifikasi</td>
            <td class="detail-value">
                Processor: {{ $komputer->spesifikasi_processor }},
                RAM: {{ $komputer->spesifikasi_ram }},
                Storage: {{ $komputer->spesifikasi_penyimpanan }}
            </td>
        </tr>
        <tr>
            <td class="detail-label">Kondisi</td>
            <td class="detail-value">{{ $komputer->kondisi_komputer }}</td>
        </tr>
    </table>


    <table class="komputer-detail">
        <tr>
            <td style="width: 25%; vertical-align: top; text-align: center;">
                <h2>Kode Aset</h2>
                @if(!empty($barcodeImage))
                <img src="{{ $barcodeImage }}" alt="Barcode" style="max-width: 150px;">
                @endif
            </td>
            <td style="width: 70%; vertical-align: top;">
                <table>
                    <tr>
                        <td>
                            <h2>Foto Komputer</h2>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            @if(count($galleryImages) > 0)
                            <div style="display: flex; flex-wrap: wrap; justify-content: flex-start; gap: 15px;">
                                @foreach($galleryImages as $index => $image)
                                <div style="display: inline-block; margin-bottom: 10px; text-align: center;">
                                    @if($image['type'] == 'image')
                                    <img src="{{ $image['data'] }}" alt="Foto" class="gallery-image">
                                    @elseif($image['type'] == 'pdf')
                                    <div class="pdf-item">
                                        <p style="font-weight: bold; margin:0;">Dokumen PDF</p>
                                        <p style="margin-top: 5px; word-wrap: break-word;">
                                            {{ \Illuminate\Support\Str::limit($image['name'], 20) }}</p>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p>Tidak ada foto</p>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Riwayat Perbaikan Section -->
    <h2>Riwayat Pemeliharaan</h2>
    <table>
        <thead>
            <tr>
                @foreach($headerRow as $header)
                <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                @foreach($row as $cell)
                <td>{{ $cell }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini diekspor dari Sistem Informasi Komputer ESDM pada {{ now()->format('d F Y, H:i:s') }}</p>
    </div>
</body>

</html> --}}

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: auto;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
        }

        td {
            padding: 6px;
            word-wrap: break-word;
            max-width: 150px;
        }

        h1 {
            font-size: 18px;
            text-align: center;
            margin-bottom: 15px;
        }

        h2 {
            font-size: 16px;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .detail-label {
            width: 25%;
            font-weight: bold;
        }

        .barcode-image {
            max-width: 180px;
            max-height: 90px;
            margin: 0 auto;
            display: block;
        }

        .gallery-image {
            width: 100px;
            height: 90px;
            object-fit: contain;
            border: 1px solid #ddd;
        }

        .pdf-item {
            width: 100px;
            height: 90px;
            border: 1px dashed #ccc;
            padding: 5px;
            text-align: center;
            font-size: 9px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .footer {
            margin-top: 20px;
            font-size: 9px;
            text-align: center;
            color: #777;
        }

        @page {
            size: landscape;
            margin: 15mm 10mm;
        }
    </style>
</head>

<body>
    <h1>{{ $title }}</h1>
    <p>Tanggal Ekspor: {{ now()->format('d F Y, H:i') }}</p>

    <h2>Detail Komputer</h2>
    <table class="komputer-detail">
        <tr>
            <td class="detail-label">Kode Barang</td>
            <td>{{ $komputer->kode_barang }}</td>
            <td class="detail-label">Pengguna</td>
            <td>{{ $komputer->nama_pengguna_sekarang }}</td>
        </tr>
        <tr>
            <td class="detail-label">Nama Komputer</td>
            <td>{{ $komputer->nama_komputer }}</td>
            <td class="detail-label">Kondisi</td>
            <td>{{ $komputer->kondisi_komputer }}</td>
        </tr>
        <tr>
            <td class="detail-label">Ruangan</td>
            <td>{{ $komputer->ruangan ? $komputer->ruangan->nama_ruangan : 'Tidak tersedia' }}</td>
            <td class="detail-label">Spesifikasi</td>
            <td>Proc: {{ $komputer->spesifikasi_processor }}, RAM: {{ $komputer->spesifikasi_ram }}</td>
        </tr>
    </table>

    <table style="width: 100%;">
        <tr>
            <td style="width: 25%; vertical-align: top; text-align: center;">
                <h2>Kode Aset</h2>
                @if(!empty($barcodeImage))
                    <img src="{{ $barcodeImage }}" alt="Barcode" style="max-width: 150px;">
                @endif
            </td>
            <td style="width: 70%; vertical-align: top;">
                <h2>FOTO KOMPUTER</h2>
                @if(isset($galleryImages) && count($galleryImages) > 0)
                    <div style="padding: 5px;">
                        @foreach($galleryImages as $index => $file)
                            <div style="display: inline-block; margin: 5px; text-align: center; vertical-align: top;">
                                {{-- TAMBAHKAN PEMERIKSAAN is_array --}}
                                @if(is_array($file) && isset($file['type']))
                                    @if($file['type'] == 'image')
                                        <img src="{{ $file['data'] }}" alt="Foto" class="gallery-image">
                                    @elseif($file['type'] == 'pdf')
                                        <a href="{{ $file['url'] }}" target="_blank" class="pdf-link">
                                            <div class="pdf-item">
                                                <p style="font-weight: bold; margin:0;">Dokumen PDF</p>
                                                <p style="margin: 5px 0; word-wrap: break-word;">
                                                    {{ \Illuminate\Support\Str::limit($file['name'], 20) }}</p>
                                                <small style="color: #007bff;">(Klik untuk buka)</small>
                                            </div>
                                        </a>
                                    @endif
                                @elseif(is_string($file))
                                    {{-- Fallback jika data masih berupa string (format lama) --}}
                                    <img src="{{ $file }}" alt="Foto" class="gallery-image">
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p>Tidak ada foto</p>
                @endif
            </td>
        </tr>
    </table>

    <h2>Riwayat Pemeliharaan</h2>
    <table>
        <thead>
            <tr>
                @foreach($headerRow as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @if(count($data) > 0)
                @foreach($data as $row)
                    <tr>
                        @foreach($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="{{ count($headerRow) }}" style="text-align: center;">Tidak ada data.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini diekspor dari Sistem Informasi Komputer ESDM pada {{ now()->format('d F Y, H:i:s') }}</p>
    </div>
</body>

</html>