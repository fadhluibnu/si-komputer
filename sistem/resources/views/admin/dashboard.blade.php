@extends('admin.components.layout')

@section('content')
    <div class="p-2">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold text-primary mb-3">Sistem Informasi Komputer ESDM</h1>
                <p class="lead">Selamat datang di Sistem Informasi Pengelolaan Data Komputer Kementerian Energi dan Sumber
                    Daya
                    Mineral. Sistem ini memudahkan pengelolaan aset teknologi informasi secara terpusat.</p>
                <div class="d-grid gap-2 d-md-flex mt-4">
                    <a href="{{ route('komputer.create') }}" class="btn btn-primary btn-lg px-4">
                        <i class="bi bi-plus-circle"></i> Tambah Data Perangkat
                    </a>
                    <a href="{{ route('komputer.index') }}" class="btn btn-outline-primary btn-lg px-4">
                        <i class="bi bi-list-ul"></i> Lihat Daftar Perangkat
                    </a>
                </div>
            </div>
            <div class="col-lg-6 mt-4 mt-lg-0">
                <img src="{{ asset('assets/komputer.png') }}" alt="Sistem Informasi Komputer" class="img-fluid rounded">
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <h2 class="border-bottom pb-2 mb-4">
                    <i class="bi bi-pie-chart-fill text-primary"></i> Ringkasan Data
                </h2>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card h-100 shadow-sm border-primary border-top border-4">
                    <div class="card-body p-4">
                        <div class="text-center">
                            <h1 class="display-4 text-primary fw-bold mb-0">{{ $total_komputer ?? 0 }}</h1>
                            <p class="text-muted mt-2">Total Komputer</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm border-success border-top border-4">
                    <div class="card-body p-4">
                        <div class="text-center">
                            <h1 class="display-4 text-success fw-bold mb-0">{{ $kondisi_baik ?? 0 }}</h1>
                            <p class="text-muted mt-2">Kondisi Baik</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm border-warning border-top border-4">
                    <div class="card-body p-4">
                        <div class="text-center">
                            <h1 class="display-4 text-warning fw-bold mb-0">{{ $kondisi_perlu_perhatian ?? 0 }}</h1>
                            <p class="text-muted mt-2">Perlu Perhatian</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm border-danger border-top border-4">
                    <div class="card-body p-4">
                        <div class="text-center">
                            <h1 class="display-4 text-danger fw-bold mb-0">{{ $kondisi_rusak ?? 0 }}</h1>
                            <p class="text-muted mt-2">Rusak</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning bg-opacity-10">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle text-warning me-2"></i> Perangkat Yang
                            Membutuhkan
                            Perhatian</h5>
                    </div>
                    <div class="card-body">
                        @if (empty($komputers ?? []))
                            <div class="text-center py-4">
                                <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                                <p class="mt-3 mb-0">Semua komputer dalam kondisi baik!</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Aksi</th>
                                            <th>Kode Barang</th>
                                            <th>Nama Komputer</th>
                                            <th>Ruangan</th>
                                            <th>Pengguna</th>
                                            <th>Spesifikasi</th>
                                            <th>Kondisi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($komputers ?? [] as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                            type="button" data-bs-toggle="dropdown">
                                                            Aksi
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item"
                                                                    href="{{ route('komputer.show', $item->uuid) }}"><i
                                                                        class="bi bi-eye"></i> Detail</a></li>
                                                            @can ('superadmin', auth()->user())
                                                                <li><a class="dropdown-item"
                                                                        href="{{ route('komputer.edit', $item->uuid) }}"><i
                                                                            class="bi bi-pencil"></i> Edit</a></li>
                                                            @endcan
                                                            <li><a class="dropdown-item"
                                                                    href="{{ route('komputer.edit', $item->uuid) }}"><i
                                                                        class="bi bi-tools"></i> Riwayat Perbaikan</a></li>
                                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                                    data-bs-target="#barcodeModal-{{ $item->uuid }}"><i
                                                                        class="bi bi-upc-scan"></i> Lihat Barcode</a></li>
                                                            <li>
                                                                <hr class="dropdown-divider">
                                                            </li>
                                                            <li>
                                                                <form action="{{ route('komputer.destroy', $item->id) }}"
                                                                    method="POST" class="d-inline"
                                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="dropdown-item text-danger"><i
                                                                            class="bi bi-trash"></i> Hapus</button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>

                                                    <!-- Barcode Modal -->
                                                    <div class="modal fade" id="barcodeModal-{{ $item->kode_barang }}" tabindex="-1"
                                                        aria-labelledby="barcodeModalLabel-{{ $item->kode_barang }}"
                                                        aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title"
                                                                        id="barcodeModalLabel-{{ $item->kode_barang }}">
                                                                        Barcode Komputer: {{ $item->nama_komputer }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                        aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body text-center">
                                                                    @if($item->barcode && Storage::disk('public')->exists($item->barcode))
                                                                        <div class="mb-3">
                                                                            <img src="{{ '/storage/' . $item->barcode }}"
                                                                                alt="Barcode {{ $item->kode_barang }}"
                                                                                class="img-fluid">
                                                                        </div>
                                                                        <div class="mt-2">
                                                                            <p class="mb-1"><strong>Kode Barang:</strong>
                                                                                {{ $item->kode_barang }}</p>
                                                                            <p class="mb-1"><strong>Nama Komputer:</strong>
                                                                                {{ $item->nama_komputer }}</p>
                                                                        </div>
                                                                    @else
                                                                        <div class="alert alert-warning">
                                                                            <i class="bi bi-exclamation-triangle"></i> Barcode belum
                                                                            tersedia
                                                                            untuk komputer ini.
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <div class="modal-footer">
                                                                    @if($item->barcode && Storage::disk('public')->exists($item->barcode))
                                                                        <button type="button" class="btn btn-primary"
                                                                            onclick="printBarcode('{{ Storage::url($item->barcode) }}', '{{ $item->kode_barang }}', '{{ $item->nama_komputer }}')">
                                                                            <i class="bi bi-printer"></i> Print Barcode
                                                                        </button>
                                                                    @endif
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">Tutup</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="{{ route('komputer.show', $item->uuid) }}"
                                                        class="text-decoration-none">
                                                        {{ $item->kode_barang }}
                                                    </a>
                                                </td>
                                                <td>{{ $item->nama_komputer }}</td>
                                                <td>{{ $item->ruangan->nama_ruangan ?? 'Tidak tersedia' }}</td>
                                                <td>{{ $item->nama_pengguna_sekarang }}</td>
                                                <td>
                                                    <small>
                                                        <i class="bi bi-cpu"></i> {{ $item->spesifikasi_processor }}<br>
                                                        <i class="bi bi-memory"></i> {{ $item->spesifikasi_ram }}
                                                    </small>
                                                </td>
                                                <td>{!! kondisiBadge($item->kondisi_komputer) !!}</td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <h2 class="text-center mb-4">Fitur Utama</h2>
            </div>
        </div>

        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-primary bg-gradient text-white rounded-circle mb-3">
                            <i class="bi bi-pc-display" style="font-size: 2rem;"></i>
                        </div>
                        <h3>Pengelolaan Aset</h3>
                        <p>Kelola data perangkat komputer dengan mudah, termasuk spesifikasi, pengguna, dan kondisi terkini.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-primary bg-gradient text-white rounded-circle mb-3">
                            <i class="bi bi-clipboard-data" style="font-size: 2rem;"></i>
                        </div>
                        <h3>Pemeliharaan</h3>
                        <p>Pantau dan kelola histori pemeliharaan perangkat untuk memastikan kinerja yang optimal.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-primary bg-gradient text-white rounded-circle mb-3">
                            <i class="bi bi-bar-chart" style="font-size: 2rem;"></i>
                        </div>
                        <h3>Laporan & Analisis</h3>
                        <p>Buat laporan dan analisis untuk pengambilan keputusan terkait pengelolaan aset IT.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .feature-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
    </style>

    <!-- Add JavaScript for printing barcode -->
    <script>
        function printBarcode(barcodeUrl, assetNumber, computerName) {
            const printWindow = window.open('', '_blank');

            printWindow.document.write(`
                    <html>
                    <head>
                        <title>Print Barcode</title>
                        <style>
                            body { font-family: Arial, sans-serif; text-align: center; }
                            .container { margin: 20px auto; max-width: 400px; }
                            img { max-width: 100%; height: auto; }
                            p { margin: 5px 0; font-size: 14px; }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <div class="mb-3">
                                <img src="${barcodeUrl}" alt="Barcode ${assetNumber}" class="img-fluid">
                            </div>
                            <div class="mt-2">
                                <p class="mb-1"><strong>Kode Barang:</strong> ${assetNumber}</p>
                                <p class="mb-1"><strong>Nama Komputer:</strong> ${computerName}</p>
                            </div>
                        </div>
                        <script>
                            window.onload = function() { window.print(); setTimeout(function() { window.close(); }, 500); }
                        <\/script>
                    </body>
                    </html>
                `);

            printWindow.document.close();
        }
    </script>

@endsection

@php
    /**
     * Helper function to display condition badges
     */
    function kondisiBadge($kondisi)
    {
        switch ($kondisi) {
            case 'Sangat Baik':
                return '<span class="badge bg-success">Sangat Baik</span>';
            case 'Baik':
                return '<span class="badge bg-success">Baik</span>';
            case 'Cukup':
                return '<span class="badge bg-warning text-dark">Cukup</span>';
            case 'Kurang':
                return '<span class="badge bg-warning text-dark">Kurang</span>';
            case 'Rusak':
                return '<span class="badge bg-danger">Rusak</span>';
            default:
                return '<span class="badge bg-secondary">Tidak Diketahui</span>';
        }
    }
@endphp