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
        table, th, td {
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            padding: 6px;
            text-align: left;
            font-weight: bold;
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
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
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
            margin: 15mm 10mm 15mm 10mm; /* Top, Right, Bottom, Left */
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
    
    <table>
        <thead>
            <tr>
                @foreach($headerRow as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $row)
                <tr>
                    @foreach($row as $cellIndex => $cell)
                        <td>
                            @if(isset($headerRow[$cellIndex]) && $headerRow[$cellIndex] == 'Barcode' && isset($barcodeImages[$index]))
                                @if($barcodeImages[$index])
                                    <!-- Display pre-processed barcode image -->
                                    <img src="{{ $barcodeImages[$index] }}" alt="Barcode" style="max-width: 100px; max-height: 50px; width: auto; height: auto;">
                                @else
                                    <!-- No barcode image available, show text -->
                                    {{ $cell }}
                                @endif
                            @else
                                {{ $cell }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        <p>Dokumen ini diekspor dari Sistem Informasi Komputer ESDM pada {{ now()->format('d F Y, H:i:s') }}</p>
    </div>
</body>
</html>
