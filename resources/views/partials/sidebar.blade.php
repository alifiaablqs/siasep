<div class="sidebar-logo">
    <div class="logo-header d-flex align-items-center justify-content-center p-3 pt-4 pb-4" style="padding:14px 16px;">
        <a href="{{ url('dashboard') }}" class="logo" style="display:block; width:100%; text-decoration:none;">
            <div
                style="
                    background:#fff;
                    padding:10px 14px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    overflow:hidden;
                    width:100%;
                ">
                <img src="{{ asset('assets/img/logo-reka.png') }}" alt="Logo"
                    style="display:block; max-width:100%; height:auto; max-height:60px; margin:0;" />
            </div>
        </a>
    </div>
</div>

<div class="sidebar-wrapper">
    <div class="sidebar-content">
        <ul class="nav nav-secondary" style="margin-top: 50px;">

        <li class="nav-section">
            <span class="text-section">Menu</span>
        </li>
        <!-- SUPERADMIN & GENERAL AFFAIRS -->
            @if (Auth::user()->role->nm_role == 'superadmin')
                <li class="nav-item {{ (request()->routeIs('superadmin.dashboard') || request()->routeIs('dashboard')) ? 'active_' : '' }}">
                    <a href="{{ route('superadmin.dashboard') }}" class="nav-link">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
            @elseif (Auth::user()->hasPermission('view_dashboard_ga'))
                <li class="nav-item {{ (request()->routeIs('general-affairs.dashboard') || request()->routeIs('dashboard')) ? 'active_' : '' }}">
                    <a href="{{ route('general-affairs.dashboard') }}" class="nav-link">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
            @endif

            <!-- SUPERADMIN & GENERAL AFFAIRS MENU -->
            @if (Auth::user()->role->nm_role == 'superadmin')



                <li class="nav-item {{ request()->routeIs('lokasi-aset.*') ? 'active_' : '' }}">
                    <a href="{{ route('lokasi-aset.index') }}" class="nav-link">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>Lokasi Aset</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('jenis-kategori.*') ? 'active_' : '' }}">
                    <a href="{{ route('jenis-kategori.index') }}" class="nav-link">
                        <i class="fas fa-layer-group"></i>
                        <p>Jenis Kategori</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('kategori-aset.*') ? 'active_' : '' }}">
                    <a href="{{ route('kategori-aset.index') }}" class="nav-link">
                        <i class="fas fa-boxes"></i>
                        <p>Kategori Aset</p>
                    </a>
                </li>

                <li class="nav-item {{ (request()->routeIs('aset.index') && !request()->boolean('filter_own_dept')) ? 'active_' : '' }}">
                    <a href="{{ route('aset.index') }}" class="nav-link">
                        <i class="fas fa-box"></i>
                        <p>Data Aset Perusahaan</p>
                    </a>
                </li>

                <li class="nav-item {{ (request()->routeIs('aset.index') && request()->boolean('filter_own_dept')) ? 'active_' : '' }}">
                    <a href="{{ route('aset.index', ['filter_own_dept' => 1]) }}" class="nav-link">
                        <i class="fas fa-cubes"></i>
                        <p>Data Aset Divisi</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('aset.pic') ? 'active_' : '' }}">
                    <a href="{{ route('aset.pic') }}" class="nav-link">
                        <i class="fas fa-user-tag"></i>
                        <p>Aset PIC</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('log-aset.index') ? 'active_' : '' }}">
                    <a href="{{ route('log-aset.index') }}" class="nav-link">
                        <i class="fas fa-clipboard-check"></i>
                        <p>Riwayat Monitoring</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('perbaikan.*') ? 'active_' : '' }}">
                    <a href="{{ route('perbaikan.index') }}" class="nav-link">
                        <i class="fas fa-tools"></i>
                        <p>Pengajuan Perbaikan</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('stock-opname.index') || request()->routeIs('stock-opname.show') || request()->routeIs('stock-opname.export') ? 'active_' : '' }}">
                    <a href="{{ route('stock-opname.index') }}" class="nav-link">
                        <i class="fas fa-clipboard-list"></i>
                        <p>Stock Opname</p>
                    </a>
                </li>
                @if(\App\Models\StockOpname::where('status', 'aktif')->exists())
                    <li class="nav-item {{ request()->routeIs('stock-opname.user-*') ? 'active_' : '' }}">
                        <a href="{{ route('stock-opname.user-index') }}" class="nav-link">
                            <i class="fas fa-clipboard-check"></i>
                            <p>Pelaksanaan Opname</p>
                        </a>
                    </li>
                @endif

                <li class="nav-section">
                    <span class="text-section">Lainnya</span>
                </li>

                @php
                    $verificationCount = \App\Models\DataAset::needsVerification()->count();
                @endphp
                <li class="nav-item {{ request()->routeIs('aset.verifikasi') ? 'active_' : '' }}">
                    <a href="{{ route('aset.verifikasi') }}" class="nav-link">
                        <i class="fas fa-clipboard-check text-warning"></i>
                        <p>Verifikasi Aset</p>
                        @if($verificationCount > 0)
                            <span class="badge badge-warning">{{ $verificationCount }}</span>
                        @endif
                    </a>
                </li>

                {{-- Pengaturan --}}
                @php
                    $isPengaturanActive = request()->is('organization-manage*') || request()->is('kode-bagian*') || request()->is('user-manage*') || request()->is('role-management*') || request()->is('permission-manage*');
                @endphp
                <li class="nav-item {{ $isPengaturanActive ? 'active_' : '' }}">
                    <a href="#pengaturanDrop"
                        class="nav-link {{ $isPengaturanActive ? '' : 'collapsed' }}"
                        data-toggle="collapse"
                        aria-expanded="{{ $isPengaturanActive ? 'true' : 'false' }}">
                            <i class="fas fa-cog"></i>
                            <p>Pengaturan</p>
                            <span class="caret"></span>
                    </a>

                    <div class="collapse {{ $isPengaturanActive ? 'show' : '' }}"
                        id="pengaturanDrop">
                        <ul class="nav nav-collapse" style="margin-top: 0; padding-bottom: 10px;">
                            <li class="{{ request()->routeIs('organization.manageOrganization') ? 'active' : '' }}">
                                <a href="{{ route('organization.manageOrganization') }}">
                                    <span class="sub-item">Kelola Struktur</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('kode-bagian.index') || request()->routeIs('kode-bagian.edit') || request()->routeIs('kode-bagian.create') ? 'active' : '' }}">
                                <a href="{{ route('kode-bagian.index') }}">
                                    <span class="sub-item">Manajemen Kode Bagian Kerja</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('user.manage') || request()->routeIs('user.create') || request()->routeIs('user-manage.edit') || request()->routeIs('user.show') ? 'active' : '' }}">
                                <a href="{{ route('user.manage') }}">
                                    <span class="sub-item">Manajemen Pengguna</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('permissions.manage') ? 'active' : '' }}">
                                <a href="{{ route('permissions.manage') }}">
                                    <span class="sub-item">Manajemen Hak Akses</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- Pemulihan di bawah Lainnya --}}
                <li class="nav-item {{ request()->is('pemulihan*') ? 'active_' : '' }}">
                    <a href="#pemulihanDrop"
                        class="nav-link {{ request()->is('pemulihan*') ? '' : 'collapsed' }}"
                        data-toggle="collapse"
                        aria-expanded="{{ request()->is('pemulihan*') ? 'true' : 'false' }}">
                            <i class="fas fa-trash-restore"></i>
                            <p>Pemulihan</p>
                            <span class="caret"></span>
                    </a>

                    <div class="collapse {{ request()->is('pemulihan*') ? 'show' : '' }}"
                        id="pemulihanDrop">
                        <ul class="nav nav-collapse" style="margin-top: 0; padding-bottom: 10px;">
                            <li class="{{ request()->routeIs('pemulihan.lokasi-aset') ? 'active' : '' }}">
                                <a href="{{ route('pemulihan.lokasi-aset') }}">
                                    <span class="sub-item">Lokasi Aset</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('pemulihan.jenis-kategori') ? 'active' : '' }}">
                                <a href="{{ route('pemulihan.jenis-kategori') }}">
                                    <span class="sub-item">Jenis Kategori</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('pemulihan.kategori-aset') ? 'active' : '' }}">
                                <a href="{{ route('pemulihan.kategori-aset') }}">
                                    <span class="sub-item">Kategori Aset</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('pemulihan.data-aset') ? 'active' : '' }}">
                                <a href="{{ route('pemulihan.data-aset') }}">
                                    <span class="sub-item">Data Aset Perusahaan</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

            @elseif (Auth::user()->hasPermission('view_dashboard_ga'))
                {{-- General Affairs tetap seperti semula untuk GA users --}}
                <li class="nav-item {{ request()->routeIs('lokasi-aset.*') ? 'active_' : '' }}">
                    <a href="{{ route('lokasi-aset.index') }}" class="nav-link">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>Lokasi Aset</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('jenis-kategori.*') ? 'active_' : '' }}">
                    <a href="{{ route('jenis-kategori.index') }}" class="nav-link">
                        <i class="fas fa-layer-group"></i>
                        <p>Jenis Kategori</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('kategori-aset.*') ? 'active_' : '' }}">
                    <a href="{{ route('kategori-aset.index') }}" class="nav-link">
                        <i class="fas fa-boxes"></i>
                        <p>Kategori Aset</p>
                    </a>
                </li>

                <li class="nav-item {{ (request()->routeIs('aset.index') && !request()->boolean('filter_own_dept')) ? 'active_' : '' }}">
                    <a href="{{ route('aset.index') }}" class="nav-link">
                        <i class="fas fa-box"></i>
                        <p>Data Aset Perusahaan</p>
                    </a>
                </li>

                <li class="nav-item {{ (request()->routeIs('aset.index') && request()->boolean('filter_own_dept')) ? 'active_' : '' }}">
                    <a href="{{ route('aset.index', ['filter_own_dept' => 1]) }}" class="nav-link">
                        <i class="fas fa-cubes"></i>
                        <p>Data Aset Divisi</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('aset.pic') ? 'active_' : '' }}">
                    <a href="{{ route('aset.pic') }}" class="nav-link">
                        <i class="fas fa-user-tag"></i>
                        <p>Aset PIC</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('log-aset.index') ? 'active_' : '' }}">
                    <a href="{{ route('log-aset.index') }}" class="nav-link">
                        <i class="fas fa-clipboard-check"></i>
                        <p>Riwayat Monitoring</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('perbaikan.*') ? 'active_' : '' }}">
                    <a href="{{ route('perbaikan.index') }}" class="nav-link">
                        <i class="fas fa-tools"></i>
                        <p>Pengajuan Perbaikan</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('stock-opname.index') || request()->routeIs('stock-opname.show') ? 'active_' : '' }}">
                    <a href="{{ route('stock-opname.index') }}" class="nav-link">
                        <i class="fas fa-boxes"></i>
                        <p>Stock Opname</p>
                    </a>
                </li>
                @if(\App\Models\StockOpname::where('status', 'aktif')->exists())
                    <li class="nav-item {{ request()->routeIs('stock-opname.user-*') ? 'active_' : '' }}">
                        <a href="{{ route('stock-opname.user-index') }}" class="nav-link">
                            <i class="fas fa-clipboard-check"></i>
                            <p>Pelaksanaan Opname</p>
                        </a>
                    </li>
                @endif

                <li class="nav-section">
                    <span class="text-section">Lainnya</span>
                </li>

                @php
                    $verificationCountGA = \App\Models\DataAset::needsVerification()->count();
                @endphp
                <li class="nav-item {{ request()->routeIs('aset.verifikasi') ? 'active_' : '' }}">
                    <a href="{{ route('aset.verifikasi') }}" class="nav-link">
                        <i class="fas fa-clipboard-check text-warning"></i>
                        <p>Verifikasi Aset</p>
                        @if($verificationCountGA > 0)
                            <span class="badge badge-warning">{{ $verificationCountGA }}</span>
                        @endif
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('organization.manageOrganization') ? 'active_' : '' }}">
                    <a href="{{ route('organization.manageOrganization') }}" class="nav-link">
                        <i class="fas fa-sitemap"></i>
                        <p>Struktur Organisasi</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->is('pemulihan*') ? 'active_' : '' }}">
                    <a href="#pemulihanDrop"
                        class="nav-link {{ request()->is('pemulihan*') ? '' : 'collapsed' }}"
                        data-toggle="collapse"
                        aria-expanded="{{ request()->is('pemulihan*') ? 'true' : 'false' }}">
                            <i class="fas fa-trash-restore"></i>
                            <p>Pemulihan</p>
                            <span class="caret"></span>
                    </a>

                    <div class="collapse {{ request()->is('pemulihan*') ? 'show' : '' }}"
                        id="pemulihanDrop">
                        <ul class="nav nav-collapse" style="margin-top: 0; padding-bottom: 10px;">
                            <li class="{{ request()->routeIs('pemulihan.lokasi-aset') ? 'active' : '' }}">
                                <a href="{{ route('pemulihan.lokasi-aset') }}">
                                    <span class="sub-item">Lokasi Aset</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('pemulihan.jenis-kategori') ? 'active' : '' }}">
                                <a href="{{ route('pemulihan.jenis-kategori') }}">
                                    <span class="sub-item">Jenis Kategori</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('pemulihan.kategori-aset') ? 'active' : '' }}">
                                <a href="{{ route('pemulihan.kategori-aset') }}">
                                    <span class="sub-item">Kategori Aset</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('pemulihan.data-aset') ? 'active' : '' }}">
                                <a href="{{ route('pemulihan.data-aset') }}">
                                    <span class="sub-item">Data Aset</span>
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                </li>
            @endif



            <!-- MENU STAFF & MANAGER (NON-GA) -->
            @if (Auth::user()->role->nm_role != 'superadmin' && !Auth::user()->isBagianUmum())

                <li class="nav-item {{ (request()->routeIs('manager.dashboard') || request()->routeIs('staff.dashboard') || request()->routeIs('admin.dashboard') || request()->routeIs('dashboard')) ? 'active_' : '' }}">
                    <a href="{{ Auth::user()->role_id_role == 3 ? route('manager.dashboard') : route('staff.dashboard') }}" class="nav-link">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('aset.index') ? 'active_' : '' }}">
                    <a href="{{ route('aset.index') }}" class="nav-link">
                        <i class="fas fa-box"></i>
                        <p>Data Aset Divisi</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('aset.pic') ? 'active_' : '' }}">
                    <a href="{{ route('aset.pic') }}" class="nav-link">
                        <i class="fas fa-user-tag"></i>
                        <p>Aset PIC</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('log-aset.index') ? 'active_' : '' }}">
                    <a href="{{ route('log-aset.index') }}" class="nav-link">
                        <i class="fas fa-clipboard-check"></i>
                        <p>Monitoring Aset</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('perbaikan.*') ? 'active_' : '' }}">
                    <a href="{{ route('perbaikan.index') }}" class="nav-link">
                        <i class="fas fa-tools"></i>
                        <p>Pengajuan Perbaikan</p>
                    </a>
                </li>

            @endif
        </ul>
    </div>
</div>
