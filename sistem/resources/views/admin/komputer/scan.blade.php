@extends('admin.components.public_layout')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h2 class="mb-1">Informasi Komputer</h2>
                        <p class="text-muted">Hasil scan barcode komputer</p>
                        <a href="{{ route('komputer.show', $komputer->uuid) }}" class="btn btn-primary">
                            <i class="bi bi-eye"></i> Lihat Detail Lengkap
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-box-arrow-in-right"></i> Login Admin
                        </a>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Anda dapat mengakses halaman ini kapanpun dengan cara scan barcode/QR code pada komputer.
                    </div>
                    
                    @if(isset($qrTextContent))
                    <div class="mb-4">
                        <div class="card border">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0"><i class="bi bi-qr-code"></i> Konten QR Code</h5>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0" style="white-space: pre-wrap; font-size: 0.9rem;">{{ $qrTextContent }}</pre>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="row">
                        <!-- Detail Komputer Section -->
                        <div class="col-md-6 mb-4">
                            <div class="card border h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="bi bi-pc-display"></i> Detail Komputer</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td style="width: 40%">Nama Komputer</td>
                                            <td>: <strong>{{ $komputer->nama_komputer }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Kode Aset</td>
                                            <td>: <strong>{{ $komputer->kode_barang }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Merek</td>
                                            <td>: {{ $komputer->merek_komputer }}</td>
                                        </tr>
                                        <tr>
                                            <td>Tahun Pengadaan</td>
                                            <td>: {{ $komputer->tahun_pengadaan }}</td>
                                        </tr>
                                        <tr>
                                            <td>Lokasi</td>
                                            <td>: {{ $komputer->ruangan->nama_ruangan }}</td>
                                        </tr>
                                        <tr>
                                            <td>Pengguna</td>
                                            <td>: {{ $komputer->nama_pengguna_sekarang ?: 'Tidak ditentukan' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Penggunaan</td>
                                            <td>: {{ $komputer->penggunaan_sekarang }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Kondisi Komputer Section -->
                        <div class="col-md-6 mb-4">
                            <div class="card border h-100">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Kondisi Komputer</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td style="width: 40%">Kondisi</td>
                                            <td>: 
                                                @if($komputer->kondisi_komputer == 'Baik')
                                                    <span class="badge bg-success">Baik</span>
                                                @elseif($komputer->kondisi_komputer == 'Rusak Ringan')
                                                    <span class="badge bg-warning text-dark">Rusak Ringan</span>
                                                @elseif($komputer->kondisi_komputer == 'Rusak Berat')
                                                    <span class="badge bg-danger">Rusak Berat</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $komputer->kondisi_komputer }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Keterangan</td>
                                            <td>: {{ $komputer->keterangan_kondisi }}</td>
                                        </tr>
                                        <tr>
                                            <td>Kesesuaian</td>
                                            <td>: 
                                                @if($komputer->kesesuaian_pc == 'Sesuai')
                                                    <span class="badge bg-success">Sesuai</span>
                                                @elseif($komputer->kesesuaian_pc == 'Kurang Sesuai')
                                                    <span class="badge bg-warning text-dark">Kurang Sesuai</span>
                                                @elseif($komputer->kesesuaian_pc == 'Tidak Sesuai')
                                                    <span class="badge bg-danger">Tidak Sesuai</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $komputer->kesesuaian_pc }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>

                                    <div class="mt-3">
                                        <h6>Spesifikasi Komputer:</h6>
                                        <table class="table table-sm table-bordered">
                                            <tr>
                                                <td style="width: 40%"><i class="bi bi-cpu"></i> Processor</td>
                                                <td>{{ $komputer->spesifikasi_processor }}</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-memory"></i> RAM</td>
                                                <td>{{ $komputer->spesifikasi_ram }}</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-gpu-card"></i> VGA</td>
                                                <td>{{ $komputer->spesifikasi_vga ?: 'Integrated' }}</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-device-hdd"></i> Penyimpanan</td>
                                                <td>{{ $komputer->spesifikasi_penyimpanan }}</td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-windows"></i> Sistem Operasi</td>
                                                <td>{{ $komputer->sistem_operasi }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Riwayat Pemeliharaan Section -->
                        <div class="col-12">
                            <div class="card border">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="bi bi-tools"></i> Riwayat Pemeliharaan</h5>
                                </div>
                                <div class="card-body">
                                    @if($komputer->riwayatPerbaikan->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Tanggal</th>
                                                        <th>Jenis</th>
                                                        <th>Teknisi</th>
                                                        <th>Keterangan</th>
                                                        <th>Hasil</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($komputer->riwayatPerbaikan->take(5) as $riwayat)
                                                        <tr>
                                                            <td>{{ $riwayat->created_at->format('d M Y') }}</td>
                                                            <td>{{ $riwayat->jenis_maintenance }}</td>
                                                            <td>{{ $riwayat->teknisi }}</td>
                                                            <td>{{ Str::limit($riwayat->keterangan, 50) }}</td>
                                                            <td>
                                                                @if($riwayat->hasil_maintenance == 'Berhasil')
                                                                    <span class="badge bg-success">Berhasil</span>
                                                                @elseif($riwayat->hasil_maintenance == 'Sebagian')
                                                                    <span class="badge bg-warning text-dark">Sebagian</span>
                                                                @elseif($riwayat->hasil_maintenance == 'Gagal')
                                                                    <span class="badge bg-danger">Gagal</span>
                                                                @else
                                                                    <span class="badge bg-secondary">{{ $riwayat->hasil_maintenance }}</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        @if($komputer->riwayatPerbaikan->count() > 5)
                                            <div class="text-center mt-3">
                                                <a href="{{ route('komputer.riwayat.index', $komputer->uuid) }}" class="btn btn-outline-success">
                                                    <i class="bi bi-clock-history"></i> Lihat Semua Riwayat Pemeliharaan
                                                </a>
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-center py-4">
                                            <i class="bi bi-clipboard-x text-secondary" style="font-size: 2rem;"></i>
                                            <p class="mt-2">Belum ada riwayat pemeliharaan untuk komputer ini</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Foto Komputer Section -->
                    @if($komputer->galleries->count() > 0)
                        <div class="mt-4">
                            <h5 class="border-bottom pb-2"><i class="bi bi-images"></i> Foto Komputer</h5>
                            <div class="row">
                                @foreach($komputer->galleries->take(4) as $gallery)
                                    <div class="col-md-3 col-6 mb-3">
                                        <a href="{{ asset('storage/' . $gallery->image_path) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $gallery->image_path) }}" alt="Foto Komputer" class="img-thumbnail" style="height: 150px; object-fit: cover;">
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
