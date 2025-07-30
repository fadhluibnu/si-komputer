@extends('admin.components.layout')

@section('content')

    <style>
        /* General styles from original file */
        .detail-card {
            transition: all 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
        }

        .detail-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .spec-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin-right: 15px;
            background-color: rgba(13, 110, 253, 0.1);
            color: var(--primary-color);
        }

        .spec-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .spec-item:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }

        .action-btn {
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .detail-header {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }

        .detail-header:before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background-color: var(--primary-color);
        }

        .qr-container {
            padding: 15px;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            text-align: center;
        }

        /* Gallery and Carousel Styles */
        .gallery-container {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .carousel-item {
            height: 450px;
            /* Increased height for better PDF view */
            background-color: #e9ecef;
        }

        .carousel-item img {
            object-fit: contain;
            height: 100%;
            width: 100%;
        }

        .carousel-item iframe {
            border: none;
            width: 100%;
            height: 100%;
        }

        .thumbnails-container {
            display: flex;
            overflow-x: auto;
            gap: 8px;
            padding: 10px;
            background-color: #f8f9fa;
        }

        .thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }

        .thumbnail:hover {
            transform: translateY(-2px);
        }

        .thumbnail.active {
            border-color: var(--primary-color);
        }

        .carousel-control-prev,
        .carousel-control-next {
            width: 10%;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .gallery-container:hover .carousel-control-prev,
        .gallery-container:hover .carousel-control-next {
            opacity: 0.8;
        }

        /* New style for PDF thumbnails */
        .pdf-thumbnail {
            background-color: #fff;
            border: 2px solid #dee2e6;
            color: #dc3545;
        }
    </style>

    <div class="container py-4">
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <a href="{{ route('komputer.index') }}" class="btn btn-sm btn-outline-secondary me-3">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <div class="d-flex align-items-center mb-2">
                    <h2 class="mb-0">
                        <i class="bi bi-pc-display text-primary"></i>
                        {{ $komputer->nama_komputer }}
                    </h2>
                </div>
                <p class="text-muted">{{ $komputer->kode_barang }}</p>
            </div>
            <div class="col-md-6 d-flex justify-content-md-end mt-3 mt-md-0">
                <div class="btn-toolbar gap-2" role="toolbar">
                    <div class="btn-group me-2" role="group">
                        <a href="{{ route('komputer.edit', $komputer->uuid) }}" class="btn btn-outline-primary">
                            <i class="bi bi-pencil-square"></i> Edit Data
                        </a>
                        <a href="{{ route('komputer.riwayat.index', $komputer->uuid) }}" class="btn btn-outline-success">
                            <i class="bi bi-tools"></i> Riwayat Perbaikan
                        </a>
                    </div>
                    <div class="action-buttons-responsive">
                        <div class="row g-2">
                            <div class="col-12 col-sm-6 col-md-4 col-lg-auto">
                                <form action="{{ route('komputer.destroy', $komputer->uuid) }}" method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')" class="w-100">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100">
                                        <i class="bi bi-trash me-1"></i>
                                        <span class="d-none d-sm-inline">Hapus</span>
                                        <span class="d-sm-none">Hapus</span>
                                    </button>
                                </form>
                            </div>

                            <div class="col-12 col-sm-6 col-md-4 col-lg-auto">
                                <form action="{{ route('komputer.regenerate-qrcode', $komputer->uuid) }}" method="POST"
                                    class="w-100">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-dark w-100">
                                        <i class="bi bi-qr-code me-1"></i>
                                        <span class="d-none d-md-inline">Buat Ulang QR Code</span>
                                        <span class="d-md-none">Buat Ulang QR Code</span>
                                    </button>
                                </form>
                            </div>

                            <div class="col-12 col-md-4 col-lg-auto">
                                <div class="dropdown w-100">
                                    <button class="btn btn-success dropdown-toggle w-100" type="button"
                                        id="exportDropdownResponsive" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-download me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end w-100"
                                        aria-labelledby="exportDropdownResponsive">
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('komputer.riwayat.export', ['komputer' => $komputer->uuid, 'format' => 'excel'] + request()->query()) }}">
                                                <i class="bi bi-file-earmark-excel me-2 text-success"></i> Export Excel
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('komputer.riwayat.export', ['komputer' => $komputer->uuid, 'format' => 'pdf'] + request()->query()) }}">
                                                <i class="bi bi-file-earmark-pdf me-2 text-danger"></i> Export PDF
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <style>
                        /* Action Buttons Responsive Styles */
                        .action-buttons-container {
                            margin: 1rem 0;
                        }

                        /* Mobile specific improvements */
                        @media (max-width: 575.98px) {
                            .action-buttons-container .btn-sm {
                                padding: 0.5rem 0.75rem;
                                font-size: 0.875rem;
                            }

                            .action-buttons-container .btn {
                                min-height: 42px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            }
                        }

                        /* Tablet improvements */
                        @media (min-width: 576px) and (max-width: 991.98px) {
                            .action-buttons-container .btn-sm {
                                padding: 0.4rem 0.8rem;
                                font-size: 0.875rem;
                            }
                        }

                        /* Desktop improvements */
                        @media (min-width: 992px) {
                            .action-buttons-container .btn {
                                min-width: 120px;
                            }
                        }

                        /* Dropdown improvements for all screen sizes */
                        .dropdown-menu {
                            z-index: 10000;
                            min-width: 180px;
                        }

                        .dropdown-item {
                            padding: 0.5rem 1rem;
                            transition: all 0.2s ease;
                        }

                        .dropdown-item:hover {
                            background-color: #f8f9fa;
                            transform: translateX(2px);
                        }

                        /* Button hover effects */
                        .btn {
                            transition: all 0.3s ease;
                            border-width: 1px;
                        }

                        .btn:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                        }

                        .btn:active {
                            transform: translateY(0);
                        }

                        /* Ensure consistent button heights */
                        .btn-sm {
                            line-height: 1.4;
                        }

                        /* Better visual separation */
                        .action-buttons-container {
                            background: rgba(248, 249, 250, 0.5);
                            border-radius: 8px;
                            padding: 1rem;
                            border: 1px solid rgba(0, 0, 0, 0.05);
                        }

                        /* Focus states for accessibility */
                        .btn:focus {
                            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
                            outline: none;
                        }

                        /* Loading state (optional) */
                        .btn.loading {
                            pointer-events: none;
                            opacity: 0.6;
                        }

                        .btn.loading::after {
                            content: "";
                            display: inline-block;
                            width: 1rem;
                            height: 1rem;
                            margin-left: 0.5rem;
                            border: 2px solid transparent;
                            border-top: 2px solid currentColor;
                            border-radius: 50%;
                            animation: spin 1s linear infinite;
                        }

                        @keyframes spin {
                            0% {
                                transform: rotate(0deg);
                            }

                            100% {
                                transform: rotate(360deg);
                            }
                        }
                    </style>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="gallery-container mb-4">
                    @if($komputer->galleries->isNotEmpty())
                        <div id="komputerGallery" class="carousel slide" data-bs-ride="false">
                            <div class="carousel-inner">
                                @foreach($komputer->galleries as $index => $gallery)
                                    @php
                                        // Cek ekstensi file
                                        $extension = strtolower(pathinfo($gallery->image_path, PATHINFO_EXTENSION));
                                        $isPdf = $extension === 'pdf';
                                    @endphp
                                    <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                        @if($isPdf)
                                            {{-- Tampilkan PDF menggunakan iframe --}}
                                            <iframe src="{{ asset('storage/' . $gallery->image_path) }}"
                                                title="PDF Viewer: {{ $komputer->nama_komputer }}"></iframe>
                                        @else
                                            {{-- Tampilkan gambar seperti biasa --}}
                                            <img src="{{ asset('storage/' . $gallery->image_path) }}"
                                                alt="{{ $komputer->nama_komputer }} - File {{ $index + 1 }}" class="d-block w-100">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#komputerGallery"
                                data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#komputerGallery"
                                data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                            <div class="position-absolute bottom-0 end-0 p-3">
                                <span class="badge rounded-pill bg-dark bg-opacity-75">
                                    <i class="bi bi-files"></i> {{ $komputer->galleries->count() }} File Media
                                </span>
                            </div>
                        </div>

                        <div class="thumbnails-container">
                            @foreach($komputer->galleries as $index => $gallery)
                                @php
                                    $extension = strtolower(pathinfo($gallery->image_path, PATHINFO_EXTENSION));
                                    $isPdf = $extension === 'pdf';
                                @endphp

                                @if($isPdf)
                                    {{-- Thumbnail untuk PDF --}}
                                    <div class="thumbnail pdf-thumbnail d-flex align-items-center justify-content-center {{ $index == 0 ? 'active' : '' }}"
                                        data-bs-target="#komputerGallery" data-bs-slide-to="{{ $index }}">
                                        <i class="bi bi-file-earmark-pdf fs-2"></i>
                                    </div>
                                @else
                                    {{-- Thumbnail untuk Gambar --}}
                                    <img src="{{ asset('storage/' . $gallery->image_path) }}"
                                        class="thumbnail {{ $index == 0 ? 'active' : '' }}" data-bs-target="#komputerGallery"
                                        data-bs-slide-to="{{ $index }}" alt="Thumbnail {{ $index + 1 }}">
                                @endif
                            @endforeach
                        </div>
                    @else
                        {{-- Fallback jika tidak ada media sama sekali --}}
                        <div class="carousel-item active d-flex align-items-center justify-content-center">
                            <div class="text-center">
                                <i class="bi bi-camera-reels fs-1 text-muted"></i>
                                <p class="mt-2 text-muted">Tidak ada file media yang tersedia</p>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card detail-card mb-4">
                    <div class="card-header bg-white">
                        <h4 class="mb-0"><i class="bi bi-info-circle text-primary"></i> Informasi Umum</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="spec-item">
                                    <div class="spec-icon">
                                        <i class="bi bi-door-open"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Ruangan</small>
                                        <strong>{{ $komputer->ruangan->nama_ruangan }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="spec-item">
                                    <div class="spec-icon">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Pengguna</small>
                                        <strong>{{ $komputer->nama_pengguna_sekarang }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="spec-item">
                                    <div class="spec-icon">
                                        <i class="bi bi-pc-display-horizontal"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Penggunaan Sekarang</small>
                                        <strong>{{ $komputer->penggunaan_sekarang }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="spec-item">
                                    <div class="spec-icon">
                                        <i class="bi bi-calendar-event"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Tahun Pengadaan</small>
                                        <strong>{{ $komputer->tahun_pengadaan }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="spec-item">
                                    <div class="spec-icon">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Kesesuaian Mendukung Pekerjaan</small>
                                        @php
                                            $kesesuaianClass = [
                                                'Sangat Sesuai' => 'bg-success',
                                                'Sesuai' => 'bg-success',
                                                'Kurang Sesuai' => 'bg-warning text-dark',
                                                'Tidak Sesuai' => 'bg-danger'
                                            ];
                                        @endphp
                                        <span
                                            class="badge {{ $kesesuaianClass[$komputer->kesesuaian_pc] ?? 'bg-secondary' }}">
                                            {{ $komputer->kesesuaian_pc ?? 'Tidak Diketahui' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="spec-item">
                            <div class="spec-icon">
                                <i class="bi bi-tags"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Merek Komputer</small>
                                <strong>{{ $komputer->merek_komputer }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card detail-card mb-4">
                    <div class="card-header bg-white">
                        <h4 class="mb-0"><i class="bi bi-cpu text-primary"></i> Spesifikasi Teknis</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="spec-item">
                                    <div class="spec-icon"><i class="bi bi-cpu"></i></div>
                                    <div><small
                                            class="text-muted d-block">Processor</small><strong>{{ $komputer->spesifikasi_processor }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="spec-item">
                                    <div class="spec-icon"><i class="bi bi-memory"></i></div>
                                    <div><small
                                            class="text-muted d-block">RAM</small><strong>{{ $komputer->spesifikasi_ram }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="spec-item">
                                    <div class="spec-icon"><i class="bi bi-gpu-card"></i></div>
                                    <div><small
                                            class="text-muted d-block">VGA</small><strong>{{ $komputer->spesifikasi_vga ?? 'N/A' }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="spec-item">
                                    <div class="spec-icon"><i class="bi bi-hdd"></i></div>
                                    <div><small
                                            class="text-muted d-block">Penyimpanan</small><strong>{{ $komputer->spesifikasi_penyimpanan }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="spec-item">
                                    <div class="spec-icon"><i class="bi bi-windows"></i></div>
                                    <div><small class="text-muted d-block">Sistem
                                            Operasi</small><strong>{{ $komputer->sistem_operasi }}</strong></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="spec-item">
                                    <div class="spec-icon">
                                        <i class="bi bi-heart-pulse"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Kondisi</small>
                                        @php
                                            $kondisiClass = [
                                                'Sangat Baik' => 'bg-success',
                                                'Baik' => 'bg-success',
                                                'Cukup' => 'bg-warning text-dark',
                                                'Kurang' => 'bg-warning text-dark',
                                                'Rusak' => 'bg-danger'
                                            ];
                                        @endphp
                                        <span
                                            class="badge {{ $kondisiClass[$komputer->kondisi_komputer] ?? 'bg-secondary' }}">
                                            {{ $komputer->kondisi_komputer ?? 'Tidak Diketahui' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(!empty($komputer->keterangan_kondisi))
                            <div class="alert alert-light mt-3 border">
                                <p class="mb-0"><i
                                        class="bi bi-info-circle me-2"></i>{!! nl2br(e($komputer->keterangan_kondisi)) !!}</p>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card detail-card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="bi bi-clock-history text-primary"></i> Histori Pemeliharaan</h4>
                        <a href="{{ route('komputer.riwayat.index', $komputer->uuid) }}"
                            class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-card-list"></i> Lihat Selengkapnya
                        </a>
                    </div>
                    <div class="card-body">
                        @if($komputer->maintenanceHistories->isEmpty())
                            <div class="text-center p-3 text-muted">
                                <i class="bi bi-folder-x fs-2"></i>
                                <p class="mt-2 mb-0">Belum ada data pemeliharaan yang tercatat.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Keterangan</th>
                                            <th>Teknisi</th>
                                            <th>Hasil</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($komputer->maintenanceHistories->take(5) as $item)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</td>
                                                <td>{{ Str::limit($item->keterangan, 50) }}</td>
                                                <td>{{ $item->teknisi ?? '-' }}</td>
                                                <td>{{ $item->hasil_maintenance ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <div class="col-lg-4">
                <div class="card detail-card sticky-lg-top" style="top: 20px;">
                    <div class="card-header bg-white">
                        <h4 class="mb-0"><i class="bi bi-upc-scan text-primary"></i> Kode Aset</h4>
                    </div>
                    <div class="card-body text-center">
                        <div class="qr-container mb-3">
                            @if($komputer->barcode && Storage::disk('public')->exists($komputer->barcode))
                                <img src="{{ asset('storage/' . $komputer->barcode) }}" alt="Barcode {{ $komputer->uuid }}"
                                    class="img-fluid">
                                <p class="mt-2 text-muted small">Scan untuk melihat detail aset</p>
                            @else
                                <p class="text-danger my-4">QR Code belum dibuat. Silakan klik "Regenerate QR Code".</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const gallery = document.getElementById('komputerGallery');
                if (gallery) {
                    const thumbnails = document.querySelectorAll('.thumbnail');

                    // Fungsi untuk mengaktifkan thumbnail
                    function activateThumbnail(index) {
                        thumbnails.forEach(thumb => thumb.classList.remove('active'));
                        if (thumbnails[index]) {
                            thumbnails[index].classList.add('active');
                        }
                    }

                    // Event listener untuk klik thumbnail
                    thumbnails.forEach(thumbnail => {
                        thumbnail.addEventListener('click', function () {
                            const slideIndex = this.getAttribute('data-bs-slide-to');
                            activateThumbnail(slideIndex);
                        });
                    });

                    // Event listener untuk slide carousel
                    gallery.addEventListener('slide.bs.carousel', function (e) {
                        activateThumbnail(e.to);
                    });
                }
            });
        </script>
    @endpush

@endsection