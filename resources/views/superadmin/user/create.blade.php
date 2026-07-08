@extends('layouts.app')

@section('title', 'Tambah Pengguna Baru')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Tambah Pengguna Baru</h3>
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
                <a href="{{ route('user.manage') }}" class="text-muted text-decoration-none d-flex align-items-center">
                    <span style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Manajemen Pengguna</span>
                </a>
            </li>
                <li class="separator text-muted d-flex align-items-center px-2">
                <span style="font-size: 14px; position: relative; top: 2px;">-</span>
            </li>
            
            <li class="nav-item d-flex align-items-center">
                <span class="text-muted" style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Tambah Pengguna</span>
            </li>
        </ul>
    </div>

    <form action="{{ route('user-manage/add') }}" method="POST">
        @csrf
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4 p-md-5">

        {{-- Informasi Akun --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white pt-3 pb-2 border-bottom-0">
                <h6 class="mb-0 fw-semibold text-navy"><i class="fas fa-id-card me-2"></i> Informasi Akun</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy mb-1 small">ID Pengguna</label>
                        <div class="input-group input-group-focus rounded-3">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-hashtag"></i></span>
                            <input type="text" name="id" class="form-control bg-light border-start-0 ps-0 shadow-none text-muted" placeholder="Otomatis terisi oleh sistem" disabled style="cursor: not-allowed; opacity: 0.8;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy mb-1 small">Email <span class="text-danger">*</span></label>
                        <div class="input-group input-group-focus rounded-3">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control border-start-0 ps-0 shadow-none" placeholder="contoh@email.com" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Data Pribadi --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white pt-3 pb-2 border-bottom-0">
                <h6 class="mb-0 fw-semibold text-navy"><i class="fas fa-user me-2"></i> Data Pribadi</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy mb-1 small">Nama Depan <span class="text-danger">*</span></label>
                        <div class="input-group input-group-focus rounded-3">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-user"></i></span>
                            <input type="text" name="firstname" class="form-control border-start-0 ps-0 shadow-none" placeholder="Masukkan nama depan" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy mb-1 small">Nama Akhir</label>
                        <div class="input-group input-group-focus rounded-3">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-font"></i></span>
                            <input type="text" name="lastname" class="form-control border-start-0 ps-0 shadow-none" placeholder="Masukkan nama akhir (opsional)">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy mb-1 small">NIP <span class="text-danger">*</span></label>
                        <div class="input-group input-group-focus rounded-3">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-id-badge"></i></span>
                            <input type="text" name="nip" class="form-control border-start-0 ps-0 shadow-none" placeholder="Masukkan NIP" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy mb-1 small">No. Telepon <span class="text-danger">*</span></label>
                        <div class="input-group input-group-focus rounded-3">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-phone-alt"></i></span>
                            <input type="text" name="phone_number" class="form-control border-start-0 ps-0 shadow-none" placeholder="08xxxxxxxxxx" required minlength="10" maxlength="15" pattern="\d{10,15}" title="Nomor telepon harus 10-15 digit angka">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Keamanan --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white pt-3 pb-2 border-bottom-0">
                <h6 class="mb-0 fw-semibold text-navy"><i class="fas fa-lock me-2"></i> Keamanan</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy mb-1 small">Kata Sandi <span class="text-danger">*</span></label>
                        <div class="input-group input-group-focus rounded-3">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" id="password" class="form-control border-start-0 ps-0 shadow-none" placeholder="Minimal 8 karakter" minlength="8" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy mb-1 small">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
                        <div class="input-group input-group-focus rounded-3">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-shield-alt"></i></span>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control border-start-0 ps-0 shadow-none" placeholder="Ulangi kata sandi" minlength="8" required oninput="this.setCustomValidity(this.value !== document.getElementById('password').value ? 'Konfirmasi kata sandi tidak cocok' : '')">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Organisasi & Posisi --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white pt-3 pb-2 border-bottom-0">
                <h6 class="mb-0 fw-semibold text-navy"><i class="fas fa-building me-2"></i> Organisasi & Posisi</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="parent_id" class="form-label fw-bold text-navy mb-1 small">Pilih Organisasi <span class="text-danger">*</span></label>
                        <div class="input-group input-group-focus rounded-3">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-sitemap"></i></span>
                            <select class="form-select border-start-0 ps-0 shadow-none" id="parent_id" name="parent_id" required>
                                <option value="">-- Pilih Organisasi --</option>
                                @php
                                    function renderOrgOptions($node, $level = 0) {
                                        $indent = str_repeat('&nbsp;', $level * 4);
                                        $statusLabel = (!isset($node->is_active) || $node->is_active) ? '' : ' (Nonaktif)';
                                        $disabledAttr = (!isset($node->is_active) || $node->is_active) ? '' : ' disabled';

                                        if (isset($node->name_director)) {
                                            echo "<option value='{$node->id_director}' data-type='director'{$disabledAttr}>{$indent}Direktur: {$node->name_director}{$statusLabel}</option>";
                                        } elseif (isset($node->nm_divisi)) {
                                            echo "<option value='{$node->id_divisi}' data-type='divisi'{$disabledAttr}>{$indent}&rarr; Divisi: {$node->nm_divisi}{$statusLabel}</option>";
                                        } elseif (isset($node->name_department)) {
                                            echo "<option value='{$node->id_department}' data-type='department'{$disabledAttr}>{$indent}&rarr; Departemen: {$node->name_department}{$statusLabel}</option>";
                                        } elseif (isset($node->name_section)) {
                                            echo "<option value='{$node->id_section}' data-type='section'{$disabledAttr}>{$indent}&rarr; Bagian: {$node->name_section}{$statusLabel}</option>";
                                        } elseif (isset($node->name_unit)) {
                                            echo "<option value='{$node->id_unit}' data-type='unit'{$disabledAttr}>{$indent}&rarr; Unit: {$node->name_unit}{$statusLabel}</option>";
                                        }
                                        if (isset($node->subDirectors)) { foreach ($node->subDirectors as $subDir) renderOrgOptions($subDir, $level + 1); }
                                        if (isset($node->divisi)) { foreach ($node->divisi as $div) renderOrgOptions($div, $level + 1); }
                                        if (isset($node->department)) { foreach ($node->department as $dept) renderOrgOptions($dept, $level + 1); }
                                        if (isset($node->section)) { foreach ($node->section as $sec) renderOrgOptions($sec, $level + 1); }
                                        if (isset($node->unit)) { foreach ($node->unit as $unit) renderOrgOptions($unit, $level + 1); }
                                    }
                                    if ($mainDirector) renderOrgOptions($mainDirector);
                                @endphp
                            </select>
                        </div>
                        <input type="hidden" name="parent_type" id="parent_type">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy mb-1 small">Posisi <span class="text-danger">*</span></label>
                        <div class="input-group input-group-focus rounded-3">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-briefcase"></i></span>
                            <select name="position_id_position" id="position_id_position" class="form-select border-start-0 ps-0 shadow-none" required>
                                <option value="">-- Pilih Posisi --</option>
                                @foreach ($positions as $p)
                                    <option value="{{ $p->id_position }}">{{ $p->nm_position }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hak Akses & Kode Bagian --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white pt-3 pb-2 border-bottom-0">
                <h6 class="mb-0 fw-semibold text-navy"><i class="fas fa-shield-alt me-2"></i> Hak Akses & Area Kerja</h6>
            </div>
            <div class="card-body p-4">
                <!-- Baris Atas: Hak Akses -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-navy mb-1 small mb-3">
                        <i class="fas fa-user-shield me-1"></i> Hak Akses <span class="text-danger">*</span>
                    </label>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 bg-light-hover cursor-pointer transition-all">
                                <div class="form-check m-0 d-flex align-items-center">
                                    <input class="form-check-input mt-0 me-3" type="radio" name="role_id_role" value="1" id="role1" required>
                                    <label class="form-check-label w-100 cursor-pointer m-0" for="role1">
                                        <div class="fw-bold text-navy"><i class="fas fa-star me-1 text-navy"></i> Superadmin</div>
                                        <small class="text-muted">Akses penuh sistem</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 bg-light-hover cursor-pointer transition-all">
                                <div class="form-check m-0 d-flex align-items-center">
                                    <input class="form-check-input mt-0 me-3" type="radio" name="role_id_role" value="2" id="role2">
                                    <label class="form-check-label w-100 cursor-pointer m-0" for="role2">
                                        <div class="fw-bold text-info" style="color: #4da3ff !important;"><i class="fas fa-user me-1" style="color: #4da3ff;"></i> User</div>
                                        <small class="text-muted">Akses terbatas</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 bg-light-hover cursor-pointer transition-all">
                                <div class="form-check m-0 d-flex align-items-center">
                                    <input class="form-check-input mt-0 me-3" type="radio" name="role_id_role" value="3" id="role3">
                                    <label class="form-check-label w-100 cursor-pointer m-0" for="role3">
                                        <div class="fw-bold text-warning"><i class="fas fa-cog me-1 text-warning"></i> Admin</div>
                                        <small class="text-muted">Kelola data</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Baris Bawah: Kode Bagian -->
                <div>
                    <label class="form-label fw-bold text-navy mb-1 small mb-2">
                        <i class="fas fa-tag me-1"></i> Kode Bagian
                    </label>
                    <p class="text-muted small mb-3"><i class="fas fa-info-circle me-1"></i> Pilih satu atau lebih bagian kerja yang akan dikelola pengguna. <strong>Centang kotak</strong> untuk memilih.</p>
                    
                    <div class="border rounded bg-white shadow-sm overflow-hidden">
                        <div style="max-height: 250px; overflow-y: auto;">
                            @foreach ($bagianKerja as $index => $b)
                                <div class="px-3 py-2 {{ $index > 0 ? 'border-top' : '' }} bg-light-hover transition-all">
                                    <div class="form-check m-0 d-flex align-items-center">
                                        <input class="form-check-input mt-0" type="checkbox" name="kode_bagian[]" value="{{ $b->kode_bagian }}" id="add_bagian_{{ $b->kode_bagian }}">
                                        <label for="add_bagian_{{ $b->kode_bagian }}" class="form-check-label d-flex align-items-center mb-0 cursor-pointer py-1 ms-3">
                                            <span class="badge bg-primary text-white px-3 py-2 me-3 rounded-1" style="min-width: 70px; text-align: center; letter-spacing: 0.5px;">
                                                {{ $b->kode_bagian }}
                                            </span>
                                            <span class="text-dark fw-medium small">{{ $b->nama_bagian ?? '-' }}</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="bg-light border-top border-start border-warning border-4 p-2 px-3 m-0">
                            <small class="text-dark"><i class="fas fa-lightbulb text-warning me-1"></i> <strong>Tips:</strong> Scroll ke bawah untuk melihat lebih banyak pilihan. Anda bisa memilih lebih dari satu bagian.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">
                    <a href="{{ route('user.manage') }}" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border">Batal</a>
                    <button type="submit" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" style="background-color: #253070;">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </div> <!-- end wrapper card-body -->
        </div> <!-- end wrapper card -->
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Initialize Select2 for parent_id
        $('#parent_id').select2({
            theme: 'bootstrap',
            placeholder: '-- Pilih Organisasi --',
            allowClear: true,
            width: '100%'
        }).on('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var type = selectedOption ? selectedOption.getAttribute('data-type') : '';
            document.getElementById('parent_type').value = type || '';
        });
    });
</script>
@endpush
