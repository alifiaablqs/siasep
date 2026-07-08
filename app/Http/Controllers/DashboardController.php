<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        \Log::info('Dashboard Access', [
            'user_id' => $user->id,
            'role_id_role' => $user->role_id_role,
            'section_id_section' => $user->section_id_section,
        ]);

        // GA dashboard (kecuali superadmin)
        if ($user->role_id_role != 1 && $user->hasPermission('view_dashboard_ga')) {
            return redirect()->route('general-affairs.dashboard');
        }

        // Role check untuk dashboard umum
        if ($user->role_id_role == 1) {
            $totalAset = \App\Models\DataAset::count();
            $totalKategori = \App\Models\KategoriAset::count();
            $asetAktif = \App\Models\DataAset::where('status_aset', 'Aktif')->count();
            $asetBulanIni = \App\Models\DataAset::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->count();
            
            $verifikasiPending = \App\Models\DataAset::needsVerification()->count();
            
            // Grafik Kondisi Aset
            $kondisiAset = \App\Models\DataAset::select('status_kondisi', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                                               ->groupBy('status_kondisi')
                                               ->pluck('total', 'status_kondisi')->toArray();
            
            // Grafik Status Aset
            $statusAset = \App\Models\DataAset::select('status_aset', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                                              ->groupBy('status_aset')
                                              ->pluck('total', 'status_aset')->toArray();
            
            $latestOpname = \App\Models\StockOpname::latest('tanggal_mulai')->first();
            $opnameProgress = 0;
            if ($latestOpname) {
                $checked = \App\Models\StockOpnameDetail::where('stock_opname_id', $latestOpname->id)->count();
                $opnameProgress = $totalAset > 0 ? round(($checked / $totalAset) * 100) : 0;
            }

            // Grafik Penambahan Aset per Bulan
            $selectedYear = $request->get('year', date('Y'));
            $monthlyAssets = \App\Models\DataAset::select(
                \Illuminate\Support\Facades\DB::raw('MONTH(created_at) as month'),
                \Illuminate\Support\Facades\DB::raw('count(*) as total')
            )
            ->where('tahun_kapitalisasi', $selectedYear)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')->toArray();

            $monthlyAsetData = [];
            for ($i = 1; $i <= 12; $i++) {
                $monthlyAsetData[] = $monthlyAssets[$i] ?? 0;
            }

            // Ambil daftar tahun unik dari tahun_kapitalisasi untuk dropdown
            $availableYears = \App\Models\DataAset::select('tahun_kapitalisasi as year')
                ->whereNotNull('tahun_kapitalisasi')
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year')
                ->toArray();

            // Pastikan tahun ini dan tahun terpilih ada di dropdown
            if (!in_array(date('Y'), $availableYears)) {
                array_unshift($availableYears, date('Y'));
            }
            if (!in_array($selectedYear, $availableYears)) {
                $availableYears[] = $selectedYear;
                rsort($availableYears);
            }

            // Grafik Aset per Kategori (Top 10)
            $kategoriStats = \App\Models\DataAset::select('kategori_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                ->whereNotNull('kategori_id')
                ->groupBy('kategori_id')
                ->with('kategoriAset:id,nama')
                ->orderByDesc('total')
                ->take(10)
                ->get()
                ->mapWithKeys(function ($item) {
                    $namaKategori = $item->kategoriAset ? $item->kategoriAset->nama : 'Tanpa Kategori';
                    return [$namaKategori => $item->total];
                })->toArray();

            // Grafik Jumlah Kategori Aset per Jenis Kategori
            $jenisKategoriQuery = \App\Models\KategoriAset::select('jenis_kategori_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                ->whereNotNull('jenis_kategori_id')
                ->groupBy('jenis_kategori_id')
                ->with('jenisKategori:id,nama_jenis,warna_label')
                ->get();

            $jenisKategoriStats = $jenisKategoriQuery->mapWithKeys(function ($item) {
                    $namaJenis = $item->jenisKategori ? $item->jenisKategori->nama_jenis : 'Lainnya';
                    return [$namaJenis => $item->total];
                })->toArray();

            $jenisKategoriColors = $jenisKategoriQuery->mapWithKeys(function ($item) {
                $namaJenis = $item->jenisKategori ? $item->jenisKategori->nama_jenis : 'Lainnya';
                $warna = $item->jenisKategori && $item->jenisKategori->warna_label ? $item->jenisKategori->warna_label : '#253070';
                return [$namaJenis => $warna];
            })->toArray();

            // 1. Data Horizontal Bar Chart (Top 7 Lokasi Aset)
            $lokasiStatsRaw = \App\Models\DataAset::select('lokasi_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                ->whereNotNull('lokasi_id')
                ->groupBy('lokasi_id')
                ->with('lokasi')
                ->orderByDesc('total')
                ->take(7)
                ->get();

            $lokasiStats = $lokasiStatsRaw->mapWithKeys(function ($item) {
                $namaLokasi = $item->lokasi ? ($item->lokasi->nama_lokasi ?? $item->lokasi->nm_lokasi_aset) : 'Tanpa Lokasi';
                // Potong teks jika terlalu panjang agar rapi di chart
                $namaLokasi = strlen($namaLokasi) > 20 ? substr($namaLokasi, 0, 20) . '...' : $namaLokasi;
                return [$namaLokasi => $item->total];
            })->toArray();

            // 2. Lokasi Aset Terbanyak (Highlight Card 1)
            $lokasiTerpadat = $lokasiStatsRaw->first();
            $namaLokasiTerpadat = $lokasiTerpadat && $lokasiTerpadat->lokasi ? ($lokasiTerpadat->lokasi->nama_lokasi ?? $lokasiTerpadat->lokasi->nm_lokasi_aset) : 'Belum Ada Data';
            $totalLokasiTerpadat = $lokasiTerpadat ? $lokasiTerpadat->total : 0;

            // 3. Ruangan dengan Aset Rusak Terbanyak (Highlight Card 2)
            $lokasiRusak = \App\Models\DataAset::select('lokasi_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                ->where('status_kondisi', 'Rusak')
                ->whereNotNull('lokasi_id')
                ->groupBy('lokasi_id')
                ->with('lokasi')
                ->orderByDesc('total')
                ->first();

            $namaLokasiRusak = $lokasiRusak && $lokasiRusak->lokasi ? ($lokasiRusak->lokasi->nama_lokasi ?? $lokasiRusak->lokasi->nm_lokasi_aset) : 'Semua Ruangan Aman';
            $totalLokasiRusak = $lokasiRusak ? $lokasiRusak->total : 0;

            $perbaikanTerbaru = \App\Models\PengajuanPerbaikan::with('aset')->latest()->take(5)->get();
            
            $monitoringTerbaru = \App\Models\LogAset::with(['aset', 'dicatatOleh'])->latest()->take(5)->get();

            $perbaikanStats = \App\Models\PengajuanPerbaikan::select('status', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')->toArray();

            return view('dashboard.dashboard_superadmin', compact(
                'totalAset', 'totalKategori', 'asetAktif', 'asetBulanIni', 'verifikasiPending', 'kondisiAset', 'statusAset', 'latestOpname', 'opnameProgress', 'monthlyAsetData', 'kategoriStats', 'jenisKategoriStats', 'jenisKategoriColors', 'perbaikanTerbaru', 'perbaikanStats', 'monitoringTerbaru', 'selectedYear', 'availableYears',
                'lokasiStats', 'namaLokasiTerpadat', 'totalLokasiTerpadat', 'namaLokasiRusak', 'totalLokasiRusak'
            ));
        } elseif ($user->role_id_role == 3) {
            // Role 3 adalah Manager
            $totalAsetDept = \App\Models\DataAset::forUser($user)->count();
            $totalAsetDeptAktif = \App\Models\DataAset::forUser($user)->where('status_aset', 'Aktif')->count();
            
            $totalPerbaikan = \App\Models\PengajuanPerbaikan::whereHas('aset', function($q) use($user) {
                $q->forUser($user);
            })->count();

            $latestOpname = \App\Models\StockOpname::latest('tanggal_mulai')->first();
            $opnameProgress = 0;
            $deptCheckedCount = 0;
            if ($latestOpname) {
                $deptAssets = \App\Models\DataAset::forUser($user)->pluck('id');
                $deptCheckedCount = \App\Models\StockOpnameDetail::where('stock_opname_id', $latestOpname->id)
                    ->whereIn('aset_id', $deptAssets)
                    ->count();
                $opnameProgress = $totalAsetDept > 0 ? round(($deptCheckedCount / $totalAsetDept) * 100) : 0;
            }

            $perbaikanTerbaru = \App\Models\PengajuanPerbaikan::whereHas('aset', function($q) use($user) {
                $q->forUser($user);
            })->with(['aset', 'pengaju'])->latest()->take(5)->get();

            $monitoringTerbaru = \App\Models\LogAset::whereHas('aset', function($q) use($user) {
                $q->forUser($user);
            })->with(['aset', 'dicatatOleh'])->latest()->take(5)->get();

            return view('dashboard.dashboard_manager', compact(
                'totalAsetDept', 'totalAsetDeptAktif', 'totalPerbaikan', 'latestOpname', 'opnameProgress', 'deptCheckedCount', 'perbaikanTerbaru', 'monitoringTerbaru'
            ));
        } else {
            // Role 2 dan Role lainnya (Staff)
            $totalAsetDept = \App\Models\DataAset::forUser($user)->count();
            $totalAsetPic = \App\Models\DataAset::where('pic_id', $user->id)->count();
            
            $totalPerbaikan = \App\Models\PengajuanPerbaikan::where('diajukan_oleh', $user->id)->count();

            $latestOpname = \App\Models\StockOpname::latest('tanggal_mulai')->first();
            $opnameProgress = 0;
            $deptCheckedCount = 0;
            if ($latestOpname) {
                $deptAssets = \App\Models\DataAset::forUser($user)->pluck('id');
                $deptCheckedCount = \App\Models\StockOpnameDetail::where('stock_opname_id', $latestOpname->id)
                    ->whereIn('aset_id', $deptAssets)
                    ->count();
                $opnameProgress = $totalAsetDept > 0 ? round(($deptCheckedCount / $totalAsetDept) * 100) : 0;
            }

            $perbaikanTerbaru = \App\Models\PengajuanPerbaikan::where('diajukan_oleh', $user->id)
                ->with('aset')->latest()->take(5)->get();

            $monitoringTerbaru = \App\Models\LogAset::whereHas('aset', function($q) use($user) {
                $q->forUser($user);
            })->with(['aset', 'dicatatOleh'])->latest()->take(5)->get();

            return view('dashboard.dashboard_user', compact(
                'totalAsetDept', 'totalAsetPic', 'totalPerbaikan', 'latestOpname', 'opnameProgress', 'deptCheckedCount', 'perbaikanTerbaru', 'monitoringTerbaru'
            ));
        }
    }

    public function generalAffairsDashboard(Request $request)
    {
        $user = Auth::user();
        
        // lihat data user
        \Log::info('GA Dashboard Access', [
            'user_id' => $user->id,
            'user_fullname' => $user->firstname . ' ' . $user->lastname,
            'role_id_role' => $user->role_id_role,
            'section_id_section' => $user->section_id_section,
            'department_id_department' => $user->department_id_department,
        ]);

        // Allow hanya jika memiliki permission 'view_dashboard_ga'
        if (!$user->hasPermission('view_dashboard_ga')) {
            abort(403, 'Akses dashboard bagian umum hanya untuk staff bagian umum.');
        }

        $totalAset = \App\Models\DataAset::count();
        $totalKategori = \App\Models\KategoriAset::count();
        $asetAktif = \App\Models\DataAset::where('status_aset', 'Aktif')->count();
        $totalAsetDept = \App\Models\DataAset::forUser($user)->count();
        
        $verifikasiPending = \App\Models\DataAset::needsVerification()->count();

        // Grafik Kondisi Aset
        $kondisiAset = \App\Models\DataAset::select('status_kondisi', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                                           ->groupBy('status_kondisi')
                                           ->pluck('total', 'status_kondisi')->toArray();
        
        // Grafik Status Aset
        $statusAset = \App\Models\DataAset::select('status_aset', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                                          ->groupBy('status_aset')
                                          ->pluck('total', 'status_aset')->toArray();
        
        $latestOpname = \App\Models\StockOpname::latest('tanggal_mulai')->first();
        $opnameProgress = 0;
        if ($latestOpname) {
            $checked = \App\Models\StockOpnameDetail::where('stock_opname_id', $latestOpname->id)->count();
            $opnameProgress = $totalAset > 0 ? round(($checked / $totalAset) * 100) : 0;
        }

        // Grafik Penambahan Aset per Bulan
        $selectedYear = $request->get('year', date('Y'));
        $monthlyAssets = \App\Models\DataAset::select(
            \Illuminate\Support\Facades\DB::raw('MONTH(created_at) as month'),
            \Illuminate\Support\Facades\DB::raw('count(*) as total')
        )
        ->where('tahun_kapitalisasi', $selectedYear)
        ->groupBy('month')
        ->orderBy('month')
        ->pluck('total', 'month')->toArray();

        $monthlyAsetData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyAsetData[] = $monthlyAssets[$i] ?? 0;
        }

        // Ambil daftar tahun unik dari tahun_kapitalisasi untuk dropdown
        $availableYears = \App\Models\DataAset::select('tahun_kapitalisasi as year')
            ->whereNotNull('tahun_kapitalisasi')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        // Pastikan tahun ini dan tahun terpilih ada di dropdown
        if (!in_array(date('Y'), $availableYears)) {
            array_unshift($availableYears, date('Y'));
        }
        if (!in_array($selectedYear, $availableYears)) {
            $availableYears[] = $selectedYear;
            rsort($availableYears);
        }

        // Grafik Aset per Kategori (Top 10)
        $kategoriStats = \App\Models\DataAset::select('kategori_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->whereNotNull('kategori_id')
            ->groupBy('kategori_id')
            ->with('kategoriAset:id,nama')
            ->orderByDesc('total')
            ->take(10)
            ->get()
            ->mapWithKeys(function ($item) {
                $namaKategori = $item->kategoriAset ? $item->kategoriAset->nama : 'Tanpa Kategori';
                return [$namaKategori => $item->total];
            })->toArray();

        // Grafik Jumlah Kategori Aset per Jenis Kategori
        $jenisKategoriQuery = \App\Models\KategoriAset::select('jenis_kategori_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->whereNotNull('jenis_kategori_id')
            ->groupBy('jenis_kategori_id')
            ->with('jenisKategori:id,nama_jenis,warna_label')
            ->get();

        $jenisKategoriStats = $jenisKategoriQuery->mapWithKeys(function ($item) {
                $namaJenis = $item->jenisKategori ? $item->jenisKategori->nama_jenis : 'Lainnya';
                return [$namaJenis => $item->total];
            })->toArray();

        $jenisKategoriColors = $jenisKategoriQuery->mapWithKeys(function ($item) {
            $namaJenis = $item->jenisKategori ? $item->jenisKategori->nama_jenis : 'Lainnya';
            $warna = $item->jenisKategori && $item->jenisKategori->warna_label ? $item->jenisKategori->warna_label : '#253070';
            return [$namaJenis => $warna];
        })->toArray();

        // 1. Data Horizontal Bar Chart (Top 7 Lokasi Aset)
        $lokasiStatsRaw = \App\Models\DataAset::select('lokasi_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->whereNotNull('lokasi_id')
            ->groupBy('lokasi_id')
            ->with('lokasi')
            ->orderByDesc('total')
            ->take(7)
            ->get();

        $lokasiStats = $lokasiStatsRaw->mapWithKeys(function ($item) {
            $namaLokasi = $item->lokasi ? ($item->lokasi->nama_lokasi ?? $item->lokasi->nm_lokasi_aset) : 'Tanpa Lokasi';
            // Potong teks jika terlalu panjang agar rapi di chart
            $namaLokasi = strlen($namaLokasi) > 20 ? substr($namaLokasi, 0, 20) . '...' : $namaLokasi;
            return [$namaLokasi => $item->total];
        })->toArray();

        // 2. Lokasi Aset Terbanyak (Highlight Card 1)
        $lokasiTerpadat = $lokasiStatsRaw->first();
        $namaLokasiTerpadat = $lokasiTerpadat && $lokasiTerpadat->lokasi ? ($lokasiTerpadat->lokasi->nama_lokasi ?? $lokasiTerpadat->lokasi->nm_lokasi_aset) : 'Belum Ada Data';
        $totalLokasiTerpadat = $lokasiTerpadat ? $lokasiTerpadat->total : 0;

        // 3. Ruangan dengan Aset Rusak Terbanyak (Highlight Card 2)
        $lokasiRusak = \App\Models\DataAset::select('lokasi_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->where('status_kondisi', 'Rusak')
            ->whereNotNull('lokasi_id')
            ->groupBy('lokasi_id')
            ->with('lokasi')
            ->orderByDesc('total')
            ->first();

        $namaLokasiRusak = $lokasiRusak && $lokasiRusak->lokasi ? ($lokasiRusak->lokasi->nama_lokasi ?? $lokasiRusak->lokasi->nm_lokasi_aset) : 'Semua Ruangan Aman';
        $totalLokasiRusak = $lokasiRusak ? $lokasiRusak->total : 0;

        $perbaikanTerbaru = \App\Models\PengajuanPerbaikan::with('aset')->latest()->take(5)->get();
        
        $monitoringTerbaru = \App\Models\LogAset::with(['aset', 'dicatatOleh'])->latest()->take(5)->get();

        $perbaikanStats = \App\Models\PengajuanPerbaikan::select('status', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')->toArray();

        return view('dashboard.dashboard_ga', compact(
            'totalAset', 'totalKategori', 'asetAktif', 'totalAsetDept', 'verifikasiPending', 'kondisiAset', 'statusAset', 'latestOpname', 'opnameProgress', 'monthlyAsetData', 'kategoriStats', 'jenisKategoriStats', 'jenisKategoriColors', 'perbaikanTerbaru', 'perbaikanStats', 'monitoringTerbaru', 'selectedYear', 'availableYears',
            'lokasiStats', 'namaLokasiTerpadat', 'totalLokasiTerpadat', 'namaLokasiRusak', 'totalLokasiRusak'
        ));
    }
}