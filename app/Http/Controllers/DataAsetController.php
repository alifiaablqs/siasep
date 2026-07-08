<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataAset;
use App\Models\AsetFoto;
use App\Models\KategoriAset;
use App\Models\JenisKategori;
use App\Models\LokasiAset;
use App\Models\User;
use App\Models\Director;
use Illuminate\Support\Facades\Storage;
use App\Exports\DataAsetExport;
use App\Imports\DataAsetImport;
use App\Exports\TemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class DataAsetController extends Controller
{
    use \App\Traits\HandlesImageUploads;
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');
        $kondisi = $request->input('kondisi');
        $status  = $request->input('status_aset');
        $jenisKategoriId = $request->input('jenis_kategori_id');
        $kategoriId = $request->input('kategori_id');
        $sortBy  = $request->input('sort_by', 'nomor_aset');
        $orderBy = $request->input('order_by', 'asc');

        // Whitelist columns to prevent SQL injection
        $allowedSortColumns = ['nomor_aset', 'nama_aset', 'status_kondisi', 'status_aset', 'lokasi_id'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'nomor_aset';
        }
        if (!in_array($orderBy, ['asc', 'desc'])) {
            $orderBy = 'asc';
        }

        $query = DataAset::verified()->with(['kategoriAset', 'director', 'divisi', 'department', 'section', 'unit', 'lokasi', 'pic', 'fotoPertama']);

        $user = auth()->user();
        $isAdmin = $user->role_id_role == 1 || $user->isBagianUmum();

        $filterOwnDept = $request->boolean('filter_own_dept') || !$isAdmin;

        if ($filterOwnDept) {
            $query->forUser($user);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nomor_aset', 'LIKE', "%{$search}%")
                  ->orWhere('nama_aset', 'LIKE', "%{$search}%")
                  ->orWhereHas('kategoriAset', function($qj) use ($search) {
                      $qj->where('nama', 'LIKE', "%{$search}%")
                         ->orWhere('kode', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($kondisi) {
            $query->where('status_kondisi', $kondisi);
        }

        if ($status) {
            $query->where('status_aset', $status);
        }

        if ($jenisKategoriId != '') {
            $query->whereHas('kategoriAset', function($q) use ($jenisKategoriId) {
                $q->where('jenis_kategori_id', $jenisKategoriId);
            });
        }

        if ($kategoriId != '') {
            $query->where('kategori_id', $kategoriId);
        }

        $lokasiId = $request->input('lokasi');
        $divisiId = $request->input('divisi_id');
        $departmentId = $request->input('department_id');

        if ($filterOwnDept) {
            $divisiId = null;
            $departmentId = null;
        }

        if ($lokasiId != '') {
            $query->where('lokasi_id', $lokasiId);
        }

        if ($divisiId) {
            if ($departmentId) {
                $exists = \App\Models\Department::where('id_department', $departmentId)
                    ->where('divisi_id_divisi', $divisiId)
                    ->exists();
                if (!$exists) {
                    $departmentId = null;
                    $request->merge(['department_id' => null]);
                }
            }
        }

        if ($departmentId != '') {
            $query->where('id_department', $departmentId);
        }

        if ($divisiId != '') {
            $query->where(function($q) use ($divisiId) {
                $q->where('id_divisi', $divisiId)
                  ->orWhereHas('department', function($qd) use ($divisiId) {
                      $qd->where('divisi_id_divisi', $divisiId);
                  })
                  ->orWhereHas('section.department', function($qsd) use ($divisiId) {
                      $qsd->where('divisi_id_divisi', $divisiId);
                  })
                  ->orWhereHas('unit.department', function($qud) use ($divisiId) {
                      $qud->where('divisi_id_divisi', $divisiId);
                  })
                  ->orWhereHas('unit.section.department', function($qusd) use ($divisiId) {
                      $qusd->where('divisi_id_divisi', $divisiId);
                  });
            });
        }

        $asets = $query->orderBy($sortBy, $orderBy)
                       ->paginate($perPage)
                       ->withQueryString();

        $lokasis = LokasiAset::all();

        $departmentsQuery = \App\Models\Department::query();
        if ($divisiId != '') {
            $departmentsQuery->where('divisi_id_divisi', $divisiId);
        }
        $departments = $departmentsQuery->get();

        $divisis = \App\Models\Divisi::all();
        $jenisList = JenisKategori::orderBy('nama_jenis')->get();
        $kategoriQuery = KategoriAset::query();
        if ($jenisKategoriId != '') {
            $kategoriQuery->where('jenis_kategori_id', $jenisKategoriId);
        }
        $kategoris = $kategoriQuery->orderBy('nama')->get();

        $pageTitle = ($isAdmin && !$filterOwnDept) ? "Data Aset Perusahaan" : "Data Aset Divisi";
        $showAdminActions = $isAdmin && !$filterOwnDept;

        return view('aset.index', compact('asets', 'lokasis', 'departments', 'divisis', 'pageTitle', 'jenisList', 'kategoris', 'showAdminActions', 'filterOwnDept'));
    }

    public function picIndex(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');
        $kondisi = $request->input('kondisi');
        $status  = $request->input('status_aset');
        $jenisKategoriId = $request->input('jenis_kategori_id');
        $kategoriId = $request->input('kategori_id');

        $query = DataAset::verified()->with(['kategoriAset', 'director', 'divisi', 'department', 'section', 'unit', 'lokasi', 'pic', 'fotoPertama']);

        $user = auth()->user();

        // Hanya tampilkan aset yang PIC-nya adalah user login
        $query->where('pic_id', $user->id);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nomor_aset', 'LIKE', "%{$search}%")
                  ->orWhere('nama_aset', 'LIKE', "%{$search}%")
                  ->orWhereHas('kategoriAset', function($qj) use ($search) {
                      $qj->where('nama', 'LIKE', "%{$search}%")
                         ->orWhere('kode', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($kondisi) {
            $query->where('status_kondisi', $kondisi);
        }

        if ($status) {
            $query->where('status_aset', $status);
        }

        if ($jenisKategoriId != '') {
            $query->whereHas('kategoriAset', function($q) use ($jenisKategoriId) {
                $q->where('jenis_kategori_id', $jenisKategoriId);
            });
        }

        if ($kategoriId != '') {
            $query->where('kategori_id', $kategoriId);
        }

        if ($request->has('lokasi') && $request->input('lokasi') != '') {
            $query->where('lokasi_id', $request->input('lokasi'));
        }

        $asets = $query->latest()
                       ->paginate($perPage)
                       ->withQueryString();

        $lokasis = LokasiAset::all();
        $departments = \App\Models\Department::all();
        $divisis = \App\Models\Divisi::all();
        $jenisList = JenisKategori::orderBy('nama_jenis')->get();
        $kategoriQuery = KategoriAset::query();
        if ($jenisKategoriId != '') {
            $kategoriQuery->where('jenis_kategori_id', $jenisKategoriId);
        }
        $kategoris = $kategoriQuery->orderBy('nama')->get();

        $pageTitle = "Data Aset PIC Saya";

        return view('aset.index', compact('asets', 'lokasis', 'departments', 'divisis', 'pageTitle', 'jenisList', 'kategoris'));
    }

    /**
     * Menampilkan daftar aset yang membutuhkan verifikasi.
     */
    public function verificationIndex(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        $query = DataAset::needsVerification()->with(['kategoriAset', 'director', 'divisi', 'department', 'section', 'unit', 'lokasi', 'pic', 'fotoPertama']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nomor_aset', 'LIKE', "%{$search}%")
                  ->orWhere('nama_aset', 'LIKE', "%{$search}%");
            });
        }

        $asets = $query->latest()
                       ->paginate($perPage)
                       ->withQueryString();

        $pageTitle = "Verifikasi Aset";
        
        // Kita butuh variabel kosong untuk menghindari error di view aset.index jika dipakai ulang (atau nanti pakai view terpisah)
        $lokasis = LokasiAset::all();
        $departments = collect();
        $divisis = collect();
        $jenisList = collect();
        $kategoris = collect();
        $showAdminActions = false;
        $filterOwnDept = false;

        return view('aset.verification', compact('asets', 'lokasis', 'departments', 'divisis', 'pageTitle', 'jenisList', 'kategoris', 'showAdminActions', 'filterOwnDept'));
    }

    /**
     * Menampilkan form untuk membuat aset baru.
     */
    public function create()
    {
        $nextId   = DataAset::getNextNomorUrut();

        $mainDirector = Director::with([
            'subDirectors',
            'divisi.department.section.unit'
        ])->whereNull('parent_director_id')->first();

        // Ambil semua kategori dikelompokkan per jenis untuk dropdown
        $jenisList          = JenisKategori::orderBy('kode_awalan')->get();
        $kategoriGrouped    = KategoriAset::with('jenisKategori')->orderBy('kode')->get()->groupBy('jenis_kategori_id');
        // Backward-compat: tetap kirim kategoriTetap & kategoriInventaris untuk view lama
        $kategoriTetap      = $kategoriGrouped->filter(fn($items, $key) =>
            JenisKategori::find($key)?->kode_awalan === '1'
        )->flatten();
        $kategoriInventaris = $kategoriGrouped->filter(fn($items, $key) =>
            JenisKategori::find($key)?->kode_awalan !== '1'
        )->flatten();
        $lokasi               = LokasiAset::all();
        $users                = User::all();

        return view('aset.create', compact(
            'mainDirector', 'jenisList', 'kategoriGrouped', 'kategoriTetap', 'kategoriInventaris', 'lokasi', 'users', 'nextId'
        ));
    }

    /**
     * Menyimpan data aset baru ke database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_aset'            => 'required|string|max:150',
            'kategori_id'          => 'required|integer|exists:kategori_aset,id',
            'kode_organisasi'      => 'required|string',
            'lokasi_id'            => 'required|integer',
            'merek'                => 'required|string|max:100',
            'deskripsi'            => 'required|string',
            'tahun_kapitalisasi'   => 'required|integer|min:1900|max:2100',
            'pic_id'               => 'required|integer',
            'penanggung_jawab_id'  => 'required|integer',
            'bast'                 => 'nullable|string|max:255',
            'status_kondisi'       => 'required|in:Baik,Rusak,Bongkar,Tidak Terpakai,Hilang,Tidak Teridentifikasi',
            'status_aset'          => 'required|in:Aktif,Tidak Aktif,Dalam Perbaikan,Dipinjam,Hilang',
            'nomor_urut'           => 'required|digits_between:1,5',
            'keterangan'           => 'nullable|string',
            // Multi-foto
            'foto'                 => 'required|array|min:1|max:10',
            'foto.*'               => 'image|mimes:jpeg,png,jpg|max:4096',
        ]);



        if (isset($validatedData['kode_organisasi'])) {
            $parts = explode('_', $validatedData['kode_organisasi']);
            if (count($parts) === 2) {
                $type = $parts[0];
                $id = $parts[1];
                $validatedData["id_{$type}"] = $id;
            }
            unset($validatedData['kode_organisasi']);
        }

        $aset = DataAset::create($validatedData);

        // Simpan foto
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $i => $file) {
                $savedPath = $this->compressAndStore($file, 'dokumentasi_aset');
                
                if ($savedPath) {
                    AsetFoto::create([
                        'aset_id'   => $aset->id,
                        'path_foto' => $savedPath,
                        'urutan'    => $i + 1,
                    ]);
                }
            }
        }

        return redirect()->route('aset.index')
            ->with('success', 'Aset berhasil ditambahkan dengan Nomor: ' . $aset->nomor_aset);
    }

    /**
     * Menampilkan detail.
     */
    public function show($id)
    {
        $aset = DataAset::with([
            'kategoriAset',
            'director', 'divisi', 'department', 'section', 'unit',
            'lokasi',
            'pic',
            'foto',
            'logAset' => fn($q) => $q->latest('tanggal_cek')->limit(10),
        ])->findOrFail($id);

        if (!auth()->check()) {
            return view('aset.public_show', compact('aset'));
        }

        $user = auth()->user();
        $isAdmin = $user->role_id_role == 1 || $user->isBagianUmum();

        if (!$isAdmin) {
            $isAuthorized = DataAset::where('id', $aset->id)->forUser($user)->exists() || $aset->pic_id == $user->id;

            if (!$isAuthorized) {
                abort(403, 'Anda tidak memiliki akses untuk melihat detail aset dari departemen lain.');
            }
        }

        $mainDirector = Director::with([
            'subDirectors',
            'divisi.department.section.unit'
        ])->whereNull('parent_director_id')->first();

        $lokasi = LokasiAset::all();

        return view('aset.show', compact('aset', 'mainDirector', 'lokasi'));
    }

    /**
     * Menampilkan halaman scanner barcode.
     */
    public function scanner()
    {
        $activeOpnames = \App\Models\StockOpname::where('status', 'aktif')->get();
        $lokasis = LokasiAset::all();
        return view('aset.scanner', compact('activeOpnames', 'lokasis'));
    }

    
    /**
     * Memproses hasil scan barcode.
     */
    public function scanProses(Request $request)
    {
        $request->validate([
            'nomor_aset' => 'required|string'
        ]);

        $inputData = trim($request->nomor_aset);
        $displayId = $inputData;

        // Cek hasil scan berupa URL
        if (filter_var($inputData, FILTER_VALIDATE_URL)) {
            // Ekstrak path dari URL
            $path = parse_url($inputData, PHP_URL_PATH);
            $segments = explode('/', trim($path, '/'));
            
            // Ambil ID aset
            $id = end($segments);
            $displayId = $id;

            $aset = DataAset::find($id);
            if ($aset) {
                return redirect()->route('aset.show', $aset->id)
                    ->with('success', 'Aset berhasil ditemukan dari QR code (URL).');
            }
        }

        //input manual nomor aset
        $aset = DataAset::where('nomor_aset', $inputData)->first();
        
        if (!$aset && filter_var($inputData, FILTER_VALIDATE_URL)) {
            $aset = DataAset::where('nomor_aset', $displayId)->first();
        }

        if ($aset) {
            return redirect()->route('aset.show', $aset->id)
                ->with('success', 'Aset berhasil ditemukan.');
        }

        return redirect()->route('aset.scanner')
            ->with('error', 'Aset dengan nomor aset "' . $displayId . '" tidak ditemukan.');
    }

    /**
     * Menampilkan form untuk mengedit aset.
     */
    public function edit($id)
    {
        $aset = DataAset::with('foto')->findOrFail($id);

        $jenisList          = JenisKategori::orderBy('kode_awalan')->get();
        $kategoriGrouped    = KategoriAset::with('jenisKategori')->orderBy('kode')->get()->groupBy('jenis_kategori_id');
        $kategoriTetap      = $kategoriGrouped->filter(fn($items, $key) =>
            JenisKategori::find($key)?->kode_awalan === '1'
        )->flatten();
        $kategoriInventaris = $kategoriGrouped->filter(fn($items, $key) =>
            JenisKategori::find($key)?->kode_awalan !== '1'
        )->flatten();
        $lokasi               = LokasiAset::all();
        $users                = User::all();

        $mainDirector = \App\Models\Director::with([
            'subDirectors',
            'divisi.department.section.unit'
        ])->whereNull('parent_director_id')->first();

        return view('aset.edit', compact(
            'aset', 'jenisList', 'kategoriGrouped', 'kategoriTetap', 'kategoriInventaris', 'lokasi', 'users', 'mainDirector'
        ));
    }

    /**
     * Menyimpan perubahan data aset.
     */
    public function update(Request $request, $id)
    {
        $aset = DataAset::findOrFail($id);

        $validatedData = $request->validate([
            'nama_aset'            => 'required|string|max:150',
            'kategori_id'          => 'required|integer|exists:kategori_aset,id',
            'kode_organisasi'      => 'required|string',
            'lokasi_id'            => 'required|integer',
            'merek'                => 'required|string|max:100',
            'deskripsi'            => 'required|string',
            'tahun_kapitalisasi'   => 'required|integer|min:1900|max:2100',
            'pic_id'               => 'required|integer',
            'penanggung_jawab_id'  => 'required|integer',
            'bast'                 => 'nullable|string|max:255',
            'status_kondisi'       => 'required|in:Baik,Rusak,Bongkar,Tidak Terpakai,Hilang,Tidak Teridentifikasi',
            'status_aset'          => 'required|in:Aktif,Tidak Aktif,Dalam Perbaikan,Dipinjam,Hilang',
            'nomor_urut'           => 'required|digits_between:1,5',
            'keterangan'           => 'nullable|string',
            // Tambah foto baru
            'foto_baru'            => 'nullable|array|max:10',
            'foto_baru.*'          => 'image|mimes:jpeg,png,jpg|max:4096',
            // ID foto yang mau dihapus
            'hapus_foto'           => 'nullable|array',
            'hapus_foto.*'         => 'integer|exists:aset_foto,id',
        ]);



        // Simpan state lama untuk pengecekan log
        $oldOrgName = $aset->organisasi_terikat;
        $wasNeedingVerification = $aset->hasVerificationIssues();

        if (isset($validatedData['kode_organisasi'])) {
            $parts = explode('_', $validatedData['kode_organisasi']);
            if (count($parts) === 2) {
                $type = $parts[0];
                $id = $parts[1];
                
                // Reset semua org ID ke null
                $aset->id_director = null;
                $aset->id_divisi = null;
                $aset->id_department = null;
                $aset->id_section = null;
                $aset->id_unit = null;

                // Assign id baru yang benar
                $aset->{"id_{$type}"} = $id;
            }
            unset($validatedData['kode_organisasi']);
        }

        // Clear verification flags since the data is now being manually updated/verified
        $validatedData['needs_org_verification'] = false;
        $validatedData['needs_pic_verification'] = false;
        $validatedData['needs_pj_verification'] = false;

        $aset->update($validatedData);

        // Jika sebelumnya butuh verifikasi dan organisasi berubah, catat ke LogAset
        $newOrgName = $aset->fresh()->organisasi_terikat;
        if ($wasNeedingVerification && $oldOrgName !== $newOrgName) {
            \App\Models\LogAset::create([
                'aset_id' => $aset->id,
                'user_id' => auth()->id(),
                'tanggal_cek' => now(),
                'kondisi' => $aset->status_kondisi,
                'keterangan' => "Aset diverifikasi dan dipindahkan dari [{$oldOrgName}] ke [{$newOrgName}].",
            ]);
        }



        // Hapus foto yang diminta
        if ($request->has('hapus_foto')) {
            foreach ($request->hapus_foto as $fotoId) {
                $foto = AsetFoto::find($fotoId);
                if ($foto && $foto->aset_id === $aset->id) {
                    // Hapus dari penyimpanan lokal jika berupa path lokal (bukan URL)
                    if (!filter_var($foto->path_foto, FILTER_VALIDATE_URL)) {
                        Storage::disk('public')->delete($foto->path_foto);
                    }
                    $foto->delete();
                }
            }
        }

        // Tambah foto baru
        if ($request->hasFile('foto_baru')) {
            $urutanTerakhir = $aset->foto()->max('urutan') ?? 0;
            foreach ($request->file('foto_baru') as $i => $file) {
                $savedPath = $this->compressAndStore($file, 'dokumentasi_aset');

                if ($savedPath) {
                    AsetFoto::create([
                        'aset_id'   => $aset->id,
                        'path_foto' => $savedPath,
                        'urutan'    => $urutanTerakhir + $i + 1,
                    ]);
                }
            }
        }

        return redirect()->route('aset.index')
            ->with('success', 'Data aset berhasil diperbarui!');
    }

    /**
     * Menghapus data aset beserta semua foto terkait.
     */
    public function destroy(Request $request, $id)
    {
        $aset = DataAset::findOrFail($id);

        $request->validate([
            'dokumen_penghapusan' => 'required|mimes:pdf|max:5120',
        ]);

        if ($request->hasFile('dokumen_penghapusan')) {
            $path = $request->file('dokumen_penghapusan')->store('dokumen_penghapusan', 'public');
            $aset->dokumen_penghapusan = $path;
            $aset->save();
        }

        // Hapus record aset (Soft Delete)
        $aset->delete();

        return redirect()->route('aset.index')
            ->with('success', 'Data aset berhasil dihapus dan masuk ke menu pemulihan!');
    }

    /**
     * Cetak Label Aset Tertentu (Multi-select)
     */
    /**
     * Menyimpan pilihan ID ke session dan redirect ke halaman cetak
     */
    public function processCetakLabelSelected(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:data_aset,id'
        ]);

        session(['cetak_label_ids' => $request->ids]);
        return redirect()->route('aset.cetak-label');
    }

    /**
     * Cetak Label Aset Tertentu (Multi-select)
     */
    public function cetakLabelSelected()
    {
        $ids = session('cetak_label_ids');

        if (!$ids) {
            return redirect()->route('aset.index')->with('error', 'Silakan pilih aset yang ingin dicetak terlebih dahulu.');
        }

        $asets = DataAset::with(['kategoriAset', 'lokasi'])->whereIn('id', $ids)->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('aset.print_label_pdf', compact('asets'))
                ->setPaper('a4', 'portrait');

        return $pdf->stream('Label_Aset_Selected.pdf');
    }

    /**
     * Preview Aset Per Lokasi untuk Modal
     */
    public function previewAsetLokasi($lokasi_id)
    {
        $asets = DataAset::with('kategoriAset')
            ->where('lokasi_id', $lokasi_id)
            ->get();
            
        return response()->json($asets);
    }

    /**
     * Cetak Label Aset Per Lokasi
     */
    /**
     * Menyimpan pilihan lokasi ke session dan redirect
     */
    public function processCetakLabelPerLokasi(Request $request)
    {
        $request->validate([
            'lokasi_id' => 'required|exists:lokasi_aset,lokasi_id'
        ]);

        session(['cetak_label_lokasi_id' => $request->lokasi_id]);
        return redirect()->route('aset.cetak-label-lokasi');
    }

    /**
     * Cetak Label Aset Per Lokasi
     */
    public function cetakLabelPerLokasi()
    {
        $lokasi_id = session('cetak_label_lokasi_id');

        if (!$lokasi_id) {
            return redirect()->route('aset.index')->with('error', 'Silakan pilih lokasi yang ingin dicetak terlebih dahulu.');
        }

        $asets = DataAset::with(['kategoriAset', 'lokasi'])
            ->where('lokasi_id', $lokasi_id)
            ->get();
            
        if ($asets->isEmpty()) {
            return redirect()->route('aset.index')->with('error', 'Tidak ada aset di ruangan ini.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('aset.print_label_pdf', compact('asets'))
                ->setPaper('a4', 'portrait');

        return $pdf->stream("Label_Aset_Lokasi_{$lokasi_id}.pdf");
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $kondisi = $request->input('kondisi');
        $status = $request->input('status_aset');
        $lokasiId = $request->input('lokasi');
        $departmentId = $request->input('department_id');
        $divisiId = $request->input('divisi_id');
        $jenisKategoriId = $request->input('jenis_kategori_id');

        if ($divisiId) {
            if ($departmentId) {
                $exists = \App\Models\Department::where('id_department', $departmentId)
                    ->where('divisi_id_divisi', $divisiId)
                    ->exists();
                if (!$exists) {
                    $departmentId = null;
                }
            }
        }

        return Excel::download(new DataAsetExport($search, $kondisi, $status, $lokasiId, false, $departmentId, $divisiId, $jenisKategoriId), 'Data_Aset.xlsx');
    }

    /**
     * Mengimpor data aset dari file Excel bertingkat
     */
    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls|max:5120',
        ]);

        try {
            Excel::import(new DataAsetImport, $request->file('file_excel'));
            return redirect()->route('aset.index')->with('success', 'Data aset berhasil diimpor!');
        } catch (\Exception $e) {
            return redirect()->route('aset.index')->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }

    /**
     * Mengunduh template Excel bertingkat untuk impor
     */
    public function downloadTemplate()
    {
        return Excel::download(new DataAsetExport(null, null, null, null, true), 'Template_Import_Aset.xlsx');
    }
}