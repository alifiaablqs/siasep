@extends('layouts.app')

@section('title', isset($pageTitle) ? $pageTitle : 'Data Aset Perusahaan')

@php
    $showAdminActions = $showAdminActions ?? false;
@endphp

@section('content')
<div class="container-fluid px-1 py-0 mt-0">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">{{ isset($pageTitle) ? $pageTitle : 'Data Aset Perusahaan' }}</h3>
        <ul class="breadcrumbs d-flex align-items-center p-0 m-0" style="list-style: none;"> 
            <li class="nav-home d-flex align-items-center">
                <a href="{{ route('dashboard') }}" class="text-muted text-decoration-none d-flex align-items-center">
                    <i class="fas fa-home me-2" style="font-size: 15px;"></i>
                    <span style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Dashboard</span>                    
                </a>                
            </li>
            <li class="separator text-muted d-flex align-items-center px-2">
                <span style="font-size: 14px; position: relative; top: 2px;">-</span>
            </li>
            <li class="nav-item d-flex align-items-center">
                <span class="text-muted" style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">{{ isset($pageTitle) ? $pageTitle : 'Data Aset Perusahaan' }}</span>
            </li>
        </ul>
    </div>

    {{-- FILTER --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ url()->current() }}">
                
                <div class="alert alert-warning mb-3 border-0 shadow-sm rounded-3">
                    <i class="fas fa-exclamation-triangle fa-lg mt-1"></i>
                    <strong>Perhatian!</strong> Daftar aset di bawah ini telah terpengaruh oleh Sinkronisasi SIPO (seperti Departemen/Divisi). Silakan tekan tombol <strong>Lihat/Edit</strong> pada masing-masing aset untuk memperbarui struktur organisasinya.
                </div>

                {{-- Form Filter, Pencarian & Reset --}}
                <div class="row g-2 align-items-end">
                    {{-- Entries --}}
                    <div class="col-6 col-sm-4 col-md-1">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Entries</label>
                        <select name="per_page" class="form-select form-select-sm rounded-3 w-100" onchange="this.form.submit()">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    {{-- Pencarian --}}
                    <div class="col-12 col-sm-8 col-md-3">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Pencarian</label>
                        <div class="input-group input-group-sm input-group-focus rounded-3">
                            <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control border-0 shadow-none bg-transparent" placeholder="Cari nomor, nama atau klasifikasi..." value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Filter Kondisi --}}
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Kondisi Aset</label>
                        <select name="kondisi" class="form-select form-select-sm rounded-3 w-100" onchange="this.form.submit()">
                            <option value="">Semua Kondisi</option>
                            <option value="Baik" {{ request('kondisi') == 'Baik' ? 'selected' : '' }}>Baik</option>
                            <option value="Rusak" {{ request('kondisi') == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                            <option value="Bongkar" {{ request('kondisi') == 'Bongkar' ? 'selected' : '' }}>Bongkar</option>
                            <option value="Tidak Terpakai" {{ request('kondisi') == 'Tidak Terpakai' ? 'selected' : '' }}>Tidak Terpakai</option>
                            <option value="Hilang" {{ request('kondisi') == 'Hilang' ? 'selected' : '' }}>Hilang</option>
                            <option value="Tidak Teridentifikasi" {{ request('kondisi') == 'Tidak Teridentifikasi' ? 'selected' : '' }}>Tidak Teridentifikasi</option>
                        </select>
                    </div>

                    {{-- Filter Jenis Kategori --}}
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Jenis Kategori</label>
                        <select name="jenis_kategori_id" class="form-select form-select-sm rounded-3 w-100" onchange="this.form.submit()">
                            <option value="">Semua Jenis Kategori</option>
                            @foreach($jenisList as $jenis)
                                <option value="{{ $jenis->id }}" {{ request('jenis_kategori_id') == $jenis->id ? 'selected' : '' }}>{{ $jenis->nama_jenis }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Lokasi --}}
                    @if(!request()->routeIs('aset.pic'))
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Lokasi Aset</label>
                        <select name="lokasi" class="form-select form-select-sm rounded-3 w-100" onchange="this.form.submit()">
                            <option value="">Semua Lokasi</option>
                            @foreach($lokasis as $lokasi)
                                <option value="{{ $lokasi->lokasi_id }}" {{ request()->filled('lokasi') && request('lokasi') == $lokasi->lokasi_id ? 'selected' : '' }}>{{ $lokasi->nama_lokasi }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Filter Divisi --}}
                    @if(!request()->routeIs('aset.pic') && (auth()->user()->role_id_role == 1 || auth()->user()->isBagianUmum()) && !request()->boolean('filter_own_dept'))
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Divisi</label>
                        <select name="divisi_id" class="form-select form-select-sm rounded-3 w-100" onchange="this.form.submit()">
                            <option value="">Semua Divisi</option>
                            @foreach($divisis as $divisi)
                                <option value="{{ $divisi->id_divisi }}" {{ request('divisi_id') == $divisi->id_divisi ? 'selected' : '' }}>{{ $divisi->nm_divisi }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Filter Departemen --}}
                    @if(!request()->routeIs('aset.pic') && (auth()->user()->role_id_role == 1 || auth()->user()->isBagianUmum()) && !request()->boolean('filter_own_dept'))
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Departemen</label>
                        <select name="department_id" class="form-select form-select-sm rounded-3 w-100" onchange="this.form.submit()">
                            <option value="">Semua Departemen</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id_department }}" {{ request('department_id') == $dept->id_department ? 'selected' : '' }}>{{ $dept->name_department }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif



                </div>

            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            @if($showAdminActions)
                            <th width="40" class="text-center border-end">
                                <input class="form-check-input" type="checkbox" id="checkAllAset">
                            </th>
                            @endif
                            <th width="80" class="text-center">Kode QR</th>
                            <th>Kode Aset</th>
                            <th>Nama Aset</th>
                            <th class="text-center">Jenis Kategori</th>
                            <th>Lokasi Aset</th>
                            <th>Kondisi Aset</th>
                            <th>Status Aset</th>
                            <th width="180" class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($asets as $aset)
                            <tr>
                                @if($showAdminActions)
                                {{-- Checkbox --}}
                                <td class="text-center border-end">
                                    <input class="form-check-input aset-checkbox" type="checkbox" name="ids[]" value="{{ $aset->id }}" form="formCetakLabelSelected">
                                </td>
                                @endif

                                {{-- QR Code --}}
                                <td class="text-center">
                                    @php
                                        // Membuat URL dinamis ke halaman detail aset
                                        $urlDetail = route('aset.show', $aset->id);
                                    @endphp

                                    {{-- Generate QR Code berisi Link Detail (Ukuran disesuaikan untuk tabel) --}}
                                    {!! QrCode::size(60)->generate($urlDetail) !!}
                                </td>

                                {{-- Data Aset --}}
                                <td>{{ $aset->nomor_aset }}</td>
                                <td>
                                    {{ $aset->nama_aset }}
                                    @if($aset->hasVerificationIssues())
                                        <div class="mt-1">
                                            @foreach($aset->getVerificationBadges() as $badge)
                                                <span class="badge bg-warning text-dark border rounded-pill px-2" style="font-size: 0.65rem;" title="Data SIPO tidak aktif. Segera sesuaikan {{ $badge }} aset.">⚠️ Cek {{ $badge }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $aset->kategoriAset->tipe_badge_color ?? 'secondary' }}">
                                        {{ $aset->kategoriAset->tipe_label ?? '-' }}
                                    </span>
                                </td>
                                <td>{{ $aset->lokasi->nama_lokasi ?? $aset->lokasi->nm_lokasi_aset ?? '-' }}</td>
                                <td>
                                    @php
                                        $kondisi = $aset->status_kondisi;
                                    @endphp
                                    @if($kondisi == 'Baik')
                                        <span class="badge bg-success rounded-pill px-3">Baik</span>
                                    @elseif($kondisi == 'Rusak')
                                        <span class="badge bg-danger rounded-pill px-3">Rusak</span>
                                    @elseif($kondisi == 'Bongkar')
                                        <span class="badge bg-warning text-white rounded-pill px-3">Bongkar</span>
                                    @elseif($kondisi == 'Tidak Terpakai')
                                        <span class="badge bg-secondary rounded-pill px-3">Tidak Terpakai</span>
                                    @elseif($kondisi == 'Hilang')
                                        <span class="badge bg-dark rounded-pill px-3">Hilang</span>
                                    @elseif($kondisi == 'Tidak Teridentifikasi')
                                        <span class="badge bg-dark rounded-pill px-3">Tidak Teridentifikasi</span>
                                    @else
                                        <span class="badge bg-light text-white border rounded-pill px-3">{{ $kondisi ?? 'Lainnya' }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($aset->status_aset == 'Aktif')
                                        <span class="badge bg-success rounded-pill px-3">Aktif</span>
                                    @elseif($aset->status_aset == 'Dalam Perbaikan')
                                        <span class="badge bg-warning text-white rounded-pill px-3">Perbaikan</span>
                                    @elseif($aset->status_aset == 'Dipinjam')
                                        <span class="badge bg-info text-white rounded-pill px-3">Dipinjam</span>
                                    @elseif($aset->status_aset == 'Hilang')
                                        <span class="badge bg-dark rounded-pill px-3">Hilang</span>
                                    @elseif($aset->status_aset == 'Tidak Aktif')
                                        <span class="badge bg-danger rounded-pill px-3">Tidak Aktif</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-3">{{ $aset->status_aset ?? 'Tidak Aktif' }}</span>
                                    @endif
                                </td>

                                {{-- Action Buttons --}}
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        {{-- TOMBOL SHOW --}}
                                        <a href="{{ route('aset.show', $aset->id) }}" 
                                        class="btn btn-info btn-sm rounded-circle text-white border-0" 
                                        style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                        title="Lihat">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                            {{-- TOMBOL EDIT --}}
                                            <a href="{{ route('aset.edit', $aset->id) }}" 
                                            class="btn btn-warning btn-sm rounded-circle text-white border-0"
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Verifikasi (Edit)">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                    </div>

                                    <!-- Modal Konfirmasi Hapus -->
                                    <div class="modal fade" id="deleteAsetModal{{ $aset->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                                                <div class="modal-body p-5 text-center bg-light">
                                                    <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-4" style="width: 80px; height: 80px; background-color: #f1f3f5;">
                                                        <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                                                    </div>
                                                    <h4 class="fw-bold text-dark mb-2">Konfirmasi Hapus</h4>
                                                    <p class="text-muted mb-4" style="font-size: 1rem;">
                                                        Anda yakin ingin menghapus aset <br>
                                                        <strong class="text-danger fs-5">{{ $aset->nomor_aset }}</strong>?
                                                    </p>
                                                    <div class="d-flex justify-content-center">
                                                        <form action="{{ route('aset.destroy', $aset->id) }}" method="POST" enctype="multipart/form-data" class="w-100 text-start">
                                                            @csrf
                                                            @method('DELETE')
                                                            <div class="mb-4 text-start">
                                                                <label class="form-label fw-bold text-navy small">Upload Berita Acara Penghapusbukuan (PDF) <span class="text-danger">*</span></label>
                                                                <input type="file" name="dokumen_penghapusan" class="form-control" accept="application/pdf" required>
                                                                <small class="text-muted" style="font-size: 0.75rem;">Maksimal 5MB.</small>
                                                            </div>
                                                            <div class="d-flex justify-content-center gap-3">
                                                                <button type="button" class="btn btn-light rounded-pill fw-bold py-2 shadow-sm border" style="width: 120px;" data-bs-dismiss="modal">Batalkan</button>
                                                                <button type="submit" class="btn btn-danger rounded-pill fw-bold py-2 shadow-sm" style="width: 140px;">Ya, Hapus</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $showAdminActions ? 9 : 8 }}" class="text-center py-5 text-muted">
                                    <i class="fas fa-box fa-3x mb-3 d-block opacity-25"></i>
                                    Tidak ada data aset.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Menampilkan {{ $asets->firstItem() ?? 0 }} sampai {{ $asets->lastItem() ?? 0 }} dari {{ $asets->total() }} data
                </div>
                <div>
                    {{ $asets->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Cetak Per Ruangan -->
<div class="modal fade" id="modalCetakPerRuangan" tabindex="-1" aria-labelledby="modalCetakPerRuanganLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            {{-- Header --}}
            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white" id="modalCetakPerRuanganLabel">
                    <i class="fas fa-print me-2"></i> Cetak Label Per Ruangan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-4 bg-light">
                <form id="formCetakLabelLokasi" action="{{ route('aset.cetak-label-lokasi.process') }}" method="POST" target="_blank">
                    @csrf

                    {{-- Pilih Lokasi --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase" style="color: #253070; font-size: 0.72rem;">
                            <i class="fas fa-map-marker-alt me-1"></i> Pilih Ruangan <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus align-items-center bg-white border">
                            <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-door-open"></i></span>
                            <select class="form-select border-0 shadow-none fs-6" id="lokasiSelect" name="lokasi_id" required>
                                <option value="">-- Pilih Ruangan --</option>
                                @foreach ($lokasis as $lok)
                                    <option value="{{ $lok->lokasi_id }}">{{ $lok->nama_lokasi ?? $lok->nm_lokasi_aset }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Preview Aset --}}
                    <div id="previewAsetContainer" class="d-none">
                        <label class="form-label fw-bold small text-uppercase mb-2" style="color: #253070; font-size: 0.72rem;">
                            <i class="fas fa-list me-1"></i> Daftar Aset di Ruangan Ini
                        </label>
                        <div class="bg-white rounded-3 shadow-sm border overflow-hidden">
                            <div style="max-height: 220px; overflow-y: auto;">
                                <table class="table table-sm table-hover mb-0 align-middle">
                                    <thead style="background-color: #f1f3f9; position: sticky; top: 0; z-index: 1;">
                                        <tr>
                                            <th class="ps-3 py-2 text-uppercase small fw-bold text-muted" style="font-size: 0.7rem; width: 55%;">No Aset</th>
                                            <th class="py-2 text-uppercase small fw-bold text-muted" style="font-size: 0.7rem;">Nama Aset</th>
                                        </tr>
                                    </thead>
                                    <tbody id="previewAsetBody">
                                        {{-- AJAX Content --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <p class="text-muted small mt-2 mb-0"><i class="fas fa-info-circle me-1"></i> Semua aset di ruangan ini akan ikut dicetak.</p>
                    </div>

                </form>
            </div>

            {{-- Footer --}}
            <div class="modal-footer bg-light border-top-0 pt-2 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border" data-bs-dismiss="modal">
                    <i></i> Batal
                </button>
                <button type="submit" form="formCetakLabelLokasi" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" id="btnProsesCetakLokasi" disabled style="background-color: #253070;">
                    <i class="fas fa-print me-1"></i> Cetak Semua Aset Di Ruangan Ini
            </div>

        </div>
    </div>
</div>

<!-- Modal Import Aset -->
<div class="modal fade" id="modalImportAset" tabindex="-1" aria-labelledby="modalImportAsetLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            {{-- Header --}}
            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white" id="modalImportAsetLabel">
                    <i class="fas fa-file-import me-2"></i> Import Data Aset
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-4 bg-light">
                <form id="formImportAset" action="{{ route('aset.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Panduan / Download Template --}}
                    <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4 d-flex align-items-start gap-2" style="background-color: #eef2fa; color: #253070;">
                        <i class="fas fa-info-circle fa-lg mt-1"></i>
                        <div>
                            <span class="fw-bold">Petunjuk Import:</span>
                            <p class="small mb-2 text-muted">Gunakan template Excel standar yang telah disediakan agar data dapat terstruktur dan terimpor dengan benar.</p>
                            <a href="{{ route('aset.template') }}" class="btn btn-sm btn-navy text-white rounded-pill px-3 py-1 fw-bold border-0 shadow-sm" style="background-color: #253070;">
                                <i class="fas fa-download me-1"></i> Unduh Template Excel
                            </a>
                        </div>
                    </div>

                    {{-- File Input --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase" style="color: #253070; font-size: 0.72rem;">
                            <i class="fas fa-file-excel me-1"></i> File Excel (.xlsx, .xls) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group shadow-sm rounded-3 input-group-focus align-items-center bg-white border">
                            <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-upload"></i></span>
                            <input type="file" class="form-control border-0 shadow-none fs-6 bg-white" name="file_excel" accept=".xlsx, .xls" required>
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Maksimal ukuran file adalah 10MB.</small>
                    </div>

                </form>
            </div>

            {{-- Footer --}}
            <div class="modal-footer bg-light border-top-0 pt-2 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="submit" form="formImportAset" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" style="background-color: #253070;">
                    <i class="fas fa-upload me-1"></i> Import Data Aset
                </button>
            </div>

        </div>
    </div>
</div>

<form id="formCetakLabelSelected" action="{{ route('aset.cetak-label.process') }}" method="POST" target="_blank">
    @csrf
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check All logic
    const checkAll = document.getElementById('checkAllAset');
    const checkboxes = document.querySelectorAll('.aset-checkbox');
    
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    // Submit form for selected
    const btnCetakSelected = document.getElementById('btnCetakLabelSelected');
    if (btnCetakSelected) {
        btnCetakSelected.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.aset-checkbox:checked');
            if (checkedBoxes.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: 'Silakan pilih minimal satu aset terlebih dahulu untuk dicetak.',
                    confirmButtonColor: '#253070',
                    confirmButtonText: 'OK',
                    customClass: { popup: 'rounded-4 shadow' }
                });
                return;
            }
            
            const form = document.getElementById('formCetakLabelSelected');
            // Hapus input hidden sebelumnya
            form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
            
            // Tambahkan input hidden untuk setiap checkbox yang dipilih
            checkedBoxes.forEach(cb => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'ids[]';
                hiddenInput.value = cb.value;
                form.appendChild(hiddenInput);
            });
            
            form.submit();
        });
    }

    // AJAX Preview Lokasi
    const lokasiSelect = document.getElementById('lokasiSelect');
    const previewContainer = document.getElementById('previewAsetContainer');
    const previewBody = document.getElementById('previewAsetBody');
    const btnProsesCetakLokasi = document.getElementById('btnProsesCetakLokasi');

    if (lokasiSelect) {
        lokasiSelect.addEventListener('change', function() {
            const lokasiId = this.value;
            if (!lokasiId) {
                previewContainer.classList.add('d-none');
                btnProsesCetakLokasi.disabled = true;
                return;
            }

            // Fetch
            fetch(`/aset/lokasi/${lokasiId}/preview`)
                .then(res => res.json())
                .then(data => {
                    previewBody.innerHTML = '';
                    if (data.length === 0) {
                        previewBody.innerHTML = '<tr><td colspan="2" class="text-center text-muted py-3">Tidak ada aset di ruangan ini!</td></tr>';
                        btnProsesCetakLokasi.disabled = true;
                    } else {
                        data.forEach((aset, i) => {
                            const bg = i % 2 === 0 ? '' : 'style="background:#f8f9fa"';
                            previewBody.innerHTML += `<tr ${bg}>
                                <td>${aset.nomor_aset || '-'}</td>
                                <td>${aset.nama_aset || '-'}</td>
                            </tr>`;
                        });
                        btnProsesCetakLokasi.disabled = false;
                    }
                    previewContainer.classList.remove('d-none');
                })
                .catch(err => {
                    console.error('Error fetching preview:', err);
                    alert('Gagal mengambil data aset.');
                });
        });
    }

    // Reset modal 
    const modalCetakEl = document.getElementById('modalCetakPerRuangan');
    if (modalCetakEl) {
        modalCetakEl.addEventListener('show.bs.modal', function () {
            // Reset dropdown ke pilihan awal
            if (lokasiSelect) lokasiSelect.value = '';
            // Kosongkan isi tabel preview
            if (previewBody) previewBody.innerHTML = '';
            // Sembunyikan container preview
            if (previewContainer) previewContainer.classList.add('d-none');
            // Disable tombol cetak
            if (btnProsesCetakLokasi) btnProsesCetakLokasi.disabled = true;
        });
    }

    // Konfigurasi SweetAlert
    const swalConfig = {
        showConfirmButton: true,
        confirmButtonText: 'OK',
        confirmButtonColor: '#253070',
        customClass: { popup: 'rounded-4 shadow' }
    };

    // Modal Success / Error / Warning
    @if(session('success'))
        Swal.fire({ ...swalConfig, icon: 'success', title: 'Berhasil!', text: '{{ session('success') }}' });
    @endif

    @if(session('error'))
        Swal.fire({ ...swalConfig, icon: 'error', title: 'Gagal!', text: '{{ session('error') }}' });
    @endif

    @if(session('warning'))
        Swal.fire({ ...swalConfig, icon: 'warning', title: 'Perhatian!', text: '{{ session('warning') }}' });
    @endif

    // Auto-refresh when search input is cleared
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            if (this.value.trim() === '') {
                this.form.submit();
            }
        });
        searchInput.addEventListener('search', function() {
            if (this.value.trim() === '') {
                this.form.submit();
            }
        });
    }
});
</script>
@endsection
