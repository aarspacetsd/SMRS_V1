@extends('layouts/layoutMaster')

@section('title', 'Manajemen Tes Laboratorium')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Simpan data dari PHP ke variabel JS
            const testsData = @json($tests->keyBy('id'));

            // Inisialisasi DataTable
            $('.datatables-basic').DataTable();

            // Inisialisasi Select2
            const select2 = $('.select2');
            if (select2.length) {
                select2.each(function() {
                    var $this = $(this);
                    $this.wrap('<div class="position-relative"></div>').select2({
                        placeholder: $this.data('placeholder'),
                        dropdownParent: $this.closest('.offcanvas')
                    });
                });
            }

            const testFormOffcanvasEl = document.getElementById('test-form-offcanvas');
            const bsOffcanvas = new bootstrap.Offcanvas(testFormOffcanvasEl);
            const testForm = document.getElementById('test-form');
            const offcanvasTitle = document.getElementById('offcanvas-title');

            // Fungsi untuk mereset form
            const resetForm = () => {
                testForm.reset();
                $('.select2').val(null).trigger('change'); // Reset Select2
                offcanvasTitle.innerText = 'Tambah Tes Baru';
                testForm.action = "{{ route('tests.store') }}";
                if (testForm.querySelector('input[name="_method"]')) {
                    testForm.querySelector('input[name="_method"]').remove();
                }
                // Set status default ke aktif (1)
                testForm.querySelector('[name="status"]').value = '1';
            };

            // Menangani tombol "Tambah Tes Baru"
            document.getElementById('add-test-btn').addEventListener('click', function() {
                resetForm();
                bsOffcanvas.show();
            });

            // Menangani tombol "Edit"
            document.querySelector('.datatables-basic tbody').addEventListener('click', function(event) {
                const editButton = event.target.closest('.edit-btn');
                if (!editButton) return;

                const testId = editButton.dataset.id;
                const testData = testsData[testId];
                if (!testData) return;

                resetForm();
                offcanvasTitle.innerText = 'Edit Tes';

                // Set action dan method untuk update
                const updateUrl = "{{ route('tests.update', ['test' => 'PLACEHOLDER']) }}".replace(
                    'PLACEHOLDER', testId);
                testForm.action = updateUrl;
                if (!testForm.querySelector('input[name="_method"]')) {
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PUT';
                    testForm.prepend(methodInput);
                }

                // Isi form dengan data yang ada
                testForm.querySelector('[name="name"]').value = testData.name;
                $('#service_id').val(testData.service_id).trigger('change');
                $('#report_type').val(testData.report_type).trigger('change');
                testForm.querySelector('[name="status"]').value = testData.status;

                bsOffcanvas.show();
            });
        });
    </script>
@endsection


@section('content')
    <h4>Manajemen Tes Laboratorium</h4>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Daftar Tes</h5>
            <button class="btn btn-primary" id="add-test-btn">
                <i class="ti ti-plus me-1"></i> Tambah Tes Baru
            </button>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Tes</th>
                        <th>Layanan Terkait</th>
                        <th>Tipe Laporan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tests as $test)
                        <tr>
                            <td>{{ $test->id }}</td>
                            <td>{{ $test->name }}</td>
                            <td>{{ $test->service->name ?? 'N/A' }}</td>
                            <td>{{ ucfirst($test->report_type) }}</td>
                            <td>
                                @if ($test->status)
                                    <span class="badge bg-label-success">Aktif</span>
                                @else
                                    <span class="badge bg-label-danger">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-icon edit-btn"
                                        data-id="{{ $test->id }}" data-bs-toggle="tooltip"
                                        data-bs-original-title="Edit Tes">
                                        <i class="text-primary ti ti-pencil"></i>
                                    </button>
                                    <form action="{{ route('tests.toggleStatus', $test->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-icon" data-bs-toggle="tooltip"
                                            data-bs-original-title="Ubah Status">
                                            <i class="text-warning ti ti-toggle-left"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('tests.destroy', $test->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon" data-bs-toggle="tooltip"
                                            data-bs-original-title="Hapus Tes"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus tes ini?');">
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

    <!-- Offcanvas Form untuk Tambah/Edit Tes -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="test-form-offcanvas">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvas-title">Form Tes</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form id="test-form" method="POST">
                @csrf
                {{-- Method PUT akan ditambahkan oleh JS saat edit --}}

                <div class="mb-3">
                    <label class="form-label" for="name">Nama Tes</label>
                    <input type="text" class="form-control" id="name" name="name"
                        placeholder="Contoh: Hemoglobin" required />
                </div>

                <div class="mb-3">
                    <label class="form-label" for="service_id">Layanan Terkait</label>
                    <select id="service_id" name="service_id" class="select2 form-select" data-placeholder="Pilih Layanan"
                        required>
                        <option value=""></option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="report_type">Tipe Laporan</label>
                    <select id="report_type" name="report_type" class="select2 form-select"
                        data-placeholder="Pilih Tipe Laporan" required>
                        <option value=""></option>
                        <option value="haematology">Haematology</option>
                        <option value="biochemistry">Biochemistry</option>
                        <option value="examination">Examination</option>
                        <option value="microbiology">Microbiology</option>
                        <option value="stain">Stain</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="1">Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary me-sm-3 me-1">Simpan</button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>
@endsection
