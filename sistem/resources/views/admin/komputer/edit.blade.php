@extends('admin.components.layout')

@section('content')
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col">
                <h2 class="border-bottom pb-2">
                    <i class="bi bi-pencil-square text-primary"></i> Edit Data Perangkat Komputer
                </h2>
                <p class="text-muted">Kode Barang: {{ $komputer->kode_barang }}</p>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Error!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Error!</strong>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <form id="formEditPerangkat" action="{{ route('komputer.update', $komputer->uuid) }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="old_kode_barang" value="{{ $komputer->kode_barang }}">

                    {{-- Data Identifikasi and Spesifikasi Section (No changes here) --}}
                    <div class="row mb-4">
                        {{-- Kolom Kiri: Data Identifikasi Perangkat --}}
                        <div class="col-md-6">
                            <h4 class="card-title mb-3">Data Identifikasi Perangkat</h4>
                            <div class="mb-3">
                                <label for="ruangan_id" class="form-label">Nama Ruangan <span class="text-danger">*</span></label>
                                <select class="form-select @error('ruangan_id') is-invalid @enderror" id="ruangan_id" name="ruangan_id" required>
                                    <option value="" disabled>-- Pilih Ruangan --</option>
                                    @foreach ($ruangans as $ruangan)
                                        <option value="{{ $ruangan->id }}" {{ old('ruangan_id', $komputer->ruangan_id) == $ruangan->id ? 'selected' : '' }}>{{ $ruangan->nama_ruangan }}</option>
                                    @endforeach
                                </select>
                                @error('ruangan_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Nama ruangan wajib diisi</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="kode_barang" class="form-label">Kode Barang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('kode_barang') is-invalid @enderror" id="kode_barang" name="kode_barang" value="{{ old('kode_barang', $komputer->kode_barang) }}" placeholder="Contoh: ESDM-PC-001" required>
                                @error('kode_barang')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Kode barang wajib diisi</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="nama_komputer" class="form-label">Nama Komputer </label>
                                <input type="text" class="form-control @error('nama_komputer') is-invalid @enderror" id="nama_komputer" name="nama_komputer" value="{{ old('nama_komputer', $komputer->nama_komputer) }}" placeholder="Contoh: PC-ADMIN-01">
                                @error('nama_komputer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Nama komputer wajib diisi</div>
                                @enderror
                            </div>
                             <div class="mb-3">
                                <label for="merek_komputer" class="form-label">Merek Komputer <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('merek_komputer') is-invalid @enderror" id="merek_komputer" name="merek_komputer" value="{{ old('merek_komputer', $komputer->merek_komputer) }}" placeholder="" required>
                                @error('merek_komputer')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Merek komputer wajib diisi</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="tahun_pengadaan" class="form-label">Tahun Pengadaan <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('tahun_pengadaan') is-invalid @enderror" id="tahun_pengadaan" name="tahun_pengadaan" min="2000" max="{{ date('Y') }}" value="{{ old('tahun_pengadaan', $komputer->tahun_pengadaan) }}" required>
                                @error('tahun_pengadaan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Tahun pengadaan wajib diisi dengan format yang benar</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="nama_pengguna_sekarang" class="form-label">Nama Pengguna Sekarang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_pengguna_sekarang') is-invalid @enderror" id="nama_pengguna_sekarang" name="nama_pengguna_sekarang" value="{{ old('nama_pengguna_sekarang', $komputer->nama_pengguna_sekarang) }}" placeholder="Contoh: Nama Pegawai" required>
                                @error('nama_pengguna_sekarang')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Nama pengguna wajib diisi</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="penggunaan_sekarang" class="form-label">Penggunaan Sekarang <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('penggunaan_sekarang') is-invalid @enderror" id="penggunaan_sekarang" name="penggunaan_sekarang" rows="3" required>{{ old('penggunaan_sekarang', $komputer->penggunaan_sekarang) }}</textarea>
                                @error('penggunaan_sekarang')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Penggunaan sekarang wajib diisi</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Kolom Kanan: Spesifikasi Perangkat --}}
                        <div class="col-md-6">
                            <h4 class="card-title mb-3">Spesifikasi Perangkat</h4>
                             <div class="mb-3">
                                <label for="spesifikasi_processor" class="form-label">Processor <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('spesifikasi_processor') is-invalid @enderror" id="spesifikasi_processor" name="spesifikasi_processor" placeholder="Contoh: Intel Core i5-10400 2.9GHz" value="{{ old('spesifikasi_processor', $komputer->spesifikasi_processor) }}" required>
                                @error('spesifikasi_processor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Spesifikasi processor wajib diisi</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="spesifikasi_ram" class="form-label">RAM <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('spesifikasi_ram') is-invalid @enderror" id="spesifikasi_ram" name="spesifikasi_ram" placeholder="Contoh: 8GB DDR4 2666MHz" value="{{ old('spesifikasi_ram', $komputer->spesifikasi_ram) }}" required>
                                @error('spesifikasi_ram')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Spesifikasi RAM wajib diisi</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="spesifikasi_vga" class="form-label">VGA</label>
                                <input type="text" class="form-control @error('spesifikasi_vga') is-invalid @enderror" id="spesifikasi_vga" name="spesifikasi_vga" placeholder="Contoh: NVIDIA GeForce GTX 1650 4GB" value="{{ old('spesifikasi_vga', $komputer->spesifikasi_vga) }}">
                                @error('spesifikasi_vga')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="spesifikasi_penyimpanan" class="form-label">Penyimpanan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('spesifikasi_penyimpanan') is-invalid @enderror" id="spesifikasi_penyimpanan" name="spesifikasi_penyimpanan" placeholder="Contoh: SSD 256GB + HDD 1TB" value="{{ old('spesifikasi_penyimpanan', $komputer->spesifikasi_penyimpanan) }}" required>
                                @error('spesifikasi_penyimpanan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Informasi penyimpanan wajib diisi</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="sistem_operasi" class="form-label">Sistem Operasi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('sistem_operasi') is-invalid @enderror" id="sistem_operasi" name="sistem_operasi" placeholder="Contoh: Windows 11 Pro" value="{{ old('sistem_operasi', $komputer->sistem_operasi) }}" required>
                                @error('sistem_operasi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Sistem operasi wajib diisi</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="kesesuaian_pc" class="form-label">Kesesuaian PC dalam Mendukung Pekerjaan</label>
                                <select class="form-select @error('kesesuaian_pc') is-invalid @enderror" id="kesesuaian_pc" name="kesesuaian_pc">
                                    <option value="Sangat Sesuai" {{ old('kesesuaian_pc', $komputer->kesesuaian_pc) == 'Sangat Sesuai' ? 'selected' : '' }}>Sangat Sesuai</option>
                                    <option value="Sesuai" {{ old('kesesuaian_pc', $komputer->kesesuaian_pc) == 'Sesuai' ? 'selected' : '' }}>Sesuai</option>
                                    <option value="Kurang Sesuai" {{ old('kesesuaian_pc', $komputer->kesesuaian_pc) == 'Kurang Sesuai' ? 'selected' : '' }}>Kurang Sesuai</option>
                                    <option value="Tidak Sesuai" {{ old('kesesuaian_pc', $komputer->kesesuaian_pc) == 'Tidak Sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                                </select>
                                @error('kesesuaian_pc')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Kondisi dan Pemeliharaan Section (No changes here) --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="card-title mb-3">Kondisi dan Pemeliharaan</h4>
                        </div>
                        <div class="row col-12">
                            <div class="mb-3">
                                <label for="kondisi_komputer" class="form-label">Keterangan Kondisi Komputer Saat Ini <span class="text-danger">*</span></label>
                                <select class="form-select @error('kondisi_komputer') is-invalid @enderror" id="kondisi_komputer" name="kondisi_komputer" required>
                                    <option value="" disabled>Pilih kondisi</option>
                                    <option value="Sangat Baik" {{ old('kondisi_komputer', $komputer->kondisi_komputer) == 'Sangat Baik' ? 'selected' : '' }}>Sangat Baik</option>
                                    <option value="Baik" {{ old('kondisi_komputer', $komputer->kondisi_komputer) == 'Baik' ? 'selected' : '' }}>Baik</option>
                                    <option value="Cukup" {{ old('kondisi_komputer', $komputer->kondisi_komputer) == 'Cukup' ? 'selected' : '' }}>Cukup</option>
                                    <option value="Kurang" {{ old('kondisi_komputer', $komputer->kondisi_komputer) == 'Kurang' ? 'selected' : '' }}>Kurang</option>
                                    <option value="Rusak" {{ old('kondisi_komputer', $komputer->kondisi_komputer) == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                                </select>
                                @error('kondisi_komputer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Kondisi komputer wajib dipilih</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="keterangan_kondisi" class="form-label">Detail Kondisi</label>
                                <textarea class="form-control @error('keterangan_kondisi') is-invalid @enderror" id="keterangan_kondisi" name="keterangan_kondisi" rows="3" placeholder="Berikan detail kondisi perangkat saat ini...">{{ old('keterangan_kondisi', $komputer->keterangan_kondisi) }}</textarea>
                                @error('keterangan_kondisi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="card-title mb-3">Media dan Dokumentasi</h4>
                        </div>

                        <div class="col-md-12 mb-4">
                            <h5><i class="bi bi-images me-2"></i>File Media Saat Ini</h5>
                            @if($komputer->galleries->isEmpty())
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Tidak ada file media yang tersedia untuk perangkat ini.
                                </div>
                            @else
                                <div class="row existing-images">
                                    @foreach($komputer->galleries as $index => $gallery)
                                        <div class="col-md-3 col-sm-6 mb-3">
                                            <div class="card h-100 shadow-sm">
                                                <div class="position-relative">
                                                    {{-- Note: This part might need backend adjustment to differentiate PDFs --}}
                                                    <img src="{{ asset('storage/' . $gallery->image_path) }}" 
                                                        alt="Image {{ $index + 1 }}" class="card-img-top" style="height: 150px; object-fit: cover;">
                                                    <div class="form-check position-absolute top-0 start-0 m-2">
                                                        <input class="form-check-input" type="checkbox" name="delete_images[]" value="{{ $gallery->id }}" id="deleteImage{{ $gallery->id }}">
                                                        <label class="form-check-label visually-hidden" for="deleteImage{{ $gallery->id }}">
                                                            Hapus file ini
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="card-body p-2">
                                                    <small class="text-muted">File {{ $index + 1 }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="alert alert-info mt-2">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Centang kotak pada file untuk menghapusnya saat menyimpan perubahan.
                                </div>
                            @endif
                        </div>

                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="fileInput" class="form-label fw-bold">Tambah File Baru (Foto/PDF)</label>

                                <div class="upload-area mb-3">
                                    <div class="upload-container text-center p-4 border rounded-3 bg-light position-relative">
                                        <input class="form-control" type="file" id="fileInput" name="foto[]" 
                                               accept="image/png, image/jpeg, image/jpg, application/pdf" multiple>

                                        <div class="upload-icon mb-3">
                                            <i class="bi bi-cloud-arrow-up" style="font-size: 3rem; color: #6c757d;"></i>
                                        </div>

                                        <h5>Unggah File Dokumentasi</h5>
                                        <p class="text-muted mb-3">Pilih atau seret file (JPG, PNG, PDF) ke area ini</p>

                                        <button type="button" class="btn btn-primary px-4 py-2" id="selectFilesBtn">
                                            <i class="bi bi-folder-plus me-2"></i> Pilih File
                                        </button>

                                        <div class="mt-2 small text-muted">
                                            Format: JPG, PNG, JPEG, PDF (Maks. 5MB per file)
                                        </div>
                                    </div>
                                </div>

                                @error('foto')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @error('foto.*')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                                <div id="preview-area" class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0"><i class="bi bi-file-earmark-medical me-2"></i>Pratinjau File Baru</h6>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="addMoreFilesBtn" style="display: none;">
                                            <i class="bi bi-plus-circle me-1"></i> Tambah File Lagi
                                        </button>
                                    </div>
                                    <div class="file-preview-container">
                                        <div class="row" id="previewContainer">
                                            <div class="col-12 text-center p-3 text-muted empty-preview">
                                                <p><i class="bi bi-files me-2"></i>Belum ada file baru yang dipilih</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="upload-status" style="display: none;">
                                    <div class="card border-primary">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1 text-primary"><i class="bi bi-info-circle me-2"></i>Status Unggahan</h6>
                                                    <p class="mb-0 small" id="fileCount">0 file dipilih</p>
                                                    <p class="mb-0 small" id="totalSize">Total: 0 KB</p>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger" id="clearAllFilesBtn">
                                                    <i class="bi bi-trash me-1"></i> Hapus Semua
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 text-center">
                            <hr>
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-save"></i> Perbarui Data Perangkat
                            </button>
                            <a href="{{ route('komputer.show', $komputer->kode_barang) }}" class="btn btn-outline-secondary btn-lg ms-2">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Form validation with Bootstrap
        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        // File upload functionality
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('fileInput');
            const selectFilesBtn = document.getElementById('selectFilesBtn');
            const addMoreFilesBtn = document.getElementById('addMoreFilesBtn');
            const clearAllFilesBtn = document.getElementById('clearAllFilesBtn');
            const previewContainer = document.getElementById('previewContainer');
            const uploadStatus = document.getElementById('upload-status');
            const fileCountElement = document.getElementById('fileCount');
            const totalSizeElement = document.getElementById('totalSize');
            const uploadContainer = document.querySelector('.upload-container');
            
            let selectedFiles = [];

            if (fileInput) {
                fileInput.className += ' visually-hidden';
                selectFilesBtn.addEventListener('click', () => fileInput.click());
                addMoreFilesBtn.addEventListener('click', () => fileInput.click());
                fileInput.addEventListener('change', () => processFiles(Array.from(fileInput.files)));
            }

            if (clearAllFilesBtn) {
                clearAllFilesBtn.addEventListener('click', () => {
                    if (confirm('Apakah Anda yakin ingin menghapus semua file baru yang dipilih?')) {
                        selectedFiles = [];
                        updateFileInput();
                        updatePreview();
                        updateUploadStatus();
                    }
                });
            }

            // Drag and Drop functionality
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadContainer.addEventListener(eventName, preventDefaults, false);
            });
            ['dragenter', 'dragover'].forEach(eventName => {
                uploadContainer.addEventListener(eventName, () => uploadContainer.classList.add('highlight'), false);
            });
            ['dragleave', 'drop'].forEach(eventName => {
                uploadContainer.addEventListener(eventName, () => uploadContainer.classList.remove('highlight'), false);
            });
            uploadContainer.addEventListener('drop', e => processFiles(Array.from(e.dataTransfer.files)), false);

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            function processFiles(files) {
                const validFiles = [];
                const maxSize = 5 * 1024 * 1024; // 5MB
                const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'application/pdf'];

                files.forEach(file => {
                    if (!allowedTypes.includes(file.type)) {
                        showAlert(`Tipe file "${file.name}" tidak didukung. Harap pilih JPG, PNG, atau PDF.`, 'warning');
                        return;
                    }
                    if (file.size > maxSize) {
                        showAlert(`File "${file.name}" terlalu besar. Ukuran maksimal adalah 5MB.`, 'warning');
                        return;
                    }
                    if (selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                        showAlert(`File "${file.name}" sudah dipilih.`, 'info');
                        return;
                    }
                    validFiles.push(file);
                });

                if (validFiles.length > 0) {
                    selectedFiles.push(...validFiles);
                    updateFileInput();
                    updatePreview();
                    updateUploadStatus();
                    addMoreFilesBtn.style.display = 'inline-block';
                }
            }

            function updateFileInput() {
                if (!fileInput) return;
                try {
                    const dataTransfer = new DataTransfer();
                    selectedFiles.forEach(file => dataTransfer.items.add(file));
                    fileInput.files = dataTransfer.files;
                } catch (error) {
                    console.error('Error updating file input:', error);
                }
            }

            function updatePreview() {
                if (!previewContainer) return;
                previewContainer.innerHTML = '';

                if (selectedFiles.length === 0) {
                    previewContainer.innerHTML = `
                        <div class="col-12 text-center p-3 text-muted empty-preview">
                            <p><i class="bi bi-files me-2"></i>Belum ada file baru yang dipilih</p>
                        </div>`;
                    addMoreFilesBtn.style.display = 'none';
                    return;
                }

                selectedFiles.forEach((file, index) => {
                    const col = document.createElement('div');
                    col.className = 'col-md-3 col-sm-6 mb-3';
                    const isImage = file.type.startsWith('image/');
                    
                    const filePreviewHTML = isImage
                        ? `<img class="card-img-top preview-file" alt="${file.name}">`
                        : `<div class="preview-file-icon d-flex align-items-center justify-content-center">
                               <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 4rem;"></i>
                           </div>`;

                    col.innerHTML = `
                        <div class="card h-100 shadow-sm">
                            <div class="position-relative">
                                ${filePreviewHTML}
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 rounded-circle btn-remove" data-index="${index}">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            <div class="card-body p-2">
                                <p class="card-text mb-1 text-truncate small fw-semibold" title="${file.name}">${file.name}</p>
                                <p class="card-text text-muted small">${formatFileSize(file.size)}</p>
                            </div>
                        </div>`;

                    if (isImage) {
                        const reader = new FileReader();
                        reader.onload = e => {
                            const img = col.querySelector('img');
                            if (img) img.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }

                    previewContainer.appendChild(col);
                    col.querySelector('.btn-remove').addEventListener('click', function() {
                        removeFile(parseInt(this.dataset.index));
                    });
                });
            }

            function updateUploadStatus() {
                uploadStatus.style.display = selectedFiles.length > 0 ? 'block' : 'none';
                if (selectedFiles.length > 0) {
                    const totalSize = selectedFiles.reduce((acc, file) => acc + file.size, 0);
                    fileCountElement.textContent = `${selectedFiles.length} file dipilih`;
                    totalSizeElement.textContent = `Total: ${formatFileSize(totalSize)}`;
                }
            }

            function removeFile(index) {
                selectedFiles.splice(index, 1);
                updateFileInput();
                updatePreview();
                updateUploadStatus();
            }

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            function showAlert(message, type = 'info') {
                const alertContainer = document.createElement('div');
                alertContainer.className = `alert alert-${type} alert-dismissible fade show mt-2`;
                alertContainer.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
                document.querySelector('.upload-area').insertAdjacentElement('afterend', alertContainer);
                setTimeout(() => alertContainer.remove(), 5000);
            }
        });
    </script>

    <style>
        .upload-container {
            border: 2px dashed #ccc !important;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        #fileInput {
            position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
            overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;
        }
        .upload-container:hover, .upload-container.highlight {
            border-color: #0d6efd !important;
            background-color: rgba(13, 110, 253, 0.05);
        }
        .file-preview-container {
            min-height: 120px; border: 1px solid #dee2e6;
            border-radius: 8px; padding: 15px; background-color: #f8f9fa;
        }
        .preview-file, .preview-file-icon {
            width: 100%; height: 150px;
            object-fit: cover;
            border-top-left-radius: calc(0.375rem - 1px);
            border-top-right-radius: calc(0.375rem - 1px);
        }
        .preview-file-icon { background-color: #f1f3f5; }
        .card { transition: transform 0.2s ease; }
        .card:hover { transform: translateY(-5px); }
        .btn-remove {
            opacity: 0.8; transition: opacity 0.2s ease;
            width: 30px; height: 30px; padding: 0; 
            display: flex; align-items: center; justify-content: center;
        }
        .card:hover .btn-remove { opacity: 1; }
        .existing-images .form-check-input {
            width: 20px; height: 20px; background-color: rgba(255, 255, 255, 0.8); border-color: #dc3545;
        }
        .existing-images .form-check-input:checked { background-color: #dc3545; border-color: #dc3545; }
        .existing-images .card:has(.form-check-input:checked) {
            border-color: #dc3545; background-color: rgba(220, 53, 69, 0.05);
        }
        .existing-images .card:has(.form-check-input:checked) .card-img-top { opacity: 0.5; }
    </style>
@endsection