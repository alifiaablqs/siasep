<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataAset extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'data_aset';

    protected $fillable = [
        'nama_aset',
        'nomor_aset',
        'nomor_urut',
        'kategori_id',
        'deskripsi',
        'merek',
        'tahun_kapitalisasi',
        'id_director',
        'id_divisi',
        'id_department',
        'id_section',
        'id_unit',
        'lokasi_id',
        'pic_id',
        'penanggung_jawab_id',
        'bast',
        'status_kondisi',
        'status_aset',
        'keterangan',
        'dokumen_penghapusan',
        'needs_org_verification',
        'needs_pic_verification',
        'needs_pj_verification',
    ];

    protected $casts = [
    ];

    public static function getNextNomorUrut(): string
    {
        $max = self::max(\DB::raw('CAST(nomor_urut AS UNSIGNED)')) ?? 0;
        return (string)($max + 1);
    }

    public function generateNomorAset(?string $noUrut = null): string
    {
        $noUrut = $noUrut ?? $this->nomor_urut;

        // Fallback jika kosong
        if (empty($noUrut)) {
            $noUrut = self::getNextNomorUrut();
        }

        // Tahun kapitalisasi
        $tahun = $this->tahun_kapitalisasi ?? date('Y');

        // Kode kategori aset (101, 102, 201, ...)
        $kategori = \App\Models\KategoriAset::find($this->kategori_id);
        $kodeKategori = $kategori ? $kategori->kode : 'XXX';

        // Kode lokasi aset
        $lokAset    = \App\Models\LokasiAset::find($this->lokasi_id);
        $kodeLokasi = $lokAset ? ($lokAset->kode_lokasi ?? 'LOK') : 'LOK';

        // Susun nomor aset
        return "{$kodeKategori}/{$noUrut}/{$kodeLokasi}/{$tahun}";
    }

    /**
     * Generate nomor_aset format baru: [KODE_KLASIFIKASI]/[NOMOR_URUT]/[KODE_LOKASI]/[TAHUN]
     */
    protected static function booted()
    {
        static::creating(function ($aset) {
            if (empty($aset->nomor_urut)) {
                $aset->nomor_urut = self::getNextNomorUrut();
            }
        });

        static::created(function ($aset) {
            $aset->nomor_aset = $aset->generateNomorAset();
            $aset->saveQuietly();
        });

        static::updating(function ($aset) {
            if ($aset->isDirty('lokasi_id') || $aset->isDirty('kategori_id') || $aset->isDirty('tahun_kapitalisasi') || $aset->isDirty('nomor_urut')) {
                $aset->nomor_aset = $aset->generateNomorAset();
            }
        });
    }

    /**
     * Tanggal cek terakhir
     */
    public function getTanggalCekTerakhirAttribute(): ?string
    {
        return $this->logAset()->latest('tanggal_cek')->value('tanggal_cek');
    }

    public function getOrganisasiTerikatAttribute(): string
    {
        if ($this->id_unit && $this->unit) return $this->unit->name_unit . (isset($this->unit->is_active) && !$this->unit->is_active ? ' (Nonaktif)' : '');
        if ($this->id_section && $this->section) return $this->section->name_section . (isset($this->section->is_active) && !$this->section->is_active ? ' (Nonaktif)' : '');
        if ($this->id_department && $this->department) return $this->department->name_department . (isset($this->department->is_active) && !$this->department->is_active ? ' (Nonaktif)' : '');
        if ($this->id_divisi && $this->divisi) return $this->divisi->nm_divisi . (isset($this->divisi->is_active) && !$this->divisi->is_active ? ' (Nonaktif)' : '');
        if ($this->id_director && $this->director) return $this->director->name_director . (isset($this->director->is_active) && !$this->director->is_active ? ' (Nonaktif)' : '');
        return 'Tanpa Organisasi';
    }

    public function hasVerificationIssues(): bool
    {
        $picIssue = $this->needs_pic_verification || ($this->pic_id && (!$this->pic || !$this->pic->is_active));
        $pjIssue = $this->needs_pj_verification || ($this->penanggung_jawab_id && (!$this->penanggungJawab || !$this->penanggungJawab->is_active));
        
        return $this->needs_org_verification || $picIssue || $pjIssue;
    }

    public function getVerificationBadges(): array
    {
        $badges = [];
        if ($this->needs_org_verification) {
            $badges[] = 'Organisasi';
        }
        
        $picIssue = $this->needs_pic_verification || ($this->pic_id && (!$this->pic || !$this->pic->is_active));
        if ($picIssue) {
            $badges[] = 'PIC';
        }
        
        $pjIssue = $this->needs_pj_verification || ($this->penanggung_jawab_id && (!$this->penanggungJawab || !$this->penanggungJawab->is_active));
        if ($pjIssue) {
            $badges[] = 'Penanggung Jawab';
        }
        
        return array_unique($badges);
    }

    public function getResolvedDepartmentNameAttribute(): string
    {
        if ($this->id_unit && $this->unit) {
            if ($this->unit->section && $this->unit->section->department) {
                return $this->unit->section->department->name_department;
            }
            if ($this->unit->department) {
                return $this->unit->department->name_department;
            }
        }
        if ($this->id_section && $this->section && $this->section->department) {
            return $this->section->department->name_department;
        }
        if ($this->id_department && $this->department) {
            return $this->department->name_department;
        }
        if ($this->id_divisi && $this->divisi) {
            return $this->divisi->nm_divisi;
        }
        if ($this->id_director && $this->director) {
            return $this->director->name_director;
        }
        return 'Tanpa Departemen';
    }

    public function getResolvedDivisiNameAttribute(): string
    {
        $dept = null;
        if ($this->id_unit && $this->unit) {
            if ($this->unit->section && $this->unit->section->department) {
                $dept = $this->unit->section->department;
            } else if ($this->unit->department) {
                $dept = $this->unit->department;
            }
        } else if ($this->id_section && $this->section && $this->section->department) {
            $dept = $this->section->department;
        } else if ($this->id_department && $this->department) {
            $dept = $this->department;
        }

        if ($dept && $dept->divisi) {
            return $dept->divisi->nm_divisi;
        }

        if ($this->id_divisi && $this->divisi) {
            return $this->divisi->nm_divisi;
        }

        if ($this->id_director && $this->director) {
            return $this->director->name_director;
        }

        return 'Tanpa Divisi';
    }

    public function getKodeOrganisasiAttribute(): ?string
    {
        if ($this->id_unit) return "unit_" . $this->id_unit;
        if ($this->id_section) return "section_" . $this->id_section;
        if ($this->id_department) return "department_" . $this->id_department;
        if ($this->id_divisi) return "divisi_" . $this->id_divisi;
        if ($this->id_director) return "director_" . $this->id_director;
        return null;
    }

    /**
     * Kategori aset (menggantikan jenis aset umum/khusus dan kategori lama)
     */
    public function kategoriAset()
    {
        return $this->belongsTo(KategoriAset::class, 'kategori_id');
    }

    public function director()
    {
        return $this->belongsTo(Director::class, 'id_director', 'id_director');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'id_divisi', 'id_divisi');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'id_department', 'id_department');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'id_section', 'id_section');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'id_unit', 'id_unit');
    }

    public function lokasi()
    {
        return $this->belongsTo(LokasiAset::class, 'lokasi_id', 'lokasi_id');
    }

    /**
     * Helper: cek jenis kategori
     */
    public function getIsAsetTetapAttribute(): bool
    {
        return $this->kategoriAset && $this->kategoriAset->tipe === 'aset_tetap';
    }

    /**
     * Helper: cek jenis kategori
     */
    public function getIsInventarisAttribute(): bool
    {
        return $this->kategoriAset && $this->kategoriAset->tipe === 'inventaris';
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_id', 'id');
    }

    public function penanggungJawab()
    {
        return $this->belongsTo(User::class, 'penanggung_jawab_id', 'id');
    }

    /**
     * Semua foto aset 
     */
    public function foto()
    {
        return $this->hasMany(AsetFoto::class, 'aset_id')->orderBy('urutan');
    }

    /**
     * Foto pertama aset 
     */
    public function fotoPertama()
    {
        return $this->hasOne(AsetFoto::class, 'aset_id')->orderBy('urutan');
    }

    /**
     * Riwayat log/pengecekan aset
     */
    public function logAset()
    {
        return $this->hasMany(LogAset::class, 'aset_id');
    }

    /**
     * Detail stock opname 
     */
    public function stockOpnameDetail()
    {
        return $this->hasMany(StockOpnameDetail::class, 'aset_id');
    }

    /**
     * Semua pengajuan perbaikan untuk aset ini
     */
    public function pengajuanPerbaikan()
    {
        return $this->hasMany(PengajuanPerbaikan::class, 'aset_id');
    }

    /**
     * Pengajuan perbaikan yang masih aktif (menunggu atau disetujui)
     */
    public function pengajuanPerbaikanAktif()
    {
        return $this->hasMany(PengajuanPerbaikan::class, 'aset_id')
                    ->whereIn('status', ['menunggu', 'disetujui']);
    }

    /**
     * Scope query to limit assets according to user's organizational structure hierarchy.
     */
    public function scopeForUser($query, $user)
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        $allowedDirectorIds = [];
        $allowedDivisiIds = [];
        $allowedDepartmentIds = [];
        $allowedSectionIds = [];
        $allowedUnitIds = [];

        // 1. Ancestors (Upward):
        if ($user->director_id_director) $allowedDirectorIds[] = $user->director_id_director;
        if ($user->divisi_id_divisi) $allowedDivisiIds[] = $user->divisi_id_divisi;
        if ($user->department_id_department) $allowedDepartmentIds[] = $user->department_id_department;
        if ($user->section_id_section) $allowedSectionIds[] = $user->section_id_section;
        if ($user->unit_id_unit) $allowedUnitIds[] = $user->unit_id_unit;

        // 2. Descendants (Downward):
        // Downward scoping should start from the user's most specific assigned organization node,
        // rather than starting from the Director and exposing other sibling branches.
        if ($user->unit_id_unit) {
            // Unit has no descendants.
        } elseif ($user->section_id_section) {
            $unitIds = \App\Models\Unit::where('section_id_section', $user->section_id_section)
                ->pluck('id_unit')->toArray();
            $allowedUnitIds = array_merge($allowedUnitIds, $unitIds);
        } elseif ($user->department_id_department) {
            $sectIds = \App\Models\Section::where('department_id_department', $user->department_id_department)
                ->pluck('id_section')->toArray();
            $allowedSectionIds = array_merge($allowedSectionIds, $sectIds);

            $unitIds = \App\Models\Unit::whereIn('section_id_section', $sectIds)
                ->orWhere('department_id_department', $user->department_id_department)
                ->pluck('id_unit')->toArray();
            $allowedUnitIds = array_merge($allowedUnitIds, $unitIds);
        } elseif ($user->divisi_id_divisi) {
            $deptIds = \App\Models\Department::where('divisi_id_divisi', $user->divisi_id_divisi)
                ->pluck('id_department')->toArray();
            $allowedDepartmentIds = array_merge($allowedDepartmentIds, $deptIds);

            if (!empty($deptIds)) {
                $sectIds = \App\Models\Section::whereIn('department_id_department', $deptIds)
                    ->pluck('id_section')->toArray();
                $allowedSectionIds = array_merge($allowedSectionIds, $sectIds);

                $unitIds = \App\Models\Unit::whereIn('section_id_section', $sectIds)
                    ->orWhereIn('department_id_department', $deptIds)
                    ->pluck('id_unit')->toArray();
                $allowedUnitIds = array_merge($allowedUnitIds, $unitIds);
            }
        } elseif ($user->director_id_director) {
            $divIds = \App\Models\Divisi::where('director_id_director', $user->director_id_director)
                ->pluck('id_divisi')->toArray();
            $allowedDivisiIds = array_merge($allowedDivisiIds, $divIds);

            $deptIds = \App\Models\Department::where('director_id_director', $user->director_id_director)
                ->orWhereIn('divisi_id_divisi', $divIds)
                ->pluck('id_department')->toArray();
            $allowedDepartmentIds = array_merge($allowedDepartmentIds, $deptIds);

            if (!empty($deptIds)) {
                $sectIds = \App\Models\Section::whereIn('department_id_department', $deptIds)
                    ->pluck('id_section')->toArray();
                $allowedSectionIds = array_merge($allowedSectionIds, $sectIds);

                $unitIds = \App\Models\Unit::whereIn('section_id_section', $sectIds)
                    ->orWhereIn('department_id_department', $deptIds)
                    ->pluck('id_unit')->toArray();
                $allowedUnitIds = array_merge($allowedUnitIds, $unitIds);
            }
        }

        $allowedDirectorIds = array_unique($allowedDirectorIds);
        $allowedDivisiIds = array_unique($allowedDivisiIds);
        $allowedDepartmentIds = array_unique($allowedDepartmentIds);
        $allowedSectionIds = array_unique($allowedSectionIds);
        $allowedUnitIds = array_unique($allowedUnitIds);

        return $query->where(function($q) use ($allowedDirectorIds, $allowedDivisiIds, $allowedDepartmentIds, $allowedSectionIds, $allowedUnitIds) {
            $hasCondition = false;

            if (!empty($allowedUnitIds)) {
                $q->orWhereIn('id_unit', $allowedUnitIds);
                $hasCondition = true;
            }
            if (!empty($allowedSectionIds)) {
                $q->orWhereIn('id_section', $allowedSectionIds);
                $hasCondition = true;
            }
            if (!empty($allowedDepartmentIds)) {
                $q->orWhereIn('id_department', $allowedDepartmentIds);
                $hasCondition = true;
            }
            if (!empty($allowedDivisiIds)) {
                $q->orWhereIn('id_divisi', $allowedDivisiIds);
                $hasCondition = true;
            }
            if (!empty($allowedDirectorIds)) {
                $q->orWhereIn('id_director', $allowedDirectorIds);
                $hasCondition = true;
            }

            if (!$hasCondition) {
                $q->whereRaw('1 = 0');
            }
        });
    }

    /**
     * Scope untuk aset yang butuh verifikasi
     */
    public function scopeNeedsVerification($query)
    {
        return $query->where(function($q) {
            $q->where('needs_org_verification', true)
              ->orWhere('needs_pic_verification', true)
              ->orWhere('needs_pj_verification', true)
              ->orWhere(function($subQ) {
                  $subQ->whereNotNull('pic_id')
                       ->whereDoesntHave('pic', function($userQ) {
                           $userQ->where('is_active', true);
                       });
              })
              ->orWhere(function($subQ) {
                  $subQ->whereNotNull('penanggung_jawab_id')
                       ->whereDoesntHave('penanggungJawab', function($userQ) {
                           $userQ->where('is_active', true);
                       });
              });
        });
    }

    /**
     * Scope untuk aset yang sudah terverifikasi / bersih
     */
    public function scopeVerified($query)
    {
        return $query->where('needs_org_verification', false)
                     ->where('needs_pic_verification', false)
                     ->where('needs_pj_verification', false)
                     ->where(function($q) {
                         $q->whereNull('pic_id')
                           ->orWhereHas('pic', function($userQ) {
                               $userQ->where('is_active', true);
                           });
                     })
                     ->where(function($q) {
                         $q->whereNull('penanggung_jawab_id')
                           ->orWhereHas('penanggungJawab', function($userQ) {
                               $userQ->where('is_active', true);
                           });
                     });
    }
}