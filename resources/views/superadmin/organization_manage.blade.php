@extends('layouts.app')

@section('title', 'Manajemen Struktur Organisasi')
@section('content')

<head>
    <meta charset="UTF-8">
    <title>Struktur Organisasi</title>
    <link rel="stylesheet" href="https://fperucic.github.io/treant-js/Treant.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/treant-js/1.0/Treant.css">
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="{{ asset('assets/css/organization-structure.css') }}">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/treant-js/1.0/Treant.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>
 </head>

<div class="container-fluid px-1 py-0 mt-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Manajemen Struktur Organisasi</h3>
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
                <span class="text-muted" style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Struktur Organisasi</span>
            </li>
        </ul>
    </div>
        <div class="card shadow-sm border-0">
            <div class="card-body">

                <form method="GET" action="{{ route('organization.manageOrganization') }}"
                    class="search-filter d-flex gap-2">
                </form>



                <!-- Wrapper untuk elemen di luar card -->
                <div class="col-12 col-md-auto">
                    {{-- Button Zoom --}}
                    <div class="treant-zoom-controls">
                        <button class="btn btn-light" style="box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);font-size:14px;font-weight:bold" onclick="zoomTreant(1.1)">+</button>
                        <button class="btn btn-light" style="box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);font-size:14px;font-weight:bold" onclick="zoomTreant(0.9)">−</button>
                        <button class="btn btn-light" style="box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);font-size:14px;font-weight:bold" onclick="resetZoom()">Reset</button>
                        
                        <form method="POST" action="{{ route('organization.sync-sipo') }}" id="syncForm" class="d-inline m-0">
                            @csrf
                            <button type="submit" class="btn text-white rounded-3 fw-bold" onclick="showSyncLoading(event)" style="background-color: #e67e22; border-color: #e67e22; font-size:14px; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);">
                                <i class="fas fa-sync-alt me-1"></i> Sinkronkan dari SIPO
                            </button>
                        </form>

                        <!-- Add User Button to Open Modal -->
                        <button type="button" class="btn btn-black rounded-3" data-bs-toggle="modal"
                            data-bs-target="#addUserModal">+ Tambah Struktur
                            Organisasi
                        </button>
                    </div>

                    {{-- TREANT JS BUAT STO --}}
                    <div id="struktur-org">
                        <div id="zoom-target">
                            <!-- Treant di render disini -->
                            <div id="tree-container"></div>
                        </div>
                    </div>

                    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.2.7/raphael.min.js"></script>
                    <script src="https://fperucic.github.io/treant-js/Treant.min.js"></script>

                    {{-- CEK LOG ERROR KIRIM DATA
                    <pre>{{ json_encode($mainDirector, JSON_PRETTY_PRINT) }}</pre> --}}

                    {{-- SCRIPT TREANT JS BUAT STO --}}
                    <script>
                        var chart_config = {
                            chart: {
                                container: "#tree-container",
                                connectors: {
                                    type: 'step'
                                },
                                node: {
                                    HTMLclass: 'nodeExample1',
                                    useHtml: true
                                },
                                nodeAlign: "BOTTOM",
                                levelSeparation: 50,
                                siblingSeparation: 50,
                                subtreeSeparation: 100
                            },
                            nodeStructure: @json($formatDirector)
                        };

                        let treantScale = 1;

                        function applyZoom() {
                            const zoomTarget = document.getElementById('zoom-target');
                            zoomTarget.style.transform = `scale(${treantScale})`;
                            zoomTarget.style.transformOrigin = '0 0';
                        }

                        function zoomTreant(factor) {
                            // Untuk + dan -
                            treantScale *= factor;
                            applyZoom();
                        }

                        function resetZoom() {
                            treantScale = 1;
                            applyZoom();
                            scrollToCenter();
                        }

                        new Treant(chart_config, function() {
                            console.log("Treant finished rendering");
                            applyZoom(); // apply initial zoom
                        });
                    </script>
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            const interval = setInterval(() => {
                                const container = document.querySelector("#struktur-org");
                                const zoomTarget = document.querySelector("#zoom-target");

                                if (container && zoomTarget) {
                                    const scrollLeft = (zoomTarget.scrollWidth * parseFloat(getComputedStyle(zoomTarget)
                                        .transform.split(',')[0].replace('matrix(', '')) / 2) - (container.clientWidth /
                                        2);
                                    const scrollTop = 0;

                                    container.scrollLeft = scrollLeft;
                                    container.scrollTop = scrollTop;

                                    clearInterval(interval);
                                }
                            }, 100);
                        });
                    </script>
                </div> <!--Penutup col 12-->
            </div> <!--Penutup Card Body Py-3-->
        </div> <!--Penutup card show sm border 0-->
    </div> <!--Penutup container fluid-->

    <!-- Modal Tambah Struktur Organisasi -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('organization-manage/add') }}" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" autocomplete="off" novalidate>
                @csrf
                <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                    <h5 class="modal-title fw-bold text-white" id="addUserModalLabel">
                        <i class="fas fa-plus-circle me-2"></i> Tambah Struktur Organisasi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4 bg-light">

                    <div class="mb-4">
                        <label for="type" class="form-label fw-bold small flex-grow-1" style="color: #253070;">JENIS STRUKTUR <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-sitemap"></i></span>
                            <select class="form-select border-start-0 fs-6" id="type" name="type" required>
                                <option value="" disabled selected>-- Pilih --</option>
                                <option value="Director">Direktur</option>
                                <option value="Divisi">Divisi</option>
                                <option value="Department">Departemen</option>
                                <option value="Section">Bagian</option>
                                <option value="Unit">Unit</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="parent_id" class="form-label fw-bold small flex-grow-1" style="color: #253070;">PARENT STRUKTUR</label>
                        <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-project-diagram"></i></span>
                            <select class="form-select border-start-0 fs-6 js-parent-org-select2" id="parent_id" name="parent_id" data-placeholder="Cari struktur organisasi">
                                <option value="">-- Pilih induk struktur --</option>
                                @php
                                    function renderOrgOptions($node, $level = 0)
                                    {
                                        $indent = str_repeat('&nbsp;', $level * 4);
                                        if (isset($node->name_director)) {
                                            echo "<option value='director-{$node->id_director}'>{$indent}Direktur: {$node->name_director}</option>";
                                        } elseif (isset($node->nm_divisi)) {
                                            echo "<option value='divisi-{$node->id_divisi}'>{$indent}--> Divisi: {$node->nm_divisi}</option>";
                                        } elseif (isset($node->name_department)) {
                                            echo "<option value='department-{$node->id_department}'>{$indent}-----> Departemen: {$node->name_department}</option>";
                                        } elseif (isset($node->name_section)) {
                                            echo "<option value='section-{$node->id_section}'>{$indent}--------> Bagian: {$node->name_section}</option>";
                                        } elseif (isset($node->name_unit)) {
                                            echo "<option value='unit-{$node->id_unit}'>{$indent}-----------> Unit: {$node->name_unit}</option>";
                                        }

                                        if (isset($node->subDirectors)) {
                                            foreach ($node->subDirectors as $subDir) {
                                                renderOrgOptions($subDir, $level + 1);
                                            }
                                        }
                                        if (isset($node->divisi)) {
                                            foreach ($node->divisi as $div) {
                                                renderOrgOptions($div, $level + 1);
                                            }
                                        }
                                        if (isset($node->department)) {
                                            if (isset($node->name_director)) {
                                                foreach ($node->department->whereNull('divisi_id_divisi') as $dept) {
                                                    renderOrgOptions($dept, $level + 1);
                                                }
                                            }
                                            if (isset($node->nm_divisi)) {
                                                foreach ($node->department as $dept) {
                                                    renderOrgOptions($dept, $level + 1);
                                                }
                                            }
                                        }
                                        if (isset($node->section)) {
                                            foreach ($node->section as $sec) {
                                                renderOrgOptions($sec, $level + 1);
                                            }
                                        }
                                        if (isset($node->unit)) {
                                            if (
                                                isset($node->name_department) &&
                                                $node->unit->whereNull('section_id_section')
                                            ) {
                                                foreach ($node->unit->whereNull('section_id_section') as $unit) {
                                                    renderOrgOptions($unit, $level + 1);
                                                }
                                            }
                                            if (isset($node->name_section)) {
                                                foreach ($node->unit as $unit) {
                                                    renderOrgOptions($unit, $level + 1);
                                                }
                                            }
                                        }
                                    }
                                    if ($mainDirector) {
                                        renderOrgOptions($mainDirector);
                                    }
                                @endphp
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="name" class="form-label fw-bold small flex-grow-1" style="color: #253070;">NAMA STRUKTUR <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-font"></i></span>
                            <input type="text" class="form-control border-start-0 fs-6" id="name" name="name" required placeholder="Masukkan nama struktur...">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="kode" class="form-label fw-bold small flex-grow-1" style="color: #253070;">KODE STRUKTUR</label>
                        <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-barcode"></i></span>
                            <input type="text" class="form-control border-start-0 fs-6" id="kode" name="kode" placeholder="Masukkan kode struktur...">
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light border-top-0 pt-3 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" style="background-color: #253070;">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="editForm" method="POST" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" autocomplete="off" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="fas fa-edit me-2"></i> Edit Struktur Organisasi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <input type="hidden" name="type" id="editType">
                    <input type="hidden" name="id" id="editId">
                    
                    <div class="mb-4">
                        <label for="editName" class="form-label fw-bold small flex-grow-1" style="color: #253070;">NAMA <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-font"></i></span>
                            <input type="text" class="form-control border-start-0 fs-6" id="editName" name="name" required>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <label for="editKode" class="form-label fw-bold small flex-grow-1" style="color: #253070;">KODE</label>
                        <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-barcode"></i></span>
                            <input type="text" class="form-control border-start-0 fs-6" id="editKode" name="kode">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 pt-3 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" style="background-color: #253070;">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus -->
    <div class="modal fade" id="deleteOrgModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-body p-5 text-center bg-light">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-4" style="width: 80px; height: 80px; background-color: #f1f3f5;">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-2">Konfirmasi Hapus</h4>
                    <p class="text-muted mb-3" style="font-size: 1rem;">
                        Anda yakin ingin menghapus data <br>
                        <strong id="deleteOrgName" class="text-danger fs-5"></strong>?
                    </p>
                    <div class="alert alert-warning border-0 rounded-3 small text-start mb-4">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        Semua data di bawahnya juga akan ikut terhapus secara permanen.
                    </div>
                    <div class="d-flex justify-content-center gap-3">
                        <button type="button" class="btn btn-light rounded-pill fw-bold py-2 shadow-sm border" style="width: 120px;" data-bs-dismiss="modal">Batalkan</button>
                        <button type="button" id="btnConfirmDeleteOrg" class="btn btn-danger rounded-pill fw-bold py-2 shadow-sm" style="width: 140px;">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const zoomLevel = 0.8; // Set your desired default zoom here (e.g., 0.8 = 80%)

            const waitForTreant = setInterval(() => {
                const nodeTree = document.querySelector('#struktur-org .Treant .node-tree');
                if (nodeTree) {
                    nodeTree.style.transform = `scale(${zoomLevel})`;
                    nodeTree.style.transformOrigin = 'top left';
                    clearInterval(waitForTreant);
                }
            }, 100);
            
            // Tampilkan session success / error
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: {!! json_encode(session('success')) !!},
                    confirmButtonColor: '#253070',
                    customClass: { popup: 'rounded-4 shadow' }
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: {!! json_encode(session('error')) !!},
                    confirmButtonColor: '#253070',
                    customClass: { popup: 'rounded-4 shadow' }
                });
            @endif
        });
    </script>
    <script>
        // treantScale is declared globally above

        function zoomTreant(factor) {

            if (factor === 1) {
                treantScale = 1;
            } else {
                treantScale *= factor;
                treantScale = Math.max(0.2, Math.min(treantScale, 3));
            }

            const treantContent = document.querySelector("#struktur-org");
            if (treantContent) {
                treantContent.style.transform = 'scale(' + treantScale + ')';
                treantContent.style.transformOrigin = '0 0';
                console.log("Zoom clicked", factor);

            }
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editModal = document.getElementById('editModal');
            editModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const type = button.getAttribute('data-type');
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                const kode = button.getAttribute('data-kode');

                editModal.querySelector('#editType').value = type;
                editModal.querySelector('#editId').value = id;
                editModal.querySelector('#editName').value = name;
                editModal.querySelector('#editKode').value = kode;

                editModal.querySelector('#editForm').action = `/organization/${type}/${id}`;
            });
        });

        let activeDeleteUrl = '';
        let deleteModal = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            deleteModal = new bootstrap.Modal(document.getElementById('deleteOrgModal'));
            
            document.getElementById('btnConfirmDeleteOrg').addEventListener('click', function() {
                if (!activeDeleteUrl) return;
                
                Swal.fire({
                    title: 'Menghapus...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch(activeDeleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                }).then(res => {
                    Swal.close();
                    deleteModal.hide();
                    if (res.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Struktur organisasi berhasil dihapus.',
                            confirmButtonColor: '#253070',
                            customClass: { popup: 'rounded-4 shadow' }
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Tidak dapat menghapus data.',
                            confirmButtonColor: '#253070',
                            customClass: { popup: 'rounded-4 shadow' }
                        });
                    }
                }).catch(() => {
                    Swal.close();
                    deleteModal.hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat menghubungi server.',
                        confirmButtonColor: '#253070',
                        customClass: { popup: 'rounded-4 shadow' }
                    });
                });
            });
        });

        function confirmDelete(url, name) {
            activeDeleteUrl = url;
            const nameEl = document.getElementById('deleteOrgName');
            if (nameEl) {
                nameEl.textContent = name;
            }
            if (deleteModal) {
                deleteModal.show();
            }
        }

        if (window.jQuery && $.fn.select2) {
            const $parentSelect = $('#parent_id');
            const $addModal = $('#addUserModal');

            if ($parentSelect.length && !$parentSelect.hasClass('select2-hidden-accessible')) {
                $parentSelect.select2({
                    width: '100%',
                    dropdownParent: $addModal.length ? $addModal : $(document.body),
                    placeholder: $parentSelect.data('placeholder') || 'Cari struktur organisasi',
                    allowClear: true
                });
            }
        }

        function showSyncLoading(event) {
            event.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Menyinkronkan Data...',
                    text: 'Harap tunggu, sedang mengambil data dari API SIPO.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
            document.getElementById('syncForm').submit();
        }
    </script>

@endsection
