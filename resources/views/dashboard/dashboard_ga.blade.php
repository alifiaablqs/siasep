@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-0 text-dark">Dashboard</h3>
        </div>
    </div>

    {{-- Welcome Card --}}
    <div class="card border-0 mb-4 overflow-hidden" style="border-radius: 1rem; background: linear-gradient(135deg, #1A2355 0%, #2A367C 100%); box-shadow: 0 8px 24px rgba(26, 35, 85, 0.12); color: #ffffff;">
        <div class="card-body p-4 d-flex align-items-center position-relative">
            <div class="d-flex align-items-center w-100 position-relative" style="z-index: 1;">
                <div class="me-3 d-none d-md-flex align-items-center justify-content-center bg-white bg-opacity-10 rounded-circle" style="width: 55px; height: 55px; backdrop-filter: blur(5px);">
                    <span class="fs-3">👋</span>
                </div>
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Public Sans', sans-serif;">Selamat Datang, {{ auth()->user()->firstname }} {{ auth()->user()->lastname }}!</h4>
                    <p class="mb-0 opacity-90 small" style="line-height: 1.5;">
                        Selamat datang di <strong>Sistem Informasi Manajemen Aset</strong>. Anda login sebagai 
                        <span class="badge bg-white text-navy fw-bold px-2.5 py-1.5 ms-1 rounded-pill" style="color: #1A2355 !important; font-size: 0.75rem; letter-spacing: 0.5px;">{{ auth()->user()->role_id_role == 1 ? 'Superadmin' : (auth()->user()->role_id_role == 3 ? 'Admin' : 'User') }} (General Affairs)</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Alert Verifikasi SIPO --}}
    @if(isset($verifikasiPending) && $verifikasiPending > 0)
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4 rounded-3" role="alert">
            <i class="fas fa-exclamation-triangle fa-2x text-warning me-3"></i>
            <div>
                <h6 class="fw-bold mb-1">Perhatian: Verifikasi Aset (Dampak Sinkronisasi SIPO)</h6>
                <span class="small text-dark">Terdapat <strong>{{ $verifikasiPending }}</strong> data aset yang memerlukan verifikasi karena perubahan organisasi/PIC dari sistem SIPO. 
                <a href="{{ route('aset.verifikasi') }}" class="fw-bold text-navy text-decoration-none">Lihat Detail <i class="fas fa-arrow-right ms-1"></i></a>
                </span>
            </div>
        </div>
    @endif

    {{-- TOP CARDS: METRIKS --}}
    <div class="row g-3 mb-4">
        {{-- Card 1: Total Aset --}}
        <div class="col-xl-3 col-sm-6">
            <div class="card glass-card h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="metric-icon icon-primary me-3">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small fw-bold text-uppercase">Total Aset</p>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalAset) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Aset Aktif --}}
        <div class="col-xl-3 col-sm-6">
            <div class="card glass-card h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="metric-icon icon-success me-3">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small fw-bold text-uppercase">Aset Aktif</p>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($asetAktif) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Aset Divisi --}}
        <div class="col-xl-3 col-sm-6">
            <div class="card glass-card h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="metric-icon icon-warning me-3" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small fw-bold text-uppercase">Aset Divisi</p>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalAsetDept) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Total Kategori --}}
        <div class="col-xl-3 col-sm-6">
            <div class="card glass-card h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="metric-icon icon-info me-3">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small fw-bold text-uppercase">Total Kategori Aset</p>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalKategori) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- BAGIAN KIRI: GRAFIK & OPNAME --}}
        <div class="col-12 col-lg-8">
            <div class="row g-4 mb-4">
                {{-- Grafik Kondisi Aset --}}
                <div class="col-md-6">
                    <div class="card glass-card h-100 p-4">
                        <h6 class="fw-bold text-navy mb-3"><i class="fas fa-chart-pie me-2"></i>Kondisi Aset</h6>
                        <div style="height: 250px; position: relative;">
                            <canvas id="kondisiChart"></canvas>
                        </div>
                    </div>
                </div>
                {{-- Grafik Status Aset --}}
                <div class="col-md-6">
                    <div class="card glass-card h-100 p-4">
                        <h6 class="fw-bold text-navy mb-3"><i class="fas fa-chart-doughnut me-2"></i>Status Aset</h6>
                        <div style="height: 250px; position: relative;">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Grafik Penambahan Aset per Bulan --}}
            <div class="card glass-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-navy mb-0"><i class="fas fa-chart-line me-2"></i>Tren Penambahan Data Aset</h6>
                    <select class="form-select form-select-sm w-auto bg-light border-0 shadow-sm rounded-pill" style="padding-left: 1rem; padding-right: 2.5rem; cursor: pointer;" onchange="window.location.href='{{ route('general-affairs.dashboard') }}?year=' + this.value">
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>Tahun {{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="height: 250px; position: relative;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            {{-- Grafik Kategori --}}
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card glass-card h-100 p-4">
                        <h6 class="fw-bold text-navy mb-3"><i class="fas fa-sitemap me-2"></i>Kategori Aset per Jenis</h6>
                        <div style="height: 300px; position: relative;">
                            <canvas id="jenisKategoriChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card glass-card h-100 p-4">
                        <h6 class="fw-bold text-navy mb-3"><i class="fas fa-layer-group me-2"></i>Distribusi Aset per Kategori (Top 10)</h6>
                        <div style="height: 300px; position: relative;">
                            <canvas id="kategoriChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Analisis Lokasi Aset --}}
            <h6 class="fw-bold text-dark mb-3 mt-4"><i class="fas fa-map-marked-alt text-primary me-2"></i>Analisis Penempatan & Lokasi Aset</h6>
            <div class="row g-4 mb-4">
                {{-- Kiri: Horizontal Bar Chart --}}
                <div class="col-md-8">
                    <div class="card glass-card h-100 p-4">
                        <h6 class="fw-bold text-navy mb-3">Distribusi Aset per Ruangan (Top 7)</h6>
                        <div style="height: 250px; position: relative;">
                            <canvas id="lokasiChart"></canvas>
                        </div>
                    </div>
                </div>
                
                {{-- Kanan: Highlight Cards --}}
                <div class="col-md-4 d-flex flex-column gap-3">
                    {{-- Card Lokasi Terpadat --}}
                    <div class="card glass-card border-0 shadow-sm flex-fill" style="background: linear-gradient(135deg, #f0f8ff 0%, #e6f2ff 100%);">
                        <div class="card-body p-3 d-flex flex-column justify-content-center">
                            <div class="d-flex align-items-center mb-2">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 40px; height: 40px;">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <h6 class="fw-bold text-primary mb-0" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Lokasi Terpadat</h6>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">{{ $namaLokasiTerpadat }}</h5>
                            <p class="text-muted small mb-0 fw-semibold"><span class="text-primary fs-5">{{ number_format($totalLokasiTerpadat) }}</span> Aset Tersimpan</p>
                        </div>
                    </div>

                    {{-- Card Zona Kritis --}}
                    <div class="card glass-card border-0 shadow-sm flex-fill" style="background: linear-gradient(135deg, #fff0f0 0%, #ffe6e6 100%);">
                        <div class="card-body p-3 d-flex flex-column justify-content-center">
                            <div class="d-flex align-items-center mb-2">
                                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 40px; height: 40px;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <h6 class="fw-bold text-danger mb-0" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Zona Kritis (Rusak)</h6>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">{{ $namaLokasiRusak }}</h5>
                            @if($totalLokasiRusak > 0)
                                <p class="text-muted small mb-0 fw-semibold"><span class="text-danger fs-5">{{ number_format($totalLokasiRusak) }}</span> Aset Butuh Perbaikan</p>
                            @else
                                <p class="text-muted small mb-0 fw-semibold text-success"><i class="fas fa-check-circle me-1"></i>Aman</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Progress Stock Opname --}}
            <div class="card glass-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-navy mb-0"><i class="fas fa-clipboard-check me-2"></i>Jadwal Opname Terakhir</h6>
                    @if($latestOpname)
                        <span class="badge bg-light text-dark border"><i class="fas fa-calendar-alt me-1"></i> {{ $latestOpname->tanggal_mulai->format('d M Y') }}</span>
                    @endif
                </div>
                
                @if($latestOpname)
                    <p class="text-muted small mb-2">Progres pengecekan fisik aset untuk periode <strong>{{ $latestOpname->periode }}</strong>.</p>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-dark">{{ $opnameProgress }}% Selesai</span>
                        <span class="text-muted small">{{ \App\Models\StockOpnameDetail::where('stock_opname_id', $latestOpname->id)->count() }} / {{ $totalAset }} Aset</span>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 10px; background-color: #e9ecef;">
                        <div class="progress-bar progress-bar-custom" role="progressbar" style="width: {{ $opnameProgress }}%" aria-valuenow="{{ $opnameProgress }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <p class="mb-0">Belum ada data jadwal Stock Opname.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- BAGIAN KANAN: QUICK ACCESS & PERBAIKAN --}}
        <div class="col-12 col-lg-4">
            
            {{-- Quick Access --}}
            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-bolt text-warning me-2"></i>Akses Cepat</h6>
            <div class="row g-2 mb-4">
                <div class="col-6">
                    <a href="{{ route('aset.scanner') }}" class="quick-access-btn shadow-sm">
                        <i class="fas fa-qrcode"></i>
                        <span class="fw-bold small text-center">Scan Aset</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('aset.index') }}" class="quick-access-btn shadow-sm">
                        <i class="fas fa-box-open"></i>
                        <span class="fw-bold small text-center">Data Aset</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('stock-opname.index') }}" class="quick-access-btn shadow-sm">
                        <i class="fas fa-boxes"></i>
                        <span class="fw-bold small text-center">Stock Opname</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('log-aset.index') }}" class="quick-access-btn shadow-sm">
                        <i class="fas fa-history"></i>
                        <span class="fw-bold small text-center">Log Monitoring</span>
                    </a>
                </div>
            </div>

            {{-- Chart Status Pengajuan Perbaikan --}}
            <div class="card glass-card p-4 mb-4">
                <h6 class="fw-bold text-navy mb-3"><i class="fas fa-chart-pie me-2"></i>Status Pengajuan Perbaikan</h6>
                <div style="height: 200px; position: relative;">
                    <canvas id="perbaikanChart"></canvas>
                </div>
            </div>

            {{-- Pengajuan Perbaikan --}}
            <div class="card glass-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-danger mb-0"><i class="fas fa-tools me-2"></i>Perbaikan Terbaru</h6>
                    <a href="{{ route('perbaikan.index') }}" class="small text-decoration-none">Lihat Semua</a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-borderless table-hover align-middle mb-0">
                        <tbody>
                            @forelse($perbaikanTerbaru as $pb)
                                <tr style="border-bottom: 1px solid rgba(0,0,0,.05);">
                                    <td class="px-0 py-2">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-danger me-2" style="width: 35px; height: 35px;">
                                                <i class="fas fa-wrench"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">{{ Str::limit($pb->aset->nama_aset ?? 'Aset Dihapus', 25) }}</h6>
                                                <small class="text-muted" style="font-size: 0.75rem;">{{ $pb->created_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end px-0 py-2">
                                        @if($pb->status == 'menunggu')
                                            <span class="badge bg-warning text-dark rounded-pill px-2" style="font-size: 0.7rem;">Menunggu</span>
                                        @elseif($pb->status == 'diproses')
                                            <span class="badge bg-info rounded-pill px-2" style="font-size: 0.7rem;">Diproses</span>
                                        @else
                                            <span class="badge bg-success rounded-pill px-2" style="font-size: 0.7rem;">{{ ucfirst($pb->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">
                                        <i class="fas fa-check-circle text-success fs-4 mb-2"></i><br>
                                        Tidak ada aset yang butuh perbaikan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Monitoring Terbaru --}}
            <div class="card glass-card p-4 mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-success mb-0"><i class="fas fa-search-location me-2"></i>Monitoring Terbaru</h6>
                    <a href="{{ route('log-aset.index') }}" class="small text-decoration-none">Lihat Semua</a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-borderless table-hover align-middle mb-0">
                        <tbody>
                            @forelse($monitoringTerbaru as $log)
                                <tr style="border-bottom: 1px solid rgba(0,0,0,.05);">
                                    <td class="px-0 py-2">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-success me-2" style="width: 35px; height: 35px;">
                                                <i class="fas fa-clipboard-list"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">{{ Str::limit($log->aset->nama_aset ?? 'Aset Dihapus', 25) }}</h6>
                                                <small class="text-muted" style="font-size: 0.75rem;">Oleh {{ explode(' ', $log->dicatatOleh->firstname)[0] ?? 'Sistem' }} &bull; {{ $log->created_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end px-0 py-2">
                                        @if($log->kondisi == 'Baik')
                                            <span class="badge bg-success rounded-pill px-2" style="font-size: 0.7rem;">Baik</span>
                                        @elseif($log->kondisi == 'Rusak')
                                            <span class="badge bg-danger rounded-pill px-2" style="font-size: 0.7rem;">Rusak</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill px-2" style="font-size: 0.7rem;">{{ ucfirst($log->kondisi) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">
                                        <i class="fas fa-history text-muted fs-4 mb-2"></i><br>
                                        Belum ada riwayat monitoring.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Data Kondisi Aset
        const kondisiData = @json($kondisiAset);
        const kondisiLabels = Object.keys(kondisiData).length > 0 ? Object.keys(kondisiData) : ['Belum ada data'];
        const kondisiValues = Object.keys(kondisiData).length > 0 ? Object.values(kondisiData) : [1];
        
        // Setup Chart Kondisi
        const ctxKondisi = document.getElementById('kondisiChart').getContext('2d');
        new Chart(ctxKondisi, {
            type: 'pie',
            data: {
                labels: kondisiLabels,
                datasets: [{
                    data: kondisiValues,
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#6c757d', '#17a2b8', '#fd7e14'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label !== 'Belum ada data') {
                                    return label + ': ' + context.parsed + ' Aset';
                                }
                                return '0 Aset';
                            }
                        }
                    }
                }
            }
        });

        // Data Status Aset
        const statusData = @json($statusAset);
        const statusLabels = Object.keys(statusData).length > 0 ? Object.keys(statusData) : ['Belum ada data'];
        const statusValues = Object.keys(statusData).length > 0 ? Object.values(statusData) : [1];

        // Setup Chart Status
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: ['#253070', '#48abf7', '#6c757d', '#dc3545', '#ffc107', '#28a745'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label !== 'Belum ada data') {
                                    return label + ': ' + context.parsed + ' Aset';
                                }
                                return '0 Aset';
                            }
                        }
                    }
                }
            }
        });

        // Data Penambahan Aset Bulanan
        const trendData = @json($monthlyAsetData);
        const trendLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        // Setup Chart Trend
        const ctxTrend = document.getElementById('trendChart').getContext('2d');
        new Chart(ctxTrend, {
            type: 'bar',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Aset Ditambahkan',
                    data: trendData,
                    backgroundColor: 'rgba(37, 48, 112, 0.8)',
                    borderColor: '#253070',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' Aset';
                            }
                        }
                    }
                }
            }
        });

        // Data Kategori Aset
        const kategoriDataObj = @json($kategoriStats);
        const kategoriLabels = Object.keys(kategoriDataObj);
        const kategoriValues = Object.values(kategoriDataObj);

        // Setup Chart Kategori (Pie)
        const ctxKategori = document.getElementById('kategoriChart').getContext('2d');
        new Chart(ctxKategori, {
            type: 'pie',
            data: {
                labels: kategoriLabels,
                datasets: [{
                    label: 'Jumlah Aset',
                    data: kategoriValues,
                    backgroundColor: [
                        '#253070', '#48abf7', '#6c757d', '#dc3545', '#ffc107', 
                        '#28a745', '#17a2b8', '#fd7e14', '#e83e8c', '#6f42c1'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' Aset';
                            }
                        }
                    }
                }
            }
        });

        // Data Jenis Kategori
        const jenisDataObj = @json($jenisKategoriStats);
        const jenisColorsObj = @json($jenisKategoriColors);
        const jenisLabels = Object.keys(jenisDataObj);
        const jenisValues = Object.values(jenisDataObj);
        const jenisColors = jenisLabels.map(label => jenisColorsObj[label] || '#253070');

        // Setup Chart Jenis Kategori (Pie)
        const ctxJenis = document.getElementById('jenisKategoriChart').getContext('2d');
        new Chart(ctxJenis, {
            type: 'pie',
            data: {
                labels: jenisLabels,
                datasets: [{
                    label: 'Jumlah Kategori',
                    data: jenisValues,
                    backgroundColor: jenisColors,
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' Sub-Kategori Aset';
                            }
                        }
                    }
                }
            }
        });

        // Data Lokasi Aset
        const lokasiDataObj = @json($lokasiStats);
        const lokasiLabels = Object.keys(lokasiDataObj);
        const lokasiValues = Object.values(lokasiDataObj);

        // Setup Chart Lokasi (Horizontal Bar)
        const ctxLokasi = document.getElementById('lokasiChart').getContext('2d');
        new Chart(ctxLokasi, {
            type: 'bar',
            data: {
                labels: lokasiLabels,
                datasets: [{
                    label: 'Total Aset',
                    data: lokasiValues,
                    backgroundColor: 'rgba(72, 171, 247, 0.85)',
                    borderColor: '#1b53a7',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.x + ' Aset Tersimpan';
                            }
                        }
                    }
                }
            }
        });

        // Data Status Pengajuan Perbaikan
        const perbaikanDataObj = @json($perbaikanStats);
        
        // Pemetaan Label dan Warna
        const statusMap = {
            'menunggu': { label: 'Menunggu', color: '#ffc107' },
            'disetujui': { label: 'Disetujui', color: '#17a2b8' },
            'diproses': { label: 'Diproses', color: '#48abf7' },
            'selesai': { label: 'Selesai', color: '#28a745' },
            'ditolak': { label: 'Ditolak', color: '#dc3545' }
        };

        const perbaikanLabels = [];
        const perbaikanValues = [];
        const perbaikanColors = [];

        if (Object.keys(perbaikanDataObj).length === 0) {
            perbaikanLabels.push('Belum ada data');
            perbaikanValues.push(1);
            perbaikanColors.push('#e9ecef');
        } else {
            for (const [status, total] of Object.entries(perbaikanDataObj)) {
                let mapped = statusMap[status.toLowerCase()] || { label: status.charAt(0).toUpperCase() + status.slice(1), color: '#6c757d' };
                perbaikanLabels.push(mapped.label);
                perbaikanValues.push(total);
                perbaikanColors.push(mapped.color);
            }
        }

        const ctxPerbaikan = document.getElementById('perbaikanChart').getContext('2d');
        new Chart(ctxPerbaikan, {
            type: 'doughnut',
            data: {
                labels: perbaikanLabels,
                datasets: [{
                    data: perbaikanValues,
                    backgroundColor: perbaikanColors,
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.label === 'Belum ada data') return 'Belum ada data';
                                return context.label + ': ' + context.parsed + ' Pengajuan';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
