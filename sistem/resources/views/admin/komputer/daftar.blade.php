@extends('admin.components.layout')

@section('content')
    <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <strong><i class="bi bi-check-circle-fill me-2"></i>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-6">
                <h2 class="border-bottom pb-2">
                    <i class="bi bi-list-ul text-primary"></i> Daftar Perangkat Komputer
                </h2>
            </div>
            @can ('superadmin', auth()->user())
                <div class="col-md-6 d-flex justify-content-md-end align-items-center">
                    <a href="{{ route('komputer.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Perangkat Baru
                    </a>
                </div>
            @endcan
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('komputer.index') }}" method="GET">
                    <div class="row gy-2 gx-3 align-items-center mb-3">

                        {{-- Kolom Pencarian --}}
                        <div class="col-12 col-md-12 col-lg-3">
                            <label for="searchInput" class="visually-hidden">Cari perangkat</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                <input type="text" name="keyword" class="form-control" id="searchInput"
                                    placeholder="Cari kode, nama..." value="{{ request('keyword') }}">
                            </div>
                        </div>

                        {{-- Kolom Filter Ruangan --}}
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <label for="filterRuangan" class="visually-hidden">Filter Ruangan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
                                {{-- Ditambahkan onchange="this.form.submit()" --}}
                                <select class="form-select" name="ruangan" id="filterRuangan" onchange="this.form.submit()">
                                    <option value="">Semua Ruangan</option>
                                    @foreach ($ruangan as $item)
                                        <option value="{{ $item->id }}" {{ request('ruangan') == $item->id ? 'selected' : '' }}>
                                            {{ $item->nama_ruangan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Kolom Filter Kondisi --}}
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <label for="filterKondisi" class="visually-hidden">Filter Kondisi</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-reception-4"></i></span>
                                {{-- Ditambahkan onchange="this.form.submit()" --}}
                                <select class="form-select" name="kondisi" id="filterKondisi" onchange="this.form.submit()">
                                    <option value="">Semua Kondisi</option>
                                    <option value="Sangat Baik" {{ request('kondisi') == 'Sangat Baik' ? 'selected' : '' }}>
                                        Sangat Baik</option>
                                    <option value="Baik" {{ request('kondisi') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                    <option value="Cukup" {{ request('kondisi') == 'Cukup' ? 'selected' : '' }}>Cukup</option>
                                    <option value="Kurang" {{ request('kondisi') == 'Kurang' ? 'selected' : '' }}>Kurang
                                    </option>
                                    <option value="Rusak" {{ request('kondisi') == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                                </select>
                            </div>
                        </div>

                        {{-- Kolom Tombol Aksi --}}
                        <div class="col-12 col-md-4 col-lg-3 d-flex justify-content-between">
                            <div class="d-flex justify-content-start align-items-center gap-2">
                                {{-- Tombol diubah menjadi "Cari" --}}
                                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
                                @if(request('keyword') || request('ruangan') || request('kondisi'))
                                    <a href="{{ route('komputer.index') }}" class="btn btn-outline-secondary"
                                        title="Reset filter">
                                        <i class="bi bi-x"></i>
                                    </a>
                                @endif
                                <div class="dropdown">
                                    <button class="btn btn-success dropdown-toggle" type="button" id="exportDropdown"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-download me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown">
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('komputer.export', ['format' => 'excel'] + request()->query()) }}">
                                                <i class="bi bi-file-earmark-excel me-2 text-success"></i> Export Excel
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('komputer.export', ['format' => 'csv'] + request()->query()) }}">
                                                <i class="bi bi-filetype-csv me-2 text-primary"></i> Export CSV
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('komputer.export', ['format' => 'pdf'] + request()->query()) }}">
                                                <i class="bi bi-file-earmark-pdf me-2 text-danger"></i> Export PDF
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover" id="perangkatTable">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Aksi</th>
                                <th>Kode Barang</th>
                                <th>Nama Komputer</th>
                                <th>Lokasi</th>
                                <th>Pengguna</th>
                                <th>Spesifikasi</th>
                                <th>Kondisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($komputers as $index => $komputer)
                                <tr>
                                    <td>{{ ($komputers->currentPage() - 1) * $komputers->perPage() + $index + 1 }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown">
                                                Aksi
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item"
                                                        href="{{ route('komputer.show', $komputer->uuid) }}"><i
                                                            class="bi bi-eye"></i> Detail</a></li>
                                                @can ('superadmin', auth()->user())
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('komputer.edit', $komputer->uuid) }}"><i
                                                                class="bi bi-pencil"></i> Edit</a></li>
                                                @endcan
                                                <li><a class="dropdown-item"
                                                        href="{{ route('komputer.riwayat.index', $komputer->uuid) }}"><i
                                                            class="bi bi-tools"></i> Riwayat Perbaikan</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                        data-bs-target="#barcodeModal-{{ $komputer->uuid }}"><i
                                                            class="bi bi-upc-scan"></i> Lihat Barcode</a></li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <form action="{{ route('komputer.destroy', $komputer->uuid) }}"
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

                                        <div class="modal fade" id="barcodeModal-{{ $komputer->uuid }}" tabindex="-1"
                                            aria-labelledby="barcodeModalLabel-{{ $komputer->uuid }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="barcodeModalLabel-{{ $komputer->uuid }}">
                                                            Barcode: {{ $komputer->nama_komputer }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        @if($komputer->barcode && Storage::disk('public')->exists($komputer->barcode))
                                                            <div class="mb-3 w-75 mx-auto">
                                                                <img src="{{ asset('storage/' . $komputer->barcode) }}"
                                                                    alt="Barcode {{ $komputer->kode_barang }}" class="img-fluid">
                                                            </div>
                                                            <div class="mt-2">
                                                                <p class="mb-1"><strong>Kode Barang:</strong>
                                                                    {{ $komputer->kode_barang }}</p>
                                                                <p class="mb-1"><strong>Nama Komputer:</strong>
                                                                    {{ $komputer->nama_komputer }}</p>
                                                            </div>
                                                        @else
                                                            <div class="alert alert-warning"><i
                                                                    class="bi bi-exclamation-triangle"></i> Barcode belum tersedia.
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $komputer->kode_barang }}</td>
                                    <td>{{ $komputer->nama_komputer }}</td>
                                    <td>{{ $komputer->ruangan->nama_ruangan }}</td>
                                    <td>{{ $komputer->nama_pengguna_sekarang }}</td>
                                    <td>
                                        <small>
                                            <i class="bi bi-cpu"></i> {{ $komputer->spesifikasi_processor }}<br>
                                            <i class="bi bi-memory"></i> {{ $komputer->spesifikasi_ram }}<br>
                                            <i class="bi bi-hdd"></i> {{ $komputer->spesifikasi_penyimpanan }}
                                        </small>
                                    </td>
                                    <td>
                                        @php
                                            $kondisiClass = ['Sangat Baik' => 'bg-success', 'Baik' => 'bg-success-subtle text-success-emphasis', 'Cukup' => 'bg-info', 'Kurang' => 'bg-warning text-dark', 'Rusak' => 'bg-danger'];
                                        @endphp
                                        <span class="badge {{ $kondisiClass[$komputer->kondisi_komputer] ?? 'bg-secondary' }}">
                                            {{ $komputer->kondisi_komputer ?? 'Tidak Diketahui' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-inbox text-secondary" style="font-size: 2rem;"></i>
                                            <h5 class="mt-3">Tidak Ada Data Ditemukan</h5>
                                            <p class="text-secondary">Coba ubah kata kunci pencarian atau reset filter.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $komputers->links('pagination::bootstrap-5') }}
                </div>

                <div class="text-center text-muted mt-2">
                    <small>Menampilkan {{ $komputers->firstItem() ?? 0 }} sampai {{ $komputers->lastItem() ?? 0 }} dari
                        {{ $komputers->total() }} data</small>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exportOptionsModal" tabindex="-1" aria-labelledby="exportOptionsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportOptionsModalLabel">Opsi Export Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('komputer.export') }}" method="GET">
                    <div class="modal-body">
                        @if(request('keyword')) <input type="hidden" name="keyword" value="{{ request('keyword') }}"> @endif
                        @if(request('ruangan')) <input type="hidden" name="ruangan" value="{{ request('ruangan') }}"> @endif
                        @if(request('kondisi')) <input type="hidden" name="kondisi" value="{{ request('kondisi') }}"> @endif
                        <div class="mb-3">
                            <label class="form-label">Format File</label>
                            <div class="d-flex gap-3">
                                <div class="form-check"><input class="form-check-input" type="radio" name="format"
                                        id="formatExcel" value="excel" checked><label class="form-check-label"
                                        for="formatExcel">Excel</label></div>
                                <div class="form-check"><input class="form-check-input" type="radio" name="format"
                                        id="formatCSV" value="csv"><label class="form-check-label"
                                        for="formatCSV">CSV</label></div>
                                <div class="form-check"><input class="form-check-input" type="radio" name="format"
                                        id="formatPDF" value="pdf"><label class="form-check-label"
                                        for="formatPDF">PDF</label></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kolom yang Diexport</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <h6 class="fw-bold text-muted small mb-2">Informasi Dasar</h6>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]"
                                            value="kode_barang" id="colKodeBarang" checked><label class="form-check-label"
                                            for="colKodeBarang">Kode Barang</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]"
                                            value="nama_komputer" id="colNamaKomputer" checked><label
                                            class="form-check-label" for="colNamaKomputer">Nama Komputer</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]"
                                            value="ruangan" id="colRuangan" checked><label class="form-check-label"
                                            for="colRuangan">Ruangan</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]"
                                            value="nama_pengguna" id="colPengguna" checked><label class="form-check-label"
                                            for="colPengguna">Pengguna</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]"
                                            value="tanggal_pengadaan" id="colTanggalPengadaan" checked><label
                                            class="form-check-label" for="colTanggalPengadaan">Tanggal Pengadaan</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="fw-bold text-muted small mb-2">Spesifikasi & Kondisi</h6>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]"
                                            value="spesifikasi" id="colSpesifikasi" checked><label class="form-check-label"
                                            for="colSpesifikasi">Spesifikasi</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]"
                                            value="kondisi" id="colKondisi" checked><label class="form-check-label"
                                            for="colKondisi">Kondisi</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]"
                                            value="penggunaan" id="colPenggunaan"><label class="form-check-label"
                                            for="colPenggunaan">Penggunaan</label></div>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="fw-bold text-muted small mb-2">Maintenance Terakhir</h6>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]"
                                            value="latest_maintenance_date" id="colMaintenanceDate"><label
                                            class="form-check-label" for="colMaintenanceDate">Tanggal Maintenance</label>
                                    </div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]"
                                            value="latest_maintenance_type" id="colMaintenanceType"><label
                                            class="form-check-label" for="colMaintenanceType">Jenis Maintenance</label>
                                    </div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]"
                                            value="latest_maintenance_technician" id="colMaintenanceTechnician"><label
                                            class="form-check-label" for="colMaintenanceTechnician">Teknisi</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]"
                                            value="latest_maintenance_result" id="colMaintenanceResult"><label
                                            class="form-check-label" for="colMaintenanceResult">Hasil Maintenance</label>
                                    </div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]"
                                            value="latest_maintenance_cost" id="colMaintenanceCost"><label
                                            class="form-check-label" for="colMaintenanceCost">Biaya Maintenance</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-download me-1"></i> Download</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection