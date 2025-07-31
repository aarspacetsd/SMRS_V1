@extends('layouts/layoutMaster')

@section('title', 'Haematology Test References')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi DataTable
            $('.datatables-basic').DataTable();

            // Inisialisasi Select2
            $('.select2').each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: $this.data('placeholder'),
                    dropdownParent: $this.closest('.card-body')
                });
            });
        });
    </script>
@endsection

@section('content')
    <h4 class="mb-4">Haematology Test</h4>

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
        <!-- Kolom Kiri: Tabel Daftar Tes -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-datatable table-responsive pt-0">
                    <table class="datatables-basic table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Test References</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tests as $test)
                                <tr>
                                    <td>{{ $test->id }}</td>
                                    <td>{{ $test->name }}</td>
                                    <td>
                                        @forelse ($test->test_references as $reference)
                                            <span class="badge bg-label-secondary me-1">{{ $reference->name }}</span>
                                        @empty
                                            <span class="text-muted">Belum ada referensi</span>
                                        @endforelse
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Form Tambah Referensi -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Add Test References</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('haematology-test.sync') }}" method="POST">
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
                            <label for="test_reference_ids" class="form-label">Test References</label>
                            <select id="test_reference_ids" name="test_reference_ids[]" class="select2 form-select"
                                data-placeholder="Pilih Referensi" multiple required>
                                @foreach ($test_references as $reference)
                                    <option value="{{ $reference->id }}">{{ $reference->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex justify-content-start">
                            <button type="submit" class="btn btn-primary me-2">Add</button>
                            <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
