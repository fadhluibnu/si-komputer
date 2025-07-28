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
                    <i class="bi bi-tools text-primary"></i> Riwayat Perbaikan Komputer
                </h2>
                <p class="text-muted">
                    <i class="bi bi-pc-display me-1"></i> {{ $komputer->nama_komputer }} ({{ $komputer->kode_barang }})
                </p>
                <p class="text-muted">
                    <i class="bi bi-person"></i> {{ $komputer->nama_pengguna_sekarang }}
                </p>
            </div>
            <div class="col-md-6 d-flex justify-content-md-end align-items-center">
                <a href="{{ route('komputer.show', $komputer->uuid) }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Kembali ke Detail
                </a>
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahRiwayatModal">
                    <i class="bi bi-plus-circle"></i> Tambah Riwayat Baru
                </a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form class="row mb-3" action="{{ route('komputer.riwayat.index', $komputer->uuid) }}" method="GET">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="d-flex">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" name="keyword" class="form-control" id="searchInput"
                                    placeholder="Cari riwayat..." value="{{ request('keyword') }}">
                                <button class="btn btn-primary" type="submit">
                                    Cari
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex gap-2 justify-content-md-end align-items-center">
                            <div class="d-flex flex-grow-1 justify-content-md-end gap-2">
                                <div class="input-group flex-nowrap" style="max-width: 260px;">
                                    <span class="input-group-text bg-light"><i class="bi bi-funnel"></i></span>
                                    <select class="form-select" name="jenis" id="filterJenis" onchange="this.form.submit()">
                                        <option value="">Semua Jenis Perbaikan</option>
                                        @foreach ($jenis_maintenance as $jenis)
                                            <option value="{{ $jenis }}" {{ request('jenis') == $jenis ? 'selected' : '' }}>
                                                {{ $jenis }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @if(request('keyword') || request('jenis'))
                                    <a href="{{ route('komputer.riwayat.index', $komputer->uuid) }}"
                                        class="btn btn-outline-secondary" title="Reset filter">
                                        <i class="bi bi-x-circle"></i>
                                    </a>
                                @endif
                                </iv>

                                <div class="dropdown">
                                    <button class="btn btn-success dropdown-toggle" type="button" id="exportDropdown"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-download me-1"></i> Export
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
                </form>

                <div class="">
                    <table class="table table-hover" id="riwayatTable">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Aksi</th>
                                <th>Tanggal</th>
                                <th>Jenis Maintenance</th>
                                <th>Teknisi</th>
                                <th>Komponen Diganti</th>
                                <th>Biaya</th>
                                <th>Hasil</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riwayat as $index => $item)
                                <tr>
                                    <td>{{ ($riwayat->currentPage() - 1) * $riwayat->perPage() + $index + 1 }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown">
                                                Aksi
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                        data-bs-target="#detailModal-{{ $item->uuid }}">
                                                        <i class="bi bi-eye"></i> Detail
                                                    </a>
                                                </li>
                                                @can ('superadmin', auth()->user())
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                            data-bs-target="#editModal-{{ $item->uuid }}">
                                                            <i class="bi bi-pencil"></i> Edit
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
                                                                <i class="bi bi-trash"></i> Hapus
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>

                                        <!-- Detail Modal -->
                                        <div class="modal fade" id="detailModal-{{ $item->uuid }}" tabindex="-1"
                                            aria-labelledby="detailModalLabel-{{ $item->uuid }}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="detailModalLabel-{{ $item->uuid }}">
                                                            Detail Riwayat Perbaikan
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <h6 class="fw-bold">Informasi Umum</h6>
                                                                <table class="table table-sm table-borderless">
                                                                    <tr>
                                                                        <td style="width: 40%">Tanggal</td>
                                                                        <td>: {{ $item->created_at->format('d M Y H:i') }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Jenis Maintenance</td>
                                                                        <td>: {{ $item->jenis_maintenance }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Teknisi</td>
                                                                        <td>: {{ $item->teknisi }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Biaya</td>
                                                                        <td>:
                                                                            @if($item->biaya_maintenance)
                                                                                Rp
                                                                                {{ number_format($item->biaya_maintenance, 0, ',', '.') }}
                                                                            @else
                                                                                -
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6 class="fw-bold">Hasil Perbaikan</h6>
                                                                <table class="table table-sm table-borderless">
                                                                    <tr>
                                                                        <td style="width: 40%">Status</td>
                                                                        <td>:
                                                                            @if($item->hasil_maintenance == 'Berhasil')
                                                                                <span class="badge bg-success">Berhasil</span>
                                                                            @elseif($item->hasil_maintenance == 'Sebagian')
                                                                                <span
                                                                                    class="badge bg-warning text-dark">Sebagian</span>
                                                                            @elseif($item->hasil_maintenance == 'Gagal')
                                                                                <span class="badge bg-danger">Gagal</span>
                                                                            @else
                                                                                <span
                                                                                    class="badge bg-secondary">{{ $item->hasil_maintenance }}</span>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Komponen Diganti</td>
                                                                        <td>: {{ $item->komponen_diganti ?: 'Tidak ada' }}</td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <h6 class="fw-bold">Keterangan</h6>
                                                            <div class="border rounded p-3 bg-light">
                                                                {!! nl2br(e($item->keterangan)) ?: 'Tidak ada keterangan' !!}
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <h6 class="fw-bold">Rekomendasi</h6>
                                                            <div class="border rounded p-3 bg-light">
                                                                {!! nl2br(e($item->rekomendasi)) ?: 'Tidak ada rekomendasi' !!}
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
                                            aria-labelledby="editModalLabel-{{ $item->uuid }}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editModalLabel-{{ $item->uuid }}">
                                                            Edit Riwayat Perbaikan
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <form
                                                        action="{{ route('komputer.riwayat.update', [$komputer->uuid, $item->uuid]) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="uuid"
                                                            value="{{ $komputer->uuid }}">
                                                        <div class="modal-body">
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label for="edit_jenis_maintenance_{{ $item->uuid }}"
                                                                            class="form-label">Jenis Maintenance <span
                                                                                class="text-danger">*</span></label>
                                                                        <select class="form-select"
                                                                            id="edit_jenis_maintenance_{{ $item->uuid }}"
                                                                            name="jenis_maintenance" required>
                                                                            <option value="">Pilih jenis maintenance</option>
                                                                            <option value="Perbaikan Hardware" {{ $item->jenis_maintenance == 'Perbaikan Hardware' ? 'selected' : '' }}>Perbaikan Hardware</option>
                                                                            <option value="Perbaikan Software" {{ $item->jenis_maintenance == 'Perbaikan Software' ? 'selected' : '' }}>Perbaikan Software</option>
                                                                            <option value="Pemeliharaan Rutin" {{ $item->jenis_maintenance == 'Pemeliharaan Rutin' ? 'selected' : '' }}>Pemeliharaan Rutin</option>
                                                                            <option value="Upgrade" {{ $item->jenis_maintenance == 'Upgrade' ? 'selected' : '' }}>Upgrade</option>
                                                                            <option value="Lainnya" {{ $item->jenis_maintenance == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="edit_teknisi_{{ $item->uuid }}"
                                                                            class="form-label">Teknisi <span
                                                                                class="text-danger">*</span></label>
                                                                        <input type="text" class="form-control"
                                                                            id="edit_teknisi_{{ $item->uuid }}" name="teknisi"
                                                                            value="{{ $item->teknisi }}" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="edit_komponen_diganti_{{ $item->uuid }}"
                                                                            class="form-label">Komponen yang Diganti</label>
                                                                        <input type="text" class="form-control"
                                                                            id="edit_komponen_diganti_{{ $item->uuid }}"
                                                                            name="komponen_diganti"
                                                                            value="{{ $item->komponen_diganti }}">
                                                                        <div class="form-text">Kosongkan jika tidak ada komponen
                                                                            yang diganti</div>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="edit_biaya_maintenance_{{ $item->uuid }}"
                                                                            class="form-label">Biaya Maintenance (Rp)</label>
                                                                        <input type="number" class="form-control"
                                                                            id="edit_biaya_maintenance_{{ $item->uuid }}"
                                                                            name="biaya_maintenance"
                                                                            value="{{ $item->biaya_maintenance }}">
                                                                        <div class="form-text">Kosongkan jika tidak ada biaya
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label for="edit_hasil_maintenance_{{ $item->uuid }}"
                                                                            class="form-label">Hasil Maintenance <span
                                                                                class="text-danger">*</span></label>
                                                                        <select class="form-select"
                                                                            id="edit_hasil_maintenance_{{ $item->uuid }}"
                                                                            name="hasil_maintenance" required>
                                                                            <option value="">Pilih hasil maintenance</option>
                                                                            <option value="Berhasil" {{ $item->hasil_maintenance == 'Berhasil' ? 'selected' : '' }}>Berhasil</option>
                                                                            <option value="Sebagian" {{ $item->hasil_maintenance == 'Sebagian' ? 'selected' : '' }}>Sebagian</option>
                                                                            <option value="Gagal" {{ $item->hasil_maintenance == 'Gagal' ? 'selected' : '' }}>Gagal</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="edit_keterangan_{{ $item->uuid }}"
                                                                            class="form-label">Keterangan <span
                                                                                class="text-danger">*</span></label>
                                                                        <textarea class="form-control"
                                                                            id="edit_keterangan_{{ $item->uuid }}"
                                                                            name="keterangan" rows="3"
                                                                            required>{{ $item->keterangan }}</textarea>
                                                                        <div class="form-text">Jelaskan permasalahan dan
                                                                            tindakan yang dilakukan</div>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="edit_rekomendasi_{{ $item->uuid }}"
                                                                            class="form-label">Rekomendasi</label>
                                                                        <textarea class="form-control"
                                                                            id="edit_rekomendasi_{{ $item->uuid }}"
                                                                            name="rekomendasi"
                                                                            rows="3">{{ $item->rekomendasi }}</textarea>
                                                                        <div class="form-text">Rekomendasi untuk pemeliharaan ke
                                                                            depan</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Simpan
                                                                Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $item->created_at->format('d M Y') }}</td>
                                    <td>{{ $item->jenis_maintenance }}</td>
                                    <td>{{ $item->teknisi }}</td>
                                    <td>{{ $item->komponen_diganti ?: 'Tidak ada' }}</td>
                                    <td>
                                        @if($item->biaya_maintenance)
                                            Rp {{ number_format($item->biaya_maintenance, 0, ',', '.') }}
                                        @else
                                            -
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
                                    <td colspan="8" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-clipboard-x text-secondary" style="font-size: 2rem;"></i>
                                            <h5 class="mt-3">Belum ada riwayat perbaikan</h5>
                                            <p class="text-secondary">Belum ada riwayat perbaikan yang tersedia untuk komputer
                                                ini</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $riwayat->links('pagination::bootstrap-5') }}
                </div>

                <!-- Pagination Info -->
                <div class="text-center text-muted mt-2">
                    <small>
                        Menampilkan {{ $riwayat->firstItem() ?? 0 }} sampai {{ $riwayat->lastItem() ?? 0 }} dari
                        {{ $riwayat->total() }} data
                    </small>
                </div>
            </div>
        </div>
    </div>

    @include('admin.riwayat_perbaikan.tambah')
@endsection