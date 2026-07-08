<?php

namespace App\Services;

use App\Models\User;
use App\Models\Director;
use App\Models\Divisi;
use App\Models\Department;
use App\Models\Section;
use App\Models\Unit;
use App\Models\Role;
use App\Models\Position;
use App\Models\BagianKerja;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class OrgSyncService
{
    protected string $baseUrl;
    protected ?string $login;
    protected ?string $password;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.external_api.base_url'), '/');
        $this->login = config('services.external_api.login');
        $this->password = config('services.external_api.password');
    }

    /**
     * Jalankan proses sinkronisasi penuh.
     */
    public function sync(): array
    {
        Log::info('Memulai sinkronisasi data organisasi dan user dari API eksternal.');

        if (empty($this->login) || empty($this->password)) {
            throw new \Exception('Kredensial API eksternal (NIP/Password) belum diset di file .env.');
        }

        // 1. Login untuk mendapatkan token
        $token = $this->getAccessToken();
        Log::info('Berhasil login ke API eksternal, mengambil data user...');

        // 2. Tarik data user
        $usersData = $this->fetchUsers($token);
        $totalUsers = count($usersData);
        Log::info("Berhasil mengunduh {$totalUsers} data user dari API. Memulai penyimpanan ke database.");

        $stats = [
            'directors' => 0,
            'divisis' => 0,
            'departments' => 0,
            'sections' => 0,
            'units' => 0,
            'roles' => 0,
            'positions' => 0,
            'bagian_kerja' => 0,
            'users' => 0,
            'errors' => 0,
        ];

        $activeUserNips = [];

        // Unguard model agar bebas melakukan insert/update kolom apa saja
        Model::unguard();

        // Gunakan DB transaction agar integritas data terjamin
        DB::transaction(function () use ($usersData, &$stats) {
            foreach ($usersData as $u) {
                try {
                    // a. Director
                    $directorId = null;
                    if (!empty($u['director'])) {
                        $d = $u['director'];
                        Director::updateOrCreate(
                            ['id_director' => $d['id_director']],
                            ['name_director' => $d['name_director']]
                        );
                        $directorId = $d['id_director'];
                        $stats['directors']++;
                    } elseif (!empty($u['director_id_director'])) {
                        $directorId = $u['director_id_director'];
                        Director::firstOrCreate(
                            ['id_director' => $directorId],
                            ['name_director' => "Director {$directorId} (Placeholder)"]
                        );
                    }

                    // b. Divisi
                    $divisiId = null;
                    if (!empty($u['divisi'])) {
                        $div = $u['divisi'];
                        Divisi::updateOrCreate(
                            ['id_divisi' => $div['id_divisi']],
                            [
                                'director_id_director' => $div['director_id_director'] ?? $directorId,
                                'nm_divisi' => $div['nm_divisi'] ?? null,
                                'kode_divisi' => $div['kode_divisi'] ?? null,
                            ]
                        );
                        $divisiId = $div['id_divisi'];
                        $stats['divisis']++;
                    } elseif (!empty($u['divisi_id_divisi'])) {
                        $divisiId = $u['divisi_id_divisi'];
                        Divisi::firstOrCreate(
                            ['id_divisi' => $divisiId],
                            [
                                'nm_divisi' => "Divisi {$divisiId} (Placeholder)",
                                'director_id_director' => $directorId,
                            ]
                        );
                    }

                    // c. Department
                    $deptId = null;
                    if (!empty($u['department'])) {
                        $dept = $u['department'];
                        Department::updateOrCreate(
                            ['id_department' => $dept['id_department']],
                            [
                                'name_department' => $dept['name_department'],
                                'kode_department' => $dept['kode_department'] ?? null,
                                'divisi_id_divisi' => $dept['divisi_id_divisi'] ?? $divisiId,
                                'director_id_director' => $dept['director_id_director'] ?? $directorId,
                            ]
                        );
                        $deptId = $dept['id_department'];
                        $stats['departments']++;
                    } elseif (!empty($u['department_id_department'])) {
                        $deptId = $u['department_id_department'];
                        Department::firstOrCreate(
                            ['id_department' => $deptId],
                            [
                                'name_department' => "Department {$deptId} (Placeholder)",
                                'divisi_id_divisi' => $divisiId,
                                'director_id_director' => $directorId,
                            ]
                        );
                    }

                    // d. Section
                    $sectionId = null;
                    if (!empty($u['section'])) {
                        $sec = $u['section'];
                        Section::updateOrCreate(
                            ['id_section' => $sec['id_section']],
                            [
                                'name_section' => $sec['name_section'],
                                'department_id_department' => $sec['department_id_department'] ?? $deptId,
                            ]
                        );
                        $sectionId = $sec['id_section'];
                        $stats['sections']++;
                    } elseif (!empty($u['section_id_section'])) {
                        $sectionId = $u['section_id_section'];
                        Section::firstOrCreate(
                            ['id_section' => $sectionId],
                            [
                                'name_section' => "Section {$sectionId} (Placeholder)",
                                'department_id_department' => $deptId,
                            ]
                        );
                    }

                    // e. Unit
                    $unitId = null;
                    if (!empty($u['unit'])) {
                        $unit = $u['unit'];
                        Unit::updateOrCreate(
                            ['id_unit' => $unit['id_unit']],
                            [
                                'name_unit' => $unit['name_unit'],
                                'department_id_department' => $unit['department_id_department'] ?? $deptId,
                                'section_id_section' => $unit['section_id_section'] ?? $sectionId,
                            ]
                        );
                        $unitId = $unit['id_unit'];
                        $stats['units']++;
                    } elseif (!empty($u['unit_id_unit'])) {
                        $unitId = $u['unit_id_unit'];
                        Unit::firstOrCreate(
                            ['id_unit' => $unitId],
                            [
                                'name_unit' => "Unit {$unitId} (Placeholder)",
                                'department_id_department' => $deptId,
                                'section_id_section' => $sectionId,
                            ]
                        );
                    }

                    // f. Role
                    $roleId = null;
                    if (!empty($u['role'])) {
                        $role = $u['role'];
                        if (is_array($role) && isset($role['id_role'])) {
                            Role::updateOrCreate(
                                ['id_role' => $role['id_role']],
                                ['nm_role' => $role['nm_role'] ?? '']
                            );
                            $roleId = $role['id_role'];
                        } else if (is_numeric($role)) {
                            $roleId = (int)$role;
                        }
                    }
                    if (!$roleId) {
                        $roleId = $u['role_id_role'] ?? 3; // default staff
                    }
                    Role::firstOrCreate(
                        ['id_role' => $roleId],
                        ['nm_role' => "Role {$roleId} (Placeholder)"]
                    );

                    // g. Position
                    $positionId = null;
                    if (!empty($u['position'])) {
                        $pos = $u['position'];
                        if (is_array($pos) && isset($pos['id_position'])) {
                            Position::updateOrCreate(
                                ['id_position' => $pos['id_position']],
                                ['nm_position' => $pos['nm_position'] ?? '']
                            );
                            $positionId = $pos['id_position'];
                        } else if (is_numeric($pos)) {
                            $positionId = (int)$pos;
                        }
                    }
                    if (!$positionId) {
                        $positionId = $u['position_id_position'] ?? 9; // default staff
                    }
                    Position::firstOrCreate(
                        ['id_position' => $positionId],
                        ['nm_position' => "Position {$positionId} (Placeholder)"]
                    );


                    // h. Bagian Kerja
                    $kodeBagian = $u['kode_bagian'] ?? null;
                    if (!empty($u['bagian_kerja'])) {
                        $bk = $u['bagian_kerja'];
                        BagianKerja::updateOrCreate(
                            ['kode_bagian' => $bk['kode_bagian']],
                            [
                                'nama_bagian' => $bk['nama_bagian'] ?? '',
                                'kategori' => $bk['kategori'] ?? null,
                                'is_active' => $bk['is_active'] ?? true,
                            ]
                        );
                        $kodeBagian = $bk['kode_bagian'];
                        $stats['bagian_kerja']++;
                    }

                    // i. User
                    // Parsing nama depan & belakang jika hanya ada fullname
                    $fullname = $u['fullname'] ?? ($u['name'] ?? '');
                    $firstname = $u['firstname'] ?? '';
                    $lastname = $u['lastname'] ?? '';
                    if (empty($firstname) && !empty($fullname)) {
                        $parts = explode(' ', $fullname, 2);
                        $firstname = $parts[0];
                        $lastname = $parts[1] ?? '';
                    }

                    if (empty($firstname)) {
                        $firstname = '-';
                    }

                    // Cari user berdasarkan NIP atau Email
                    $existingUser = User::where('nip', $u['nip'])
                        ->orWhere('email', $u['email'])
                        ->first();

                    $activeUserNips[] = $u['nip'];

                    $userDataToSave = [
                        'firstname' => $firstname,
                        'lastname' => $lastname,
                        'nip' => $u['nip'],
                        'email' => $u['email'],
                        'phone_number' => $u['phone_number'] ?? '-',
                        'role_id_role' => $roleId,
                        'position_id_position' => $positionId,
                        'director_id_director' => $directorId ?? ($u['director_id_director'] ?? null),
                        'divisi_id_divisi' => $divisiId ?? ($u['divisi_id_divisi'] ?? null),
                        'department_id_department' => $deptId ?? ($u['department_id_department'] ?? null),
                        'section_id_section' => $sectionId ?? ($u['section_id_section'] ?? null),
                        'unit_id_unit' => $unitId ?? ($u['unit_id_unit'] ?? null),
                        'kode_bagian' => $kodeBagian,
                        'profile_image' => $u['profile_image'] ?? null,
                    ];

                    if ($existingUser) {
                        $existingUser->update($userDataToSave);
                    } else {
                        $userDataToSave['password'] = Hash::make(Str::random(16));
                        User::create($userDataToSave);
                    }

                    $stats['users']++;

                } catch (\Exception $e) {
                    Log::error("Gagal menyinkronkan user NIP: " . ($u['nip'] ?? 'unknown') . ". Error: " . $e->getMessage());
                    $stats['errors']++;
                }
            }

            // 3. Deactivate users missing from SIPO
            if (!empty($activeUserNips)) {
                $deletedUsers = User::whereNotIn('nip', $activeUserNips)->where('is_active', true)->get();
                foreach ($deletedUsers as $user) {
                    $user->update(['is_active' => false]);

                    $affectedPic = \App\Models\DataAset::where('pic_id', $user->id)->update(['needs_pic_verification' => true]);
                    $affectedPj = \App\Models\DataAset::where('penanggung_jawab_id', $user->id)->update(['needs_pj_verification' => true]);

                    if ($affectedPic > 0 || $affectedPj > 0) {
                        Log::warning("User NIP {$user->nip} tidak ada di SIPO. Diset menjadi nonaktif. {$affectedPic} aset perlu verifikasi PIC, {$affectedPj} aset perlu verifikasi PJ.");
                    }
                }
            }

        });

        Model::reguard();

        Log::info('Sinkronisasi data API eksternal selesai.', $stats);

        $this->notifyVerificationNeeded();

        return $stats;
    }

    /**
     * Dapatkan token akses melalui login POST.
     */
    protected function getAccessToken(): string
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->post("{$this->baseUrl}/login", [
            'login' => $this->login,
            'password' => $this->password,
        ]);

        if ($response->failed()) {
            throw new \Exception("Gagal login ke API eksternal: HTTP Status {$response->status()} | Respon: {$response->body()}");
        }

        $data = $response->json();
        
        if (empty($data['token'])) {
            throw new \Exception("Login sukses tetapi token tidak ditemukan dalam respon: " . json_encode($data));
        }

        return $data['token'];
    }

    /**
     * Tarik daftar user dari API.
     */
    protected function fetchUsers(string $token): array
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$token}",
        ])->get("{$this->baseUrl}/users");

        if ($response->failed()) {
            throw new \Exception("Gagal mengambil data user dari API: HTTP Status {$response->status()} | Respon: {$response->body()}");
        }

        $data = $response->json();

        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }

        if (is_array($data)) {
            return $data;
        }

        throw new \Exception("Format respon user tidak valid (bukan array): " . json_encode($data));
    }

    /**
     * Jalankan proses sinkronisasi struktur organisasi dan bagian kerja.
     */
    public function syncStructureAndBagianKerja(): array
    {
        Log::info('Memulai sinkronisasi data struktur organisasi dan bagian kerja dari API eksternal.');

        if (empty($this->login) || empty($this->password)) {
            throw new \Exception('Kredensial API eksternal (NIP/Password) belum diset di file .env.');
        }

        // 1. Login untuk mendapatkan token
        $token = $this->getAccessToken();
        Log::info('Berhasil login ke API eksternal, memulai pengambilan data...');

        $stats = [
            'directors' => 0,
            'divisis' => 0,
            'departments' => 0,
            'sections' => 0,
            'units' => 0,
            'roles' => 0,
            'positions' => 0,
            'bagian_kerja' => 0,
            'users' => 0,
            'errors' => 0,
        ];

        $activeIds = [
            'directors' => [],
            'divisis' => [],
            'departments' => [],
            'sections' => [],
            'units' => [],
        ];

        Model::unguard();

        DB::transaction(function () use ($token, &$stats, &$activeIds) {
            // a. Sinkronisasi Struktur Organisasi
            try {
                $orgData = $this->fetchOrgStructure($token);
                Log::info('Org structure API response: ' . json_encode($orgData));
                if (is_array($orgData)) {
                    if (isset($orgData['type'])) {
                        // Single node
                        Log::info('Processing single org node: ' . ($orgData['name'] ?? 'no-name'));
                        $this->processOrgNode($orgData, null, null, null, null, $stats, $activeIds);
                    } else {
                        // Array of nodes
                        Log::info('Processing multiple org nodes. Count: ' . count($orgData));
                        foreach ($orgData as $node) {
                            if (is_array($node)) {
                                $this->processOrgNode($node, null, null, null, null, $stats, $activeIds);
                            }
                        }
                    }
                } else {
                    Log::warning('Org structure API response is not an array.');
                }
            } catch (\Exception $e) {
                Log::error("Gagal menyinkronkan struktur organisasi. Error: " . $e->getMessage());
                throw $e;
            }

            // b. Sinkronisasi Bagian Kerja
            try {
                $bagianKerjaData = $this->fetchBagianKerja($token);
                if (is_array($bagianKerjaData)) {
                    foreach ($bagianKerjaData as $item) {
                        BagianKerja::updateOrCreate(
                            ['kode_bagian' => $item['kode_bagian']],
                            [
                                'nama_bagian' => $item['nama_bagian'] ?? '',
                                'kategori' => $item['kategori'] ?? null,
                                'is_active' => $item['is_active'] ?? true,
                            ]
                        );
                        $stats['bagian_kerja']++;
                    }
                }
            } catch (\Exception $e) {
                Log::error("Gagal menyinkronkan bagian kerja. Error: " . $e->getMessage());
                throw $e;
            }

            // c. Soft deactivation for missing orgs
            $this->reconcileDeletedNodes($activeIds);

           

        });

        Model::reguard();

        Log::info('Sinkronisasi struktur organisasi dan bagian kerja selesai.', $stats);

        $this->notifyVerificationNeeded();

        return $stats;
    }

    /**
     * Nonaktifkan node organisasi yang tidak ada di SIPO dan flag aset terkait.
     */
    protected function reconcileDeletedNodes(array $activeIds): void
    {
        $entities = [
            'directors' => ['model' => Director::class, 'id_field' => 'id_director', 'fk' => 'id_director', 'name_field' => 'name_director'],
            'divisis' => ['model' => Divisi::class, 'id_field' => 'id_divisi', 'fk' => 'id_divisi', 'name_field' => 'nm_divisi'],
            'departments' => ['model' => Department::class, 'id_field' => 'id_department', 'fk' => 'id_department', 'name_field' => 'name_department'],
            'sections' => ['model' => Section::class, 'id_field' => 'id_section', 'fk' => 'id_section', 'name_field' => 'name_section'],
            'units' => ['model' => Unit::class, 'id_field' => 'id_unit', 'fk' => 'id_unit', 'name_field' => 'name_unit'],
        ];

        foreach ($entities as $key => $config) {
            if (!empty($activeIds[$key])) {
                $deletedNodes = $config['model']::whereNotIn($config['id_field'], $activeIds[$key])
                    ->where('is_active', true)
                    ->get();

                foreach ($deletedNodes as $node) {
                    $node->update(['is_active' => false]);
                    $affected = \App\Models\DataAset::where($config['fk'], $node->{$config['id_field']})
                        ->update(['needs_org_verification' => true]);

                    if ($affected > 0) {
                        Log::warning("Organisasi {$key} '{$node->{$config['name_field']}}' (ID: {$node->{$config['id_field']}}) tidak ada di SIPO. Diset nonaktif. {$affected} aset perlu verifikasi.");
                    }
                }
            }
        }
    }

    /**
     * Traversal rekursif untuk memproses node struktur organisasi.
     */
    protected function processOrgNode(array $node, ?int $parentDirectorId, ?int $parentDivisiId, ?int $parentDeptId, ?int $parentSectionId, array &$stats, array &$activeIds = [])
    {
        $type = $node['type'] ?? null;
        $id = $node['id'] ?? null;
        $name = $node['name'] ?? null;
        $kode = $node['kode'] ?? null;

        $directorId = $parentDirectorId;
        $divisiId = $parentDivisiId;
        $deptId = $parentDeptId;
        $sectionId = $parentSectionId;

        if ($type === 'director') {
            Director::updateOrCreate(
                ['id_director' => $id],
                [
                    'name_director' => $name,
                    'kode_director' => $kode ?? '',
                    'parent_director_id' => $parentDirectorId,
                    'is_main' => ($parentDirectorId === null) ? 1 : 0
                ]
            );
            $directorId = $id;
            $stats['directors']++;
            $activeIds['directors'][] = $id;
        } elseif ($type === 'divisi') {
            Divisi::updateOrCreate(
                ['id_divisi' => $id],
                [
                    'nm_divisi' => $name,
                    'kode_divisi' => $kode,
                    'director_id_director' => $parentDirectorId
                ]
            );
            $divisiId = $id;
            $stats['divisis']++;
            $activeIds['divisis'][] = $id;
        } elseif ($type === 'department') {
            Department::updateOrCreate(
                ['id_department' => $id],
                [
                    'name_department' => $name,
                    'kode_department' => $kode,
                    'divisi_id_divisi' => $parentDivisiId,
                    'director_id_director' => $parentDirectorId
                ]
            );
            $deptId = $id;
            $stats['departments']++;
            $activeIds['departments'][] = $id;
        } elseif ($type === 'section') {
            Section::updateOrCreate(
                ['id_section' => $id],
                [
                    'name_section' => $name,
                    'department_id_department' => $parentDeptId
                ]
            );
            $sectionId = $id;
            $stats['sections']++;
            $activeIds['sections'][] = $id;
        } elseif ($type === 'unit') {
            Unit::updateOrCreate(
                ['id_unit' => $id],
                [
                    'name_unit' => $name,
                    'department_id_department' => $parentDeptId,
                    'section_id_section' => $parentSectionId
                ]
            );
            $stats['units']++;
            $activeIds['units'][] = $id;
        }

        if (isset($node['children']) && is_array($node['children'])) {
            foreach ($node['children'] as $child) {
                $this->processOrgNode($child, $directorId, $divisiId, $deptId, $sectionId, $stats, $activeIds);
            }
        }
    }

    /**
     * Tarik struktur organisasi dari API.
     */
    protected function fetchOrgStructure(string $token): array
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$token}",
        ])->get("{$this->baseUrl}/struktur-organisasi");

        if ($response->failed()) {
            throw new \Exception("Gagal mengambil data struktur organisasi dari API: HTTP Status {$response->status()} | Respon: {$response->body()}");
        }

        $data = $response->json();

        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }

        if (is_array($data)) {
            return $data;
        }

        throw new \Exception("Format respon struktur organisasi tidak valid (bukan array): " . json_encode($data));
    }

    /**
     * Tarik bagian kerja dari API.
     */
    protected function fetchBagianKerja(string $token): array
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$token}",
        ])->get("{$this->baseUrl}/bagian-kerja");

        if ($response->failed()) {
            throw new \Exception("Gagal mengambil data bagian kerja dari API: HTTP Status {$response->status()} | Respon: {$response->body()}");
        }

        $data = $response->json();

        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }

        if (is_array($data)) {
            return $data;
        }

        throw new \Exception("Format respon bagian kerja tidak valid (bukan array): " . json_encode($data));
    }

    /**
     * Cek apakah ada aset yang perlu verifikasi dan kirim notifikasi ke superadmin
     */
    protected function notifyVerificationNeeded(): void
    {
        try {
            $assetsNeedVerification = \App\Models\DataAset::needsVerification()->count();
            if ($assetsNeedVerification > 0) {
                $superadmins = User::where('role_id_role', 1)->get();
                foreach ($superadmins as $admin) {
                    $admin->notify(new \App\Notifications\SystemNotification(
                        'Verifikasi Aset (Sinkronisasi SIPO)',
                        "Terdapat {$assetsNeedVerification} data aset yang memerlukan verifikasi karena perubahan organisasi atau PIC dari SIPO.",
                        route('aset.verifikasi'),
                        'warning'
                    ));
                }
                Log::info("Notifikasi verifikasi aset telah dikirim ke superadmin.");
            }
        } catch (\Exception $e) {
            Log::error("Gagal mengirim notifikasi verifikasi aset: " . $e->getMessage());
        }
    }
}
