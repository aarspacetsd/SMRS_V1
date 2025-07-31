@extends('layouts/layoutMaster')

@section('title', 'Stain Test')

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
            const stainsData = @json($test_stains->keyBy('id'));

            // Inisialisasi DataTable & Select2
            $('.datatables-basic').DataTable();
            $('.select2').each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: $this.data('placeholder'),
                    dropdownParent: $this.closest('.card-body')
                });
            });

            const stainForm = document.getElementById('stain-form');
            const formTitle = document.getElementById('form-title');
            const testNameContainer = document.getElementById('test-name-container');

            // Fungsi untuk menambah input field 'Test Name' baru
            document.getElementById('add-more-btn').addEventListener('click', function() {
                const newDiv = document.createElement('div');
                newDiv.className = 'input-group mb-2';
                newDiv.innerHTML = `
                <input type="text" name="test_names[]" class="form-control" required />
                <button type="button" class="btn btn-outline-danger remove-test-name-btn">
                    <i class="ti ti-x"></i>
                </button>
            `;
                testNameContainer.appendChild(newDiv);
            });

            // Fungsi untuk menghapus input field 'Test Name'
            testNameContainer.addEventListener('click', function(event) {
                if (event.target.closest('.remove-test-name-btn')) {
                    event.target.closest('.input-group').remove();
                }
            });

            // Fungsi untuk mereset form ke mode "Tambah"
            const resetToAddMode = () => {
                stainForm.reset();
                $('.select2').val(null).trigger('change');
                formTitle.innerText = 'Add Test Stain';
                stainForm.action = "{{ route('stain-tests.store') }}";
                if (stainForm.querySelector('input[name="_method"]')) {
                    stainForm.querySelector('input[name="_method"]').remove();
                }
                // Sisakan satu input field kosong
                testNameContainer.innerHTML = `
                <div class="input-group mb-2">
                    <input type="text" name="test_names[]" class="form-control" placeholder="Add new test" required />
                </div>
            `;
                document.getElementById('form-submit-btn').innerText = 'Add';
            };

            document.getElementById('form-reset-btn').addEventListener('click', resetToAddMode);

            // Menangani tombol "Edit"
            document.querySelector('.datatables-basic tbody').addEventListener('click', function(event) {
                const editButton = event.target.closest('.edit-btn');
                if (!editButton) return;

                const stainId = editButton.dataset.id;
                const stainData = stainsData[stainId];
                if (!stainData) return;

                resetToAddMode();
                formTitle.innerText = 'Edit Test Stain';
                document.getElementById('form-submit-btn').innerText = 'Update';

                // Set action dan method untuk update
                const updateUrl = "{{ route('stain-tests.update', ['stain_test' => 'PLACEHOLDER']) }}"
                    .replace('PLACEHOLDER', stainId);
                stainForm.action = updateUrl;
                if (!stainForm.querySelector('input[name="_method"]')) {
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PUT';
                    stainForm.prepend(methodInput);
                }

                // Isi form dengan data yang ada
                $('#test_id').val(stainData.test_id).trigger('change');

                // Buat ulang input fields untuk test_names
                testNameContainer.innerHTML = '';
                if (stainData.test_names && stainData.test_names.length > 0) {
                    stainData.test_names.forEach(name => {
                        const newDiv = document.createElement('div');
                        newDiv.className = 'input-group mb-2';
                        newDiv.innerHTML = `
                        <input type="text" name="test_names[]" class="form-control" value="${name}" required />
                        <button type="button" class="btn btn-outline-danger remove-test-name-btn">
                            <i class="ti ti-x"></i>
                        </button>
                    `;
                        testNameContainer.appendChild(newDiv);
                    });
                }
            });
        });
    </script>
@endsection

@section('content')
    <h4 class="mb-4">Stain Test</h4>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
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

    <div class="row g-4">
        <!-- Kolom Kiri: Tabel Daftar -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-datatable table-responsive pt-0">
                    <table class="datatables-basic table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Tests</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($test_stains as $stain)
                                <tr>
                                    <td>{{ $stain->test->name ?? 'N/A' }}</td>
                                    <td>
                                        @if (is_array($stain->test_names))
                                            {{ implode(', ', $stain->test_names) }}
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <button type="button" class="btn btn-sm btn-icon btn-primary me-2 edit-btn"
                                                data-id="{{ $stain->id }}" data-bs-toggle="tooltip" title="Edit">
                                                <i class="ti ti-pencil"></i>
                                            </button>
                                            <form action="{{ route('stain-tests.destroy', $stain->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon btn-danger"
                                                    data-bs-toggle="tooltip" title="Hapus"
                                                    onclick="return confirm('Apakah Anda yakin?');">
                                                    <i class="ti ti-trash"></i>
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
        </div>

        <!-- Kolom Kanan: Form Tambah/Edit -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0" id="form-title">Add Test Stain</h5>
                </div>
                <div class="card-body">
                    <form id="stain-form" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="test_id" class="form-label">Select Test</label>
                            <select id="test_id" name="test_id" class="select2 form-select" data-placeholder="Pilih Tes"
                                required>
                                <option value=""></option>
                                @foreach ($tests as $test)
                                    <option value="{{ $test->id }}">{{ $test->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Test Name</label>
                            <div id="test-name-container">
                                <div class="input-group mb-2">
                                    <input type="text" name="test_names[]" class="form-control"
                                        placeholder="Add new test" required />
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="add-more-btn">Add
                                More</button>
                        </div>

                        <div class="d-flex justify-content-start">
                            <button type="submit" class="btn btn-primary me-2" id="form-submit-btn">Add</button>
                            <button type="button" class="btn btn-outline-secondary" id="form-reset-btn">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
