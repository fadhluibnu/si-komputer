<!-- Modal Tambah Riwayat -->
<div class="modal fade" id="tambahRiwayatModal" tabindex="-1" aria-labelledby="tambahRiwayatModalLabel"
    aria-hidden="true" style="z-index: 10500;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahRiwayatModalLabel">Tambah Riwayat Perbaikan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('komputer.riwayat.store', $komputer->uuid) }}" method="POST">
                @csrf
                <input type="hidden" name="uuid" value="{{ $komputer->uuid }}">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jenis_maintenance" class="form-label">Jenis Maintenance <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="jenis_maintenance" name="jenis_maintenance" required>
                                    <option value="" selected disabled>Pilih jenis maintenance</option>
                                    <option value="Perbaikan Hardware">Perbaikan Hardware</option>
                                    <option value="Perbaikan Software">Perbaikan Software</option>
                                    <option value="Pemeliharaan Rutin">Pemeliharaan Rutin</option>
                                    <option value="Upgrade">Upgrade</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="teknisi" class="form-label">Teknisi <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="teknisi" name="teknisi" required>
                            </div>
                            <div class="mb-3">
                                <label for="komponen_diganti" class="form-label">Komponen yang Diganti</label>
                                <input type="text" class="form-control" id="komponen_diganti" name="komponen_diganti">
                                <div class="form-text">Kosongkan jika tidak ada komponen yang diganti</div>
                            </div>
                            <div class="mb-3">
                                <label for="biaya_maintenance" class="form-label">Biaya Maintenance (Rp)</label>
                                <input type="number" class="form-control" id="biaya_maintenance"
                                    name="biaya_maintenance" value="0">
                                <div class="form-text">Kosongkan jika tidak ada biaya</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="hasil_maintenance" class="form-label">Hasil Maintenance <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="hasil_maintenance" name="hasil_maintenance" required>
                                    <option value="" selected disabled>Pilih hasil maintenance</option>
                                    <option value="Berhasil">Berhasil</option>
                                    <option value="Sebagian">Sebagian</option>
                                    <option value="Gagal">Gagal</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="keterangan" class="form-label">Keterangan <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="3"
                                    required></textarea>
                                <div class="form-text">Jelaskan permasalahan dan tindakan yang dilakukan</div>
                            </div>
                            <div class="mb-3">
                                <label for="rekomendasi" class="form-label">Rekomendasi</label>
                                <textarea class="form-control" id="rekomendasi" name="rekomendasi" rows="3"></textarea>
                                <div class="form-text">Rekomendasi untuk pemeliharaan ke depan</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>