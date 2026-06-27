<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['firstname', 'lastname', 'nip', 'email', 'password', 'phone_number', 'kode_bagian', 'role_id_role', 'position_id_position', 'director_id_director', 'divisi_id_divisi', 'department_id_department', 'section_id_section', 'unit_id_unit', 'profile_image', 'is_active'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */


    public function bagianKerja()
    {
        return $this->belongsTo(BagianKerja::class, 'kode_bagian', 'kode_bagian');
    }
    
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'phone_number' => 'string',
        ];
    }
    public function getFullnameAttribute()
    {
        return trim($this->firstname . ' ' . $this->lastname);
    }
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id_role', 'id_role');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id_position', 'id_position');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id_divisi', 'id_divisi');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id_department', 'id_department');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id_section', 'id_section');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id_unit', 'id_unit');
    }
    public function director()
    {
        return $this->belongsTo(Director::class, 'director_id_director', 'id_director');
    }

    /**
     * Memeriksa apakah user memiliki hak akses tertentu.
     */
    public function hasPermission(string $permissionName): bool
    {
        // 1. Superadmin (Role ID 1) otomatis diizinkan mengakses semua hal
        if ($this->role_id_role === 1) {
            return true;
        }

        // 2. Cek permission dari Role yang ditautkan ke User
        if ($this->role && $this->role->permissions()->where('name', $permissionName)->exists()) {
            return true;
        }

        // 3. Cek permission dari Department (Departemen) yang ditautkan ke User
        if ($this->department && $this->department->permissions()->where('name', $permissionName)->exists()) {
            return true;
        }

        // 4. Cek permission dari Section (Kustomisasi Seksi) yang ditautkan ke User
        if ($this->section && $this->section->permissions()->where('name', $permissionName)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Mengecek kode_bagian
     */
    public function isBagianUmum(): bool
    {
        if ($this->kode_bagian) {
            $kodes = array_filter(array_map('trim', explode(';', $this->kode_bagian)));
            if (!empty($kodes)) {
                $bagianUmumKodes = \App\Models\BagianKerja::where('nama_bagian', 'like', '%Umum%')
                    ->orWhere('nama_bagian', 'like', '%General Affairs%')
                    ->orWhere('nama_bagian', 'like', '%GA%')
                    ->pluck('kode_bagian')
                    ->toArray();
                if (!empty(array_intersect($kodes, $bagianUmumKodes))) {
                    return true;
                }
            }
        }

        // --- Cek hak akses GA secara dinamis berdasarkan Department ---
        if ($this->department && $this->department->permissions()->whereIn('name', ['manage_stock_opname', 'manage_assets'])->exists()) {
            return true;
        }

        // --- Cek hak akses GA secara dinamis berdasarkan Section ---
        if ($this->section && $this->section->permissions()->whereIn('name', ['manage_stock_opname', 'manage_assets'])->exists()) {
            return true;
        }

        // --- Cek posisi dalam struktur organisasi ---
        if ($this->department && (str_contains(strtolower($this->department->name_department ?? ''), 'umum') || str_contains(strtolower($this->department->name_department ?? ''), 'general affairs') || str_contains(strtolower($this->department->name_department ?? ''), 'ga'))) {
            return true;
        }
        if ($this->section && (str_contains(strtolower($this->section->name_section ?? ''), 'umum') || str_contains(strtolower($this->section->name_section ?? ''), 'general affairs') || str_contains(strtolower($this->section->name_section ?? ''), 'ga'))) {
            return true;
        }
        if ($this->unit && (str_contains(strtolower($this->unit->name_unit ?? ''), 'umum') || str_contains(strtolower($this->unit->name_unit ?? ''), 'general affairs') || str_contains(strtolower($this->unit->name_unit ?? ''), 'ga'))) {
            return true;
        }

        return false;
    }

    /**
     * Mengecek apakah user merupakan General Affairs untuk modul stock opname.
     *
     * Pure delegation ke {@see self::isBagianUmum()} tanpa bypass berdasarkan
     * `role_id_role === 1`. Konsekuensinya, superadmin yang tidak lolos
     * `isBagianUmum()` TIDAK diperlakukan sebagai General Affairs pada modul ini.
     *
     * Requirements: 1.5, 5.2
     */
    public function isGeneralAffairs(): bool
    {
        return $this->isBagianUmum();
    }

    /** Semua pengajuan perbaikan yang diajukan user */
    public function pengajuanPerbaikan()
    {
        return $this->hasMany(PengajuanPerbaikan::class, 'diajukan_oleh');
    }
}
