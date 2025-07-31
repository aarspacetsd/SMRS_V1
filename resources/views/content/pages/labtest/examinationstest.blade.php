@extends('layouts/layoutMaster')

@section('title', 'Manajemen Tes Pemeriksaan')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- INISIALISASI ---
            if ($('#examinations-table').length) {
                $('#examinations-table').DataTable({
                    // Konfigurasi tambahan untuk mengatasi masalah column count
                    columnDefs: [{
                            orderable: false,
                            targets: 4
                        } // Kolom aksi tidak bisa diurutkan
                    ],
                    language: {
                        emptyTable: "Belum ada data pemeriksaan",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                        infoFiltered: "(difilter dari _MAX_ total data)",
                        lengthMenu: "Tampilkan _MENU_ data",
                        loadingRecords: "Memuat...",
                        processing: "Sedang memproses...",
                        search: "Cari:",
                        zeroRecords: "Tidak ada data yang cocok",
                        paginate: {
                            first: "Pertama",
                            last: "Terakhir",
                            next: "Selanjutnya",
                            previous: "Sebelumnya"
                        }
                    }
                });
            }

            const selectTest = $('#test_id');
            if (selectTest.length) {
                selectTest.wrap('<div class="position-relative"></div>').select2({
                    placeholder: 'Pilih sebuah tes',
                    dropdownParent: selectTest.parent()
                });
            }

            // --- ELEMEN UI ---
            const formOffcanvasElement = document.getElementById('examination-form-offcanvas');
            const bsOffcanvas = new bootstrap.Offcanvas(formOffcanvasElement);
            const examinationForm = document.getElementById('examination-form');
            const offcanvasTitle = document.getElementById('offcanvas-title');
            const examinationsData = JSON.parse(`{!! addslashes(
                $test_examinations->getCollection()->keyBy('id')->map(function ($item) {
                        $item->macroscopics = json_encode(json_decode($item->macroscopics ?? '[]'));
                        $item->microscopics = json_encode(json_decode($item->microscopics ?? '[]'));
                        return $item;
                    })->toJson(),
            ) !!}`);

            // --- URL ---
            const storeUrl = "{{ route('examinations.store') }}";
            const updateUrlTemplate = "{{ route('examinations.update', ['examination' => 'PLACEHOLDER']) }}";

            // --- FUNGSI UNTUK INPUT DINAMIS ---
            function setupDynamicInputs(containerId, buttonId, fieldName) {
                const container = document.getElementById(containerId);
                document.getElementById(buttonId).addEventListener('click', function() {
                    const div = document.createElement('div');
                    div.className = 'input-group mb-2';
                    div.innerHTML = `
                <input type="text" name="${fieldName}[]" class="form-control" required>
                <button class="btn btn-outline-danger remove-item-btn" type="button">Hapus</button>
            `;
                    container.appendChild(div);
                });

                container.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-item-btn')) {
                        e.target.closest('.input-group').remove();
                    }
                });
            }

            setupDynamicInputs('macroscopic-container', 'add-macroscopic-btn', 'macroscopic');
            setupDynamicInputs('microscopic-container', 'add-microscopic-btn', 'microscopic');

            function renderDynamicInputs(containerId, items, fieldName) {
                const container = document.getElementById(containerId);
                container.innerHTML = ''; // Kosongkan container
                if (items && Array.isArray(items)) {
                    items.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'input-group mb-2';
                        div.innerHTML = `
                    <input type="text" name="${fieldName}[]" class="form-control" value="${item}" required>
                    <button class="btn btn-outline-danger remove-item-btn" type="button">Hapus</button>
                `;
                        container.appendChild(div);
                    });
                }
            }

            // --- EVENT HANDLER ---
            // Tombol "Tambah Pemeriksaan"
            document.getElementById('add-examination-btn').addEventListener('click', function() {
                examinationForm.reset();
                selectTest.val(null).trigger('change');
                offcanvasTitle.innerText = 'Tambah Tes Pemeriksaan';
                examinationForm.action = storeUrl;
                document.querySelector('input[name="_method"]')?.remove();

                document.getElementById('macroscopic-container').innerHTML = '';
                document.getElementById('microscopic-container').innerHTML = '';

                bsOffcanvas.show();
            });

            // Tombol "Edit" di tabel
            document.querySelector('#examinations-table tbody').addEventListener('click', function(event) {
                const editButton = event.target.closest('.edit-btn');
                if (!editButton) return;

                const examinationId = editButton.dataset.id;
                const data = examinationsData[examinationId];
                if (!data) return;

                examinationForm.reset();
                offcanvasTitle.innerText = 'Edit Tes Pemeriksaan';
                examinationForm.action = updateUrlTemplate.replace('PLACEHOLDER', data.id);

                if (!examinationForm.querySelector('input[name="_method"]')) {
                    examinationForm.insertAdjacentHTML('afterbegin',
                        '<input type="hidden" name="_method" value="PUT">');
                }

                selectTest.val(data.test_id).trigger('change');
                const macroscopics = JSON.parse(data.macroscopics || '[]');
                const microscopics = JSON.parse(data.microscopics || '[]');
                renderDynamicInputs('macroscopic-container', macroscopics, 'macroscopic');
                renderDynamicInputs('microscopic-container', microscopics, 'microscopic');

                bsOffcanvas.show();
            });

            // Tombol "Hapus" di tabel
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Anda yakin?',
                        text: "Data yang dihapus tidak dapat dipulihkan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal',
                        customClass: {
                            confirmButton: 'btn btn-primary me-3',
                            cancelButton: 'btn btn-label-secondary'
                        },
                        buttonsStyling: false
                    }).then(result => {
                        if (result.isConfirmed) {
                            e.target.submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection

@section('content')
    <h4>Manajemen Tes Pemeriksaan</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <!-- DataTable -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Daftar Pemeriksaan</h5>
            <button id="add-examination-btn" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Tambah Pemeriksaan
            </button>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <table id="examinations-table" class="datatables-basic table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Tes</th>
                        <th>Macroscopic</th>
                        <th>Microscopic</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($test_examinations as $examination)
                        @php
                            // Pengecekan keamanan yang sangat ketat untuk mencegah error render
                            $macroscopics = [];
                            $microscopics = [];
                            try {
                                // Pastikan data adalah string sebelum di-decode
                                if (!empty($examination->macroscopics) && is_string($examination->macroscopics)) {
                                    $decoded = json_decode($examination->macroscopics, true);
                                    // Pastikan hasil decode adalah array
                                    if (is_array($decoded)) {
                                        $macroscopics = $decoded;
                                    }
                                }
                                if (!empty($examination->microscopics) && is_string($examination->microscopics)) {
                                    $decoded = json_decode($examination->microscopics, true);
                                    if (is_array($decoded)) {
                                        $microscopics = $decoded;
                                    }
                                }
                            } catch (\Exception $e) {
                                // Jika terjadi error saat decode, biarkan array tetap kosong
                            }
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration + $test_examinations->firstItem() - 1 }}</td>
                            <td>{{ $examination->test->name ?? 'N/A' }}</td>
                            <td>
                                @if (count($macroscopics) > 0)
                                    <ul class="mb-0">
                                        @foreach ($macroscopics as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if (count($microscopics) > 0)
                                    <ul class="mb-0">
                                        @foreach ($microscopics as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-icon edit-btn"
                                        data-id="{{ $examination->id }}" data-bs-toggle="tooltip"
                                        data-bs-original-title="Edit Data">
                                        <i class="text-primary ti ti-pencil"></i>
                                    </button>
                                    <form action="{{ route('examinations.destroy', $examination->id) }}" method="POST"
                                        class="d-inline delete-form ms-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon" data-bs-toggle="tooltip"
                                            data-bs-original-title="Hapus Data">
                                            <i class="text-danger ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        {{-- Hapus baris @empty atau pastikan setiap <td> memiliki kolom yang tepat --}}
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $test_examinations->links() }}
        </div>
    </div>

    <!-- Add/Edit Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="examination-form-offcanvas">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvas-title">Form Pemeriksaan</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form id="examination-form" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="test_id">Pilih Tes</label>
                    <select id="test_id" name="test_id" class="select2 form-select" required>
                        <option value="" disabled selected>Pilih sebuah tes</option>
                        @foreach ($tests as $test)
                            <option value="{{ $test->id }}">{{ $test->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Macroscopic</label>
                    <div id="macroscopic-container"></div>
                    <button type="button" id="add-macroscopic-btn" class="btn btn-sm btn-outline-primary mt-2">Tambah
                        Macroscopic</button>
                </div>
                <div class="mb-3">
                    <label class="form-label">Microscopic</label>
                    <div id="microscopic-container"></div>
                    <button type="button" id="add-microscopic-btn" class="btn btn-sm btn-outline-primary mt-2">Tambah
                        Microscopic</button>
                </div>
                <button type="submit" class="btn btn-primary me-sm-3 me-1">Simpan</button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>
@endsection
