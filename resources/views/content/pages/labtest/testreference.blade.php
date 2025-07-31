@extends('layouts/layoutMaster')

@section('title', 'Test References')

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
            const referencesData = @json($test_references->keyBy('id'));

            // Inisialisasi DataTable
            $('.datatables-basic').DataTable();

            // Inisialisasi Select2
            const select2 = $('.select2');
            if (select2.length) {
                select2.each(function() {
                    var $this = $(this);
                    $this.wrap('<div class="position-relative"></div>').select2({
                        placeholder: $this.data('placeholder'),
                        dropdownParent: $this.closest('.card-body'),
                        allowClear: true
                    });
                });
            }

            const referenceForm = document.getElementById('reference-form');
            const formTitle = document.getElementById('form-title');
            const parentSelect = $('#parent_id');

            // Fungsi untuk mereset form ke mode "Tambah"
            const resetToAddMode = () => {
                referenceForm.reset();
                parentSelect.val(null).trigger('change');
                formTitle.innerText = 'Add Test References';
                referenceForm.action = "{{ route('test-references.store') }}";
                if (referenceForm.querySelector('input[name="_method"]')) {
                    referenceForm.querySelector('input[name="_method"]').remove();
                }
                document.getElementById('form-submit-btn').innerText = 'Add';
            };

            // Event listener untuk tombol reset
            document.getElementById('form-reset-btn').addEventListener('click', resetToAddMode);

            // Menangani tombol "Edit" (ikon pensil)
            document.querySelector('.datatables-basic tbody').addEventListener('click', function(event) {
                const editButton = event.target.closest('.edit-btn');
                if (!editButton) return;

                const referenceId = editButton.dataset.id;
                const referenceData = referencesData[referenceId];
                if (!referenceData) return;

                // Reset form dan set ke mode "Edit"
                resetToAddMode();
                formTitle.innerText = 'Edit Test Reference';
                document.getElementById('form-submit-btn').innerText = 'Update';

                // Set action dan method untuk update
                const updateUrl =
                    "{{ route('test-references.update', ['test_reference' => 'PLACEHOLDER']) }}".replace(
                        'PLACEHOLDER', referenceId);
                referenceForm.action = updateUrl;
                if (!referenceForm.querySelector('input[name="_method"]')) {
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PUT';
                    referenceForm.prepend(methodInput);
                }

                // Isi form dengan data yang ada
                referenceForm.querySelector('[name="name"]').value = referenceData.name;
                referenceForm.querySelector('[name="unit"]').value = referenceData.unit || '';
                referenceForm.querySelector('[name="ref_range"]').value = referenceData.ref_range || '';
                parentSelect.val(referenceData.parent_id).trigger('change');

                // Scroll ke atas agar form terlihat
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
    </script>
@endsection

@section('content')
    <h4 class="mb-4">Test References</h4>

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

    <div class="row g-4">
        <!-- Kolom Kiri: Tabel Daftar Referensi -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-datatable table-responsive pt-0">
                    <table class="datatables-basic table">
                        <thead>
                            <tr>
                                <th>Test Name</th>
                                <th>Unit</th>
                                <th>Ref Range</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($test_references as $reference)
                                <tr>
                                    <td>{{ $reference->name }}</td>
                                    <td>{{ $reference->unit }}</td>
                                    <td>{{ $reference->ref_range }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <button type="button" class="btn btn-sm btn-icon btn-primary me-2 edit-btn"
                                                data-id="{{ $reference->id }}" data-bs-toggle="tooltip" title="Edit">
                                                <i class="ti ti-pencil"></i>
                                            </button>
                                            <form action="{{ route('test-references.destroy', $reference->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon btn-danger"
                                                    data-bs-toggle="tooltip" title="Hapus"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus item ini?');">
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

        <!-- Kolom Kanan: Form Tambah/Edit Referensi -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0" id="form-title">Add Test References</h5>
                </div>
                <div class="card-body">
                    <form id="reference-form" method="POST">
                        @csrf
                        {{-- Method _method akan ditambahkan oleh JS saat edit --}}

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" id="name" name="name" class="form-control" required />
                        </div>

                        <div class="mb-3">
                            <label for="unit" class="form-label">Unit</label>
                            <input type="text" id="unit" name="unit" class="form-control" />
                        </div>

                        <div class="mb-3">
                            <label for="ref_range" class="form-label">Ref Range</label>
                            <input type="text" id="ref_range" name="ref_range" class="form-control" />
                        </div>

                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Parent Test</label>
                            <select id="parent_id" name="parent_id" class="select2 form-select"
                                data-placeholder="Pilih Induk (Opsional)">
                                <option value=""></option>
                                @foreach ($parent_references as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                @endforeach
                            </select>
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
