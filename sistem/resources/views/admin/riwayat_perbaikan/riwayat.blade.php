@extends('admin.components.layout')

@section('content')
    <div class="container-fluid px-2 px-md-4 py-3 py-md-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <strong><i class="bi bi-check-circle-fill me-2"></i>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Header Section -->
        <div class="row mb-3 mb-md-4 g-3">
            <div class="col-12 col-lg-7">
                <h2 class="border-bottom pb-2 mb-3">
                    <i class="bi bi-tools text-primary"></i> Riwayat Perbaikan Komputer
                </h2>
                <div class="mb-2">
                    <p class="text-muted mb-1">
                        <i class="bi bi-pc-display me-1"></i> 
                        <span class="fw-medium">{{ $komputer->nama_komputer }}</span> 
                        <span class="badge bg-light text-dark ms-1">({{ $komputer->kode_barang }})</span>
                    </p>
                    <p class="text-muted mb-0">
                        <i class="bi bi-person me-1"></i> {{ $komputer->nama_pengguna_sekarang }}
                    </p>
                </div>
            </div>
            <div class="col-12 col-lg-5">
                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-lg-end">
                    <a href="{{ route('komputer.show', $komputer->uuid) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 
                        <span class="d-none d-sm-inline">Kembali ke Detail</span>
                        <span class="d-sm-none">Kembali</span>
                    </a>
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahRiwayatModal">
                        <i class="bi bi-plus-circle"></i> 
                        <span class="d-none d-sm-inline">Tambah Riwayat Baru</span>
                        <span class="d-sm-none">Tambah</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-2 p-md-3">
                <!-- Search and Filter Form -->
                <form class="mb-3" action="{{ route('komputer.riwayat.index', $komputer->uuid) }}" method="GET">
                    <div class="row g-2 g-md-3">
                        <!-- Search Input -->
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" name="keyword" class="form-control" id="searchInput"
                                    placeholder="Cari riwayat..." value="{{ request('keyword') }}">
                                <button class="btn btn-primary d-block d-md-none" type="submit">
                                    Cari
                                </button>
                            </div>
                        </div>

                        <!-- Filter and Actions -->
                        <div class="col-12 col-md-6 col-lg-8">
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <!-- Filter -->
                                <div class="input-group flex-fill" style="max-width: 280px;">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-funnel"></i>
                                    </span>
                                    <select class="form-select" name="jenis" id="filterJenis" onchange="this.form.submit()">
                                        <option value="">Semua Jenis</option>
                                        @foreach ($jenis_maintenance as $jenis)
                                            <option value="{{ $jenis }}" {{ request('jenis') == $jenis ? 'selected' : '' }}>
                                                {{ $jenis }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="d-flex gap-2">
                                    <!-- Search Button (hidden on mobile) -->
                                    <button class="btn btn-primary d-none d-md-block" type="submit">
                                        <i class="bi bi-search"></i> Cari
                                    </button>

                                    <!-- Reset Filter -->
                                    @if(request('keyword') || request('jenis'))
                                        <a href="{{ route('komputer.riwayat.index', $komputer->uuid) }}"
                                            class="btn btn-outline-secondary" title="Reset filter">
                                            <i class="bi bi-x-circle"></i>
                                        </a>
                                    @endif

                                    <!-- Export Dropdown -->
                                    <div class="dropdown">
                                        <button class="btn btn-success dropdown-toggle" type="button" id="exportDropdown"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-download"></i>
                                            <span class="d-none d-sm-inline ms-1">Export</span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown">
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
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route('komputer.riwayat.export', ['komputer' => $komputer->uuid, 'format' => 'csv'] + request()->query()) }}">
                                                    <i class="bi bi-file-earmark-text me-2 text-primary"></i> Export CSV
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Table Container with Horizontal Scroll -->
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="riwayatTable">
                        <thead class="table-light">
                            <tr>
                                <th class="text-nowrap" style="min-width: 50px;">No</th>
                                <th class="text-nowrap" style="min-width: 80px;">Aksi</th>
                                <th class="text-nowrap" style="min-width: 100px;">Tanggal</th>
                                <th class="text-nowrap" style="min-width: 140px;">Jenis Maintenance</th>
                                <th class="text-nowrap" style="min-width: 120px;">Teknisi</th>
                                <th class="text-nowrap" style="min-width: 150px;">Komponen Diganti</th>
                                <th class="text-nowrap" style="min-width: 100px;">Biaya</th>
                                <th class="text-nowrap" style="min-width: 80px;">Hasil</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riwayat as $index => $item)
                                <tr>
                                    <td class="text-nowrap">{{ ($riwayat->currentPage() - 1) * $riwayat->perPage() + $index + 1 }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical d-sm-none"></i>
                                                <span class="d-none d-sm-inline">Aksi</span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                        data-bs-target="#detailModal-{{ $item->uuid }}">
                                                        <i class="bi bi-eye me-2"></i> Detail
                                                    </a>
                                                </li>
                                                @can ('superadmin', auth()->user())
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                            data-bs-target="#editModal-{{ $item->uuid }}">
                                                            <i class="bi bi-pencil me-2"></i> Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <form
                                                            action="{{ route('komputer.riwayat.destroy', [$komputer->uuid, $item->uuid]) }}"
                                                            method="POST" class="d-inline"
                                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat perbaikan ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="bi bi-trash me-2"></i> Hapus
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>

                                        <!-- Detail Modal -->
                                        <div class="modal fade" id="detailModal-{{ $item->uuid }}" tabindex="-1"
                                            aria-labelledby="detailModalLabel-{{ $item->uuid }}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="detailModalLabel-{{ $item->uuid }}">
                                                            Detail Riwayat Perbaikan
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row mb-3 g-3">
                                                            <div class="col-12 col-md-6">
                                                                <h6 class="fw-bold mb-3">Informasi Umum</h6>
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm table-borderless">
                                                                        <tr>
                                                                            <td class="fw-medium" style="width: 40%">Tanggal</td>
                                                                            <td>: {{ $item->created_at->format('d M Y H:i') }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="fw-medium">Jenis Maintenance</td>
                                                                            <td>: {{ $item->jenis_maintenance }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="fw-medium">Teknisi</td>
                                                                            <td>: {{ $item->teknisi }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="fw-medium">Biaya</td>
                                                                            <td>:
                                                                                @if($item->biaya_maintenance)
                                                                                    <span class="text-success fw-medium">
                                                                                        Rp {{ number_format($item->biaya_maintenance, 0, ',', '.') }}
                                                                                    </span>
                                                                                @else
                                                                                    <span class="text-muted">-</span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <h6 class="fw-bold mb-3">Hasil Perbaikan</h6>
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm table-borderless">
                                                                        <tr>
                                                                            <td class="fw-medium" style="width: 40%">Status</td>
                                                                            <td>:
                                                                                @if($item->hasil_maintenance == 'Berhasil')
                                                                                    <span class="badge bg-success">Berhasil</span>
                                                                                @elseif($item->hasil_maintenance == 'Sebagian')
                                                                                    <span class="badge bg-warning text-dark">Sebagian</span>
                                                                                @elseif($item->hasil_maintenance == 'Gagal')
                                                                                    <span class="badge bg-danger">Gagal</span>
                                                                                @else
                                                                                    <span class="badge bg-secondary">{{ $item->hasil_maintenance }}</span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="fw-medium">Komponen Diganti</td>
                                                                            <td>: {{ $item->komponen_diganti ?: 'Tidak ada' }}</td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <h6 class="fw-bold mb-2">Keterangan</h6>
                                                            <div class="border rounded p-3 bg-light">
                                                                {!! nl2br(e($item->keterangan)) ?: '<span class="text-muted">Tidak ada keterangan</span>' !!}
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <h6 class="fw-bold mb-2">Rekomendasi</h6>
                                                            <div class="border rounded p-3 bg-light">
                                                                {!! nl2br(e($item->rekomendasi)) ?: '<span class="text-muted">Tidak ada rekomendasi</span>' !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal-{{ $item->uuid }}" tabindex="-1"
                                            aria-labelledby="editModalLabel-{{ $item->uuid }}" aria-hidden="true" style="z-index: 10500;">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editModalLabel-{{ $item->uuid }}">
                                                            Edit Riwayat Perbaikan
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('komputer.riwayat.update', [$komputer->uuid, $item->uuid]) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="uuid" value="{{ $komputer->uuid }}">
                                                        <div class="modal-body">
                                                            <div class="row g-3">
                                                                <div class="col-12 col-md-6">
                                                                    <div class="mb-3">
                                                                        <label for="edit_jenis_maintenance_{{ $item->uuid }}" class="form-label">
                                                                            Jenis Maintenance <span class="text-danger">*</span>
                                                                        </label>
                                                                        <select class="form-select" id="edit_jenis_maintenance_{{ $item->uuid }}" name="jenis_maintenance" required>
                                                                            <option value="">Pilih jenis maintenance</option>
                                                                            <option value="Perbaikan Hardware" {{ $item->jenis_maintenance == 'Perbaikan Hardware' ? 'selected' : '' }}>Perbaikan Hardware</option>
                                                                            <option value="Perbaikan Software" {{ $item->jenis_maintenance == 'Perbaikan Software' ? 'selected' : '' }}>Perbaikan Software</option>
                                                                            <option value="Pemeliharaan Rutin" {{ $item->jenis_maintenance == 'Pemeliharaan Rutin' ? 'selected' : '' }}>Pemeliharaan Rutin</option>
                                                                            <option value="Upgrade" {{ $item->jenis_maintenance == 'Upgrade' ? 'selected' : '' }}>Upgrade</option>
                                                                            <option value="Lainnya" {{ $item->jenis_maintenance == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="edit_teknisi_{{ $item->uuid }}" class="form-label">
                                                                            Teknisi <span class="text-danger">*</span>
                                                                        </label>
                                                                        <input type="text" class="form-control" id="edit_teknisi_{{ $item->uuid }}" 
                                                                               name="teknisi" value="{{ $item->teknisi }}" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="edit_komponen_diganti_{{ $item->uuid }}" class="form-label">
                                                                            Komponen yang Diganti
                                                                        </label>
                                                                        <input type="text" class="form-control" id="edit_komponen_diganti_{{ $item->uuid }}"
                                                                               name="komponen_diganti" value="{{ $item->komponen_diganti }}">
                                                                        <div class="form-text">Kosongkan jika tidak ada komponen yang diganti</div>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="edit_biaya_maintenance_{{ $item->uuid }}" class="form-label">
                                                                            Biaya Maintenance (Rp)
                                                                        </label>
                                                                        <input type="number" class="form-control" id="edit_biaya_maintenance_{{ $item->uuid }}"
                                                                               name="biaya_maintenance" value="{{ $item->biaya_maintenance }}">
                                                                        <div class="form-text">Kosongkan jika tidak ada biaya</div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-6">
                                                                    <div class="mb-3">
                                                                        <label for="edit_hasil_maintenance_{{ $item->uuid }}" class="form-label">
                                                                            Hasil Maintenance <span class="text-danger">*</span>
                                                                        </label>
                                                                        <select class="form-select" id="edit_hasil_maintenance_{{ $item->uuid }}" name="hasil_maintenance" required>
                                                                            <option value="">Pilih hasil maintenance</option>
                                                                            <option value="Berhasil" {{ $item->hasil_maintenance == 'Berhasil' ? 'selected' : '' }}>Berhasil</option>
                                                                            <option value="Sebagian" {{ $item->hasil_maintenance == 'Sebagian' ? 'selected' : '' }}>Sebagian</option>
                                                                            <option value="Gagal" {{ $item->hasil_maintenance == 'Gagal' ? 'selected' : '' }}>Gagal</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="edit_keterangan_{{ $item->uuid }}" class="form-label">
                                                                            Keterangan <span class="text-danger">*</span>
                                                                        </label>
                                                                        <textarea class="form-control" id="edit_keterangan_{{ $item->uuid }}" 
                                                                                  name="keterangan" rows="3" required>{{ $item->keterangan }}</textarea>
                                                                        <div class="form-text">Jelaskan permasalahan dan tindakan yang dilakukan</div>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="edit_rekomendasi_{{ $item->uuid }}" class="form-label">
                                                                            Rekomendasi
                                                                        </label>
                                                                        <textarea class="form-control" id="edit_rekomendasi_{{ $item->uuid }}" 
                                                                                  name="rekomendasi" rows="3">{{ $item->rekomendasi }}</textarea>
                                                                        <div class="form-text">Rekomendasi untuk pemeliharaan ke depan</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">{{ $item->created_at->format('d M Y') }}</td>
                                    <td>
                                        <span class="d-inline-block" style="max-width: 140px;" title="{{ $item->jenis_maintenance }}">
                                            {{ Str::limit($item->jenis_maintenance, 15) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="d-inline-block" style="max-width: 120px;" title="{{ $item->teknisi }}">
                                            {{ Str::limit($item->teknisi, 12) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item->komponen_diganti)
                                            <span class="d-inline-block" style="max-width: 150px;" title="{{ $item->komponen_diganti }}">
                                                {{ Str::limit($item->komponen_diganti, 20) }}
                                            </span>
                                        @else
                                            <span class="text-muted">Tidak ada</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        @if($item->biaya_maintenance)
                                            <span class="text-success fw-medium">
                                                Rp {{ number_format($item->biaya_maintenance, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->hasil_maintenance == 'Berhasil')
                                            <span class="badge bg-success">Berhasil</span>
                                        @elseif($item->hasil_maintenance == 'Sebagian')
                                            <span class="badge bg-warning text-dark">Sebagian</span>
                                        @elseif($item->hasil_maintenance == 'Gagal')
                                            <span class="badge bg-danger">Gagal</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $item->hasil_maintenance }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-clipboard-x text-secondary mb-3" style="font-size: 3rem;"></i>
                                            <h5 class="text-secondary mb-2">Belum ada riwayat perbaikan</h5>
                                            <p class="text-muted mb-0">Belum ada riwayat perbaikan yang tersedia untuk komputer ini</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Section -->
                @if($riwayat->hasPages())
                    <div class="border-top pt-3 mt-3">
                        <div class="d-flex justify-content-center mb-3">
                            {{ $riwayat->links('pagination::bootstrap-5') }}
                        </div>
                        <div class="text-center">
                            <small class="text-muted">
                                Menampilkan {{ $riwayat->firstItem() ?? 0 }} sampai {{ $riwayat->lastItem() ?? 0 }} dari {{ $riwayat->total() }} data
                            </small>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('admin.riwayat_perbaikan.tambah')
@endsection

@push('styles')
    <style>
        /* Modal z-index fix for scrolling issues */
        .modal {
            z-index: 10500 !important;
        }

        .modal-backdrop {
            z-index: 10499 !important;
        }

        /* Custom responsive adjustments */
        @media (max-width: 576px) {
            .container-fluid {
                padding-left: 8px;
                padding-right: 8px;
            }

            .card-body {
                padding: 1rem !important;
            }

            .table th, .table td {
                padding: 0.5rem 0.25rem;
                font-size: 0.875rem;
            }

            .btn {
                padding: 0.375rem 0.5rem;
            }

            .btn-sm {
                padding: 0.25rem 0.375rem;
            }

            .modal-dialog {
                margin: 0.5rem;
            }
        }

        @media (max-width: 768px) {
            .table-responsive {
                border: none;
            }

            .table {
                margin-bottom: 0;
            }

            .dropdown-menu {
                font-size: 0.875rem;
            }
        }

        /* Ensure proper spacing in modals */
        .modal-body .row.g-3 > * {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        /* Better badge visibility */
        .badge {
            font-size: 0.75rem;
            padding: 0.375em 0.75em;
        }

        /* Improved table scroll experience */
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Additional modal improvements */
        .modal-dialog-scrollable .modal-content {
            max-height: calc(100vh - 3.5rem);
        }

        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
            max-height: calc(100vh - 200px);
        }
    </style>
@endpush