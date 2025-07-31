@extends('layouts/layoutMaster')

@section('title', 'Manajemen Paket Tes')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Simpan data dari PHP ke variabel JS
            const packagesData = @json($packages->keyBy('id'));
            const testsData = @json($tests);

            // Inisialisasi DataTable
            const dt_basic = $('.datatables-basic').DataTable();

            // Inisialisasi Select2
            const select2 = $('.select2');
            if (select2.length) {
                select2.each(function() {
                    var $this = $(this);
                    $this.wrap('<div class="position-relative"></div>').select2({
                        placeholder: 'Pilih Tes',
                        dropdownParent: $this.closest('.offcanvas')
                    });
                });
            }

            const packageFormOffcanvasEl = document.getElementById('package-form-offcanvas');
            const bsOffcanvas = new bootstrap.Offcanvas(packageFormOffcanvasEl);
            const packageForm = document.getElementById('package-form');
            const offcanvasTitle = document.getElementById('offcanvas-title');
            const testSelect = $('#test_ids');

            // Fungsi untuk mereset form
            const resetForm = () => {
                packageForm.reset();
                testSelect.val(null).trigger('change'); // Reset Select2
                offcanvasTitle.innerText = 'Tambah Paket Baru';
                // --- PERBAIKAN: Menambahkan prefix 'reports.' pada nama route ---
                packageForm.action = "{{ route('reports.packages.store') }}";
                if (packageForm.querySelector('input[name="_method"]')) {
                    packageForm.querySelector('input[name="_method"]').remove();
                }
            };

            // Menangani tombol "Tambah Paket Baru"
            document.getElementById('add-package-btn').addEventListener('click', function() {
                resetForm();
                bsOffcanvas.show();
            });

            // Menangani tombol "Edit"
            document.querySelector('.datatables-basic tbody').addEventListener('click', function(event) {
                const editButton = event.target.closest('.edit-btn');
                if (!editButton) return;

                const packageId = editButton.dataset.id;
                const packageData = packagesData[packageId];
                if (!packageData) return;

                resetForm(); // Reset form dulu
                offcanvasTitle.innerText = 'Edit Paket';

                // --- PERBAIKAN: Menambahkan prefix 'reports.' pada nama route ---
                const updateUrl = "{{ route('reports.packages.update', ['package' => 'PLACEHOLDER']) }}"
                    .replace('PLACEHOLDER', packageId);
                packageForm.action = updateUrl;
                if (!packageForm.querySelector('input[name="_method"]')) {
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PUT';
                    packageForm.prepend(methodInput);
                }

                // Isi form dengan data yang ada
                packageForm.querySelector('[name="name"]').value = packageData.name;
                packageForm.querySelector('[name="price"]').value = packageData.price;
                packageForm.querySelector('[name="description"]').value = packageData.description || '';

                // Pilih tes yang sesuai di Select2
                const selectedTestIds = packageData.tests.map(test => test.id);
                testSelect.val(selectedTestIds).trigger('change');

                bsOffcanvas.show();
            });
        });
    </script>
@endsection


@section('content')
    <h4>Manajemen Paket Tes Laboratorium</h4>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Daftar Paket</h5>
            <button class="btn btn-primary" id="add-package-btn">
                <i class="ti ti-plus me-1"></i> Tambah Paket Baru
            </button>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Paket</th>
                        <th>Harga</th>
                        <th>Tes Termasuk</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($packages as $package)
                        <tr>
                            <td>{{ $package->id }}</td>
                            <td>{{ $package->name }}</td>
                            <td>Rp {{ number_format($package->price, 0, ',', '.') }}</td>
                            <td>
                                @forelse ($package->tests as $test)
                                    <span class="badge bg-label-secondary me-1">{{ $test->name }}</span>
                                @empty
                                    <span class="badge bg-label-warning">Belum ada tes</span>
                                @endforelse
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-icon edit-btn"
                                        data-id="{{ $package->id }}" data-bs-toggle="tooltip"
                                        data-bs-original-title="Edit Paket">
                                        <i class="text-primary ti ti-pencil"></i>
                                    </button>
                                    {{-- --- PERBAIKAN: Menambahkan prefix 'reports.' pada nama route --- --}}
                                    <form action="{{ route('reports.packages.destroy', $package->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon" data-bs-toggle="tooltip"
                                            data-bs-original-title="Hapus Paket"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus paket ini?');">
                                            <i class="text-danger ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Offcanvas Form untuk Tambah/Edit Paket -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="package-form-offcanvas">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvas-title">Form Paket</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form id="package-form" method="POST">
                @csrf
                {{-- Method PUT akan ditambahkan oleh JS saat edit --}}

                <div class="mb-3">
                    <label class="form-label" for="name">Nama Paket</label>
                    <input type="text" class="form-control" id="name" name="name"
                        placeholder="Contoh: Paket Demam Lengkap" required />
                </div>

                <div class="mb-3">
                    <label class="form-label" for="price">Harga</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" id="price" name="price" class="form-control"
                            placeholder="Contoh: 350000" required />
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="test_ids">Pilih Tes</label>
                    <select id="test_ids" name="test_ids[]" class="select2 form-select" multiple required>
                        @foreach ($tests as $test)
                            <option value="{{ $test->id }}">{{ $test->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="description">Deskripsi (Opsional)</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-primary me-sm-3 me-1">Simpan</button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>
@endsection
