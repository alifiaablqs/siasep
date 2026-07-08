@extends('layouts.app')

@section('title', 'Tambah Data Aset')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/aset-form.css') }}">
@endpush

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Tambah Data Aset Baru</h3>
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
                <a href="{{ route('aset.index') }}" class="text-muted text-decoration-none d-flex align-items-center">
                    <span style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Data Aset Perusahaan</span>
                </a>
            </li>
                <li class="separator text-muted d-flex align-items-center px-2">
                <span style="font-size: 14px; position: relative; top: 2px;">-</span>
            </li>
            
            <li class="nav-item d-flex align-items-center">
                <span class="text-muted" style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Tambah Data Aset</span>
            </li>
        </ul>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm rounded-3">
                    <ul class="mb-0 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            @php
                $oldNomorUrut = old('nomor_urut');
                $isCustomNomorUrut = $oldNomorUrut !== null && $oldNomorUrut !== (string)$nextId;
            @endphp
            <form action="{{ route('aset.store') }}" method="POST" enctype="multipart/form-data" id="formAset" autocomplete="off" novalidate>
                @csrf

                {{-- INFORMASI DATA ASET --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white pt-3 pb-2 border-bottom-0">
                        <h6 class="mb-0 fw-semibold text-navy">
                            <i class="fas fa-box-open me-2"></i> Informasi Data Aset
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            {{-- NOMOR URUT ASET --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy mb-1 small">
                                    Nomor Urut Aset <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-hashtag"></i></span>
                                    <input type="text" name="nomor_urut" id="nomor_urut"
                                           class="form-control border-start-0 ps-0 shadow-none bg-white"
                                           value="{{ old('nomor_urut') }}"
                                           placeholder="Contoh: 1 atau 105"
                                           maxlength="5"
                                           inputmode="numeric"
                                           style="cursor: text;"
                                           required>
                                </div>
                                <small class="text-muted" id="hint_urut" style="font-size: 0.7rem;">
                                    Masukkan nomor urut (contoh: 1)
                                </small>
                                @error('nomor_urut')<div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                            </div>

                            {{-- PRATINJAU NOMOR ASET (otomatis) --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy mb-1 small">Pratinjau Nomor Aset</label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-barcode"></i></span>
                                    <input type="text" id="nomor_aset_display" class="form-control border-start-0 ps-0 shadow-none bg-light text-navy fw-bold" 
                                           value="Memuat..." disabled style="cursor: not-allowed; opacity: 1;">
                                </div>
                                <small class="text-muted" style="font-size: 0.7rem;">KODE/NO_URUT/LOKASI/TAHUN</small>
                            </div>


                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy mb-1 small">Kategori Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-layer-group"></i></span>
                                    <select name="kategori_id" id="kategori_id" class="form-select border-start-0 ps-0 shadow-none" required>
                                        <option value="" selected disabled>-- Pilih Kategori Aset --</option>
                                        <optgroup label="KATEGORI ASET TETAP">
                                            @foreach($kategoriTetap as $kt)
                                                <option value="{{ $kt->id }}" data-kode="{{ $kt->kode }}" data-nama="{{ $kt->nama }}" {{ old('kategori_id') == $kt->id ? 'selected' : '' }}>
                                                    {{ $kt->kode }} - {{ $kt->nama }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="KATEGORI ASET EC">
                                            @foreach($kategoriInventaris as $ki)
                                                <option value="{{ $ki->id }}" data-kode="{{ $ki->kode }}" data-nama="{{ $ki->nama }}" {{ old('kategori_id') == $ki->id ? 'selected' : '' }}>
                                                    {{ $ki->kode }} - {{ $ki->nama }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </div>
                                @error('kategori_id')<div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy mb-1 small">Nama Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-box-open"></i></span>
                                    <input type="text" name="nama_aset" id="nama_aset" class="form-control border-start-0 ps-0 shadow-none" value="{{ old('nama_aset') }}" placeholder="Contoh: Gedung Kantor Utama" required>
                                </div>
                                @error('nama_aset')<div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-navy mb-1 small">Tahun Kapitalisasi <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="number" min="1900" max="2100" name="tahun_kapitalisasi" id="id_tahun" class="form-control border-start-0 ps-0 shadow-none" value="{{ old('tahun_kapitalisasi', date('Y')) }}" placeholder="Contoh: 2025" required>
                                </div>
                                @error('tahun_kapitalisasi')<div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-navy mb-1 small">Merk Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-tag"></i></span>
                                    <input type="text" name="merek" class="form-control border-start-0 ps-0 shadow-none" value="{{ old('merek') }}" placeholder="Lenovo / Honda" required>
                                </div>
                                @error('merek')<div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold text-navy mb-1 small">Nomor BAST (Opsional)</label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-file-signature"></i></span>
                                    <input type="text" name="bast" class="form-control border-start-0 ps-0 shadow-none" value="{{ old('bast') }}" placeholder="Contoh: 001/BAST/2023">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold text-navy mb-1 small">Deskripsi Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-align-left"></i></span>
                                    <textarea name="deskripsi" class="form-control border-start-0 ps-0 shadow-none" rows="2" placeholder="Rincian detail aset..." required>{{ old('deskripsi') }}</textarea>
                                </div>
                                @error('deskripsi')<div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PENEMPATAN ASET --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white pt-3 pb-2 border-bottom-0">
                        <h6 class="mb-0 fw-semibold text-navy">
                            <i class="fas fa-map-marker-alt me-2"></i> Penempatan Aset
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy mb-1 small">Lokasi Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-map-marker-alt"></i></span>
                                    <select name="lokasi_id" id="dropdown_lokasi" class="form-select border-start-0 ps-0 shadow-none js-searchable-select" data-placeholder="Cari lokasi aset" required>
                                        <option value="" selected disabled>-- Pilih Lokasi --</option>
                                        @foreach($lokasi as $lok)
                                            <option value="{{ $lok->lokasi_id }}" 
                                                    data-detail="{{ $lok->detail_lokasi ?? '' }}"
                                                    data-kode="{{ $lok->kode_lokasi ?? 'LOK' }}"
                                                    {{ old('lokasi_id') == $lok->lokasi_id ? 'selected' : '' }}>
                                                {{ $lok->nama_lokasi ?? $lok->nm_lokasi_aset }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('lokasi_id')<div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy mb-1 small text-muted">Detail Lokasi</label>
                                <input type="text" id="input_detail_lokasi" class="form-control bg-light text-muted border-0 shadow-none rounded-3 px-3 py-2" 
                                       disabled placeholder="Otomatis dari lokasi terpilih" style="cursor: not-allowed; opacity: 0.8;">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STATUS & STRUKTUR --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white pt-3 pb-2 border-bottom-0">
                        <h6 class="mb-0 fw-semibold text-navy"><i class="fas fa-shield-alt me-2"></i> Kondisi & Struktur</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-navy mb-1 small">Kondisi Saat Ini <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-info-circle"></i></span>
                                    <select name="status_kondisi" id="status_kondisi" class="form-select border-start-0 ps-0 shadow-none" required>
                                        <option value="" selected disabled>-- Pilih Kondisi --</option>
                                        <option value="Baik" {{ old('status_kondisi') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                        <option value="Rusak" {{ old('status_kondisi') == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                                        <option value="Bongkar" {{ old('status_kondisi') == 'Bongkar' ? 'selected' : '' }}>Bongkar</option>
                                        <option value="Tidak Terpakai" {{ old('status_kondisi') == 'Tidak Terpakai' ? 'selected' : '' }}>Tidak Terpakai</option>
                                        <option value="Hilang" {{ old('status_kondisi') == 'Hilang' ? 'selected' : '' }}>Hilang</option>
                                        <option value="Tidak Teridentifikasi" {{ old('status_kondisi') == 'Tidak Teridentifikasi' ? 'selected' : '' }}>Tidak Teridentifikasi</option>
                                    </select>
                                </div>
                                @error('status_kondisi')<div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                            </div>
                            


                            <div class="col-md-3">
                                <label class="form-label fw-bold text-navy mb-1 small">Status Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-cog"></i></span>
                                    <select name="status_aset" class="form-select border-start-0 ps-0 shadow-none" required>
                                        <option value="" selected disabled>-- Pilih Status --</option>
                                        <option value="Aktif" {{ old('status_aset') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="Dalam Perbaikan" {{ old('status_aset') == 'Dalam Perbaikan' ? 'selected' : '' }}>Dalam Perbaikan</option>
                                        <option value="Tidak Aktif" {{ old('status_aset') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                                        <option value="Dipinjam" {{ old('status_aset') == 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                                        <option value="Hilang" {{ old('status_aset') == 'Hilang' ? 'selected' : '' }}>Hilang</option>
                                    </select>
                                </div>
                                @error('status_aset')<div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-navy mb-1 small">PIC Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-user-tie"></i></span>
                                    <select name="pic_id" class="form-select border-start-0 ps-0 shadow-none js-searchable-select" data-placeholder="Cari PIC aset" required>
                                        <option value="" selected disabled>-- Pilih User --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('pic_id') == $user->id ? 'selected' : '' }}>{{ $user->firstname }} {{ $user->lastname }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('pic_id')<div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-navy mb-1 small">Penanggung Jawab Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-user-shield"></i></span>
                                    <select name="penanggung_jawab_id" class="form-select border-start-0 ps-0 shadow-none js-searchable-select" data-placeholder="Cari penanggung jawab aset" required>
                                        <option value="" selected disabled>-- Pilih User --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('penanggung_jawab_id') == $user->id ? 'selected' : '' }}>{{ $user->firstname }} {{ $user->lastname }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('penanggung_jawab_id')<div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold text-navy mb-1 small">Struktur Organisasi <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-sitemap"></i></span>
                                    <select class="form-select border-start-0 ps-0 shadow-none js-org-select2" id="kode_organisasi" name="kode_organisasi" data-placeholder="Cari struktur organisasi" required>
                                        <option value="" selected disabled>-- Pilih Organisasi --</option>
                                        @php
                                            if (!function_exists('renderOrgOptions')) {
                                                function renderOrgOptions($node, &$seen = [], $level = 0) {
                                                    $indent = str_repeat('&nbsp;', $level * 4);
                                                    $prefix = $level > 0 ? '→ ' : '';
                                                    
                                                    $val = null;
                                                    $type = null;
                                                    $label = null;

                                                    if (isset($node->name_director)) {
                                                        $val = $node->id_director;
                                                        $type = 'director';
                                                        $label = "Direktur: {$node->name_director}";
                                                        $printLabel = "Direktur: {$node->name_director}";
                                                    } elseif (isset($node->nm_divisi)) {
                                                        $val = $node->id_divisi;
                                                        $type = 'divisi';
                                                        $label = "Divisi: {$node->nm_divisi}";
                                                        $printLabel = "{$prefix}Divisi: {$node->nm_divisi}";
                                                    } elseif (isset($node->name_department)) {
                                                        $val = $node->id_department;
                                                        $type = 'department';
                                                        $label = "Departemen: {$node->name_department}";
                                                        $printLabel = "{$prefix}Departemen: {$node->name_department}";
                                                    } elseif (isset($node->name_section)) {
                                                        $val = $node->id_section;
                                                        $type = 'section';
                                                        $label = "Bagian: {$node->name_section}";
                                                        $printLabel = "{$prefix}Bagian: {$node->name_section}";
                                                    } elseif (isset($node->name_unit)) {
                                                        $val = $node->id_unit;
                                                        $type = 'unit';
                                                        $label = "Unit: {$node->name_unit}";
                                                        $printLabel = "{$prefix}Unit: {$node->name_unit}";
                                                    }

                                                    if ($type && $val) {
                                                        $key = $type . '_' . $val;
                                                        if (isset($seen[$key])) return; // Skip duplicate
                                                        $seen[$key] = true;

                                                        $statusLabel = (!isset($node->is_active) || $node->is_active) ? '' : ' (Nonaktif)';
                                                        $disabledAttr = (!isset($node->is_active) || $node->is_active) ? '' : ' disabled';

                                                        $selected = old('kode_organisasi') == $key ? 'selected' : '';
                                                        echo "<option value='{$key}' data-label='{$label}' {$selected} {$disabledAttr}>{$indent}{$printLabel}{$statusLabel}</option>";
                                                    }

                                                    if (isset($node->subDirectors)) foreach ($node->subDirectors as $s) renderOrgOptions($s, $seen, $level + 1);
                                                    if (isset($node->divisi)) foreach ($node->divisi as $d) renderOrgOptions($d, $seen, $level + 1);
                                                    if (isset($node->department)) foreach ($node->department as $dp) renderOrgOptions($dp, $seen, $level + 1);
                                                    if (isset($node->section)) foreach ($node->section as $sc) renderOrgOptions($sc, $seen, $level + 1);
                                                    if (isset($node->unit)) foreach ($node->unit as $u) renderOrgOptions($u, $seen, $level + 1);
                                                }
                                            }
                                            $seenArray = [];
                                            if (isset($mainDirector)) renderOrgOptions($mainDirector, $seenArray);
                                        @endphp
                                    </select>
                                </div>
                                @error('kode_organisasi')<div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DOKUMENTASI MULTI FOTO --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white pt-3 pb-2 border-bottom-0">
                        <h6 class="mb-0 fw-semibold text-navy"><i class="fas fa-camera me-2"></i> Foto Aset <span class="text-danger">*</span></h6>
                    </div>
                    <div class="card-body">
                        <div class="upload-container border border-2 border-dashed rounded-3 p-4 text-center bg-light position-relative" id="dropzone" style="border-color: #253070 !important; cursor: pointer; transition: all 0.3s ease;">
                            <input type="file" name="foto[]" id="fileInput" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" accept="image/*" multiple required style="cursor: pointer; z-index: 10;">
                            <div class="upload-prompt">
                                <i class="fas fa-cloud-upload-alt fa-3x text-navy mb-3"></i>
                                <h6 class="fw-bold text-navy">Tarik & Lepas Foto di Sini</h6>
                                <p class="text-muted small mb-0">atau klik untuk memilih file dari komputer</p>
                                <span class="badge bg-secondary mt-2">Maks. 10 Foto</span>
                            </div>
                        </div>
                        
                        {{-- Container untuk Pratinjau Foto --}}
                        <div class="row g-3 mt-3" id="previewContainer" style="display: none;">
                            {{-- Rendered by JS --}}
                        </div>

                        <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle me-1"></i> Bisa upload lebih dari 1 foto sekaligus. Format: JPG, JPEG, PNG. Maks 4MB per foto.</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('aset.index') }}" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border">Batal</a>
                    <button type="submit" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" style="background-color: #253070;">
                        <i class="fas fa-save me-1"></i> Simpan Data Aset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formAset');

        function clearFieldError(field) {
            field.classList.remove('is-invalid');
            const wrapper = field.closest('.input-group') || field;
            const next = wrapper.nextElementSibling;
            if (next && next.classList.contains('invalid-feedback-custom')) {
                next.remove();
            }
        }

        function showFieldError(field, message) {
            field.classList.add('is-invalid');
            const wrapper = field.closest('.input-group') || field;
            let errorEl = wrapper.nextElementSibling;

            if (!errorEl || !errorEl.classList.contains('invalid-feedback-custom')) {
                errorEl = document.createElement('div');
                errorEl.className = 'text-danger small mt-1 fw-bold invalid-feedback-custom';
                wrapper.parentNode.insertBefore(errorEl, wrapper.nextSibling);
            }

            errorEl.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>' + message;
        }

        function validateRequiredFields() {
            let firstInvalid = null;
            const requiredFields = form.querySelectorAll('input[required], select[required], textarea[required]');

            requiredFields.forEach(function(field) {
                clearFieldError(field);

                const isEmpty = field.tagName === 'SELECT'
                    ? !field.value
                    : field.type === 'file'
                        ? !field.files || field.files.length === 0
                        : !String(field.value || '').trim();

                if (isEmpty) {
                    const label = field.closest('.col-md-12, .col-md-6, .col-md-3, .mb-4, .mb-2')?.querySelector('label');
                    const labelText = label ? label.textContent.replace('*', '').trim() : (field.getAttribute('placeholder') || 'Kolom');
                    showFieldError(field, labelText + ' wajib diisi.');
                    if (!firstInvalid) {
                        firstInvalid = field;
                    }
                }
            });

            return firstInvalid;
        }

        if (form) {
            form.addEventListener('submit', function(e) {
                const firstInvalid = validateRequiredFields();
                if (firstInvalid) {
                    e.preventDefault();
                    firstInvalid.focus();
                }
            });
        }

        if (window.jQuery && $.fn.select2) {
            $('.js-searchable-select').each(function () {
                const $select = $(this);
                $select.select2({
                    width: '100%',
                    placeholder: $select.data('placeholder') || '',
                    allowClear: false
                });
            });

            $('#dropdown_lokasi').on('change', function() {
                const opt = this.options[this.selectedIndex];
                inputDetail.value = (opt && opt.value !== "") ? (opt.getAttribute('data-detail') || '') : '';
                updateNomor();
            });
        }

        if (window.jQuery && $.fn.select2) {
            $('.js-org-select2').each(function () {
                const $select = $(this);
                const dropdownParent = $select.closest('.modal').length ? $select.closest('.modal') : $(document.body);

                $select.select2({
                    width: '100%',
                    dropdownParent: dropdownParent,
                    placeholder: $select.data('placeholder') || 'Cari struktur organisasi'
                });
            });
        }

        const inputDisplay  = document.getElementById('nomor_aset_display');
        const inputDetail   = document.getElementById('input_detail_lokasi');
        const inputNoUrut   = document.getElementById('nomor_urut');
        const btnOverride   = document.getElementById('btn_override_urut');
        const iconLock      = document.getElementById('icon_lock');
        const badgeAuto     = document.getElementById('badge_auto');
        const badgeManual   = document.getElementById('badge_manual');
        const hintUrut      = document.getElementById('hint_urut');

        const selectKategori = document.getElementById('kategori_id');
        const selectLok      = document.getElementById('dropdown_lokasi');
        const selectThn      = document.getElementById('id_tahun');
        const statusKondisi  = document.getElementById('status_kondisi');
        const inputNamaAset  = document.getElementById('nama_aset');

        const nextId = "{{ $nextId }}"; // dari DB
        let isOverride = @json($isCustomNomorUrut);

        function padNoUrut(val) {
            return val.replace(/\D/g, '').slice(0, 5);
        }

        function updateNomor() {
            // Kode Kategori
            const optKat = selectKategori.options[selectKategori.selectedIndex];
            const kKat   = (selectKategori.value && optKat.getAttribute('data-kode')) ? optKat.getAttribute('data-kode') : 'XXX';
            
            // Kode Lokasi
            const optLok = selectLok.options[selectLok.selectedIndex];
            const kLok   = (selectLok.value && optLok.getAttribute('data-kode')) ? optLok.getAttribute('data-kode') : 'LOK';
            
            // Tahun Kapitalisasi
            const thn = (selectThn && selectThn.value) ? parseInt(selectThn.value) : new Date().getFullYear();

            // Nomor Urut
            const rawUrut = inputNoUrut ? inputNoUrut.value : '';
            const noUrut  = rawUrut ? padNoUrut(rawUrut) : 'XXXXX';

            inputDisplay.value = `${kKat}/${noUrut}/${kLok}/${thn}`;
        }

        // Saat mengetik di nomor urut
        if (inputNoUrut) {
            inputNoUrut.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 5);
                updateNomor();
            });
            inputNoUrut.addEventListener('blur', function() {
                if (this.value) this.value = padNoUrut(this.value);
                updateNomor();
            });
        }

        [selectKategori, selectLok].forEach(el => {
            if(el) el.addEventListener('change', updateNomor);
        });
        if (selectThn) {
            selectThn.addEventListener('input', updateNomor);
            selectThn.addEventListener('change', updateNomor);
        }

        // Detail Lokasi & Auto-fill Nama Aset
        if(selectLok) {
            selectLok.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                inputDetail.value = (opt && opt.value !== "") ? (opt.getAttribute('data-detail') || '') : '';
            });
        }
        
        if(selectKategori) {
            selectKategori.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                if(opt && opt.value !== "") {
                    inputNamaAset.value = opt.getAttribute('data-nama') || '';
                }
                updateNomor();
            });
        }


        // Uploader Multi-Foto Logic
        const fileInput = document.getElementById('fileInput');
        const dropzone = document.getElementById('dropzone');
        const previewContainer = document.getElementById('previewContainer');
        let selectedFiles = [];

        if (fileInput && dropzone) {
            // Drag and Drop Events
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    dropzone.classList.add('dragover');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    dropzone.classList.remove('dragover');
                }, false);
            });

            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                handleFiles(dt.files);
            });

            fileInput.addEventListener('change', function() {
                handleFiles(this.files);
            });
        }

        function handleFiles(files) {
            const validFiles = Array.from(files).filter(file => {
                const isImage = file.type.startsWith('image/');
                const isValidSize = file.size <= 4 * 1024 * 1024; // 4MB
                if (!isImage) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Berkas Bukan Gambar',
                        text: `File "${file.name}" bukan gambar yang valid.`,
                        confirmButtonColor: '#253070'
                    });
                } else if (!isValidSize) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ukuran Terlalu Besar',
                        text: `File "${file.name}" melebihi batas ukuran maksimal 4MB.`,
                        confirmButtonColor: '#253070'
                    });
                }
                return isImage && isValidSize;
            });

            if (selectedFiles.length + validFiles.length > 10) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Batas Maksimal Foto',
                    text: 'Maksimal foto yang dapat diunggah adalah 10 foto.',
                    confirmButtonColor: '#253070'
                });
                const remainingSlots = 10 - selectedFiles.length;
                selectedFiles = [...selectedFiles, ...validFiles.slice(0, remainingSlots)];
            } else {
                selectedFiles = [...selectedFiles, ...validFiles];
            }

            updateInputAndPreview();
        }

        window.removeUploadFile = function(index) {
            selectedFiles.splice(index, 1);
            updateInputAndPreview();
        };

        function updateInputAndPreview() {
            const dt = new DataTransfer();
            selectedFiles.forEach(file => dt.items.add(file));
            fileInput.files = dt.files;

            // Clear preview
            previewContainer.innerHTML = '';

            if (selectedFiles.length > 0) {
                previewContainer.style.display = 'flex';
                
                selectedFiles.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-6 col-md-3 col-lg-2 position-relative';
                        col.innerHTML = `
                            <div class="card border shadow-sm rounded-3 overflow-hidden preview-card" style="height: 120px;">
                                <img src="${e.target.result}" class="w-100 h-100 object-fit-cover" alt="Preview">
                                <div class="position-absolute top-0 end-0 m-1">
                                    <button type="button" class="remove-btn btn btn-danger btn-sm p-0 rounded-circle" onclick="window.removeUploadFile(${index})">
                                        <i class="fas fa-times" style="font-size: 11px;"></i>
                                    </button>
                                </div>
                                <div class="position-absolute bottom-0 start-0 w-100 bg-dark bg-opacity-75 text-white px-2 py-1 small text-truncate" style="font-size: 0.7rem;" title="${file.name}">
                                    ${file.name}
                                </div>
                            </div>
                        `;
                        previewContainer.appendChild(col);
                    };
                    reader.readAsDataURL(file);
                });
            } else {
                previewContainer.style.display = 'none';
            }
        }

        // Jalankan pratinjau pertama kali
        setTimeout(updateNomor, 100);
    });
</script>
@endpush