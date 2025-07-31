@extends('layouts/layoutMaster')

@section('title', 'Microbiology Test')

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
            $('.datatables-antibiotics').DataTable({
                pageLength: 5,
                lengthChange: false
            });

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
    <h4 class="mb-4">Microbiology Test</h4>

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
        {{-- --- PERBAIKAN: Mengubah tata letak menjadi vertikal --- --}}

        <!-- Baris 1: Microbiology Test & Add Test Antibiotics -->
        <div class="col-lg-8">
            <div class="card">
                <h5 class="card-header">Microbiology Test</h5>
                <div class="card-datatable table-responsive pt-0">
                    <table class="datatables-basic table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Antibiotics</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tests as $test)
                                <tr>
                                    <td>{{ $test->name }}</td>
                                    <td>
                                        @forelse ($test->test_antibiotics as $antibiotic)
                                            <span class="badge bg-label-secondary me-1">{{ $antibiotic->name }}</span>
                                        @empty
                                            <span class="text-muted">N/A</span>
                                        @endforelse
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <h5 class="card-header">Add Test Antibiotics</h5>
                <div class="card-body">
                    <form action="{{ route('microbiology-test.sync') }}" method="POST">
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
                            <label for="test_antibiotics_ids" class="form-label">Antibiotics</label>
                            <select id="test_antibiotics_ids" name="test_antibiotics_ids[]" class="select2 form-select"
                                data-placeholder="Pilih Antibiotik" multiple required>
                                @foreach ($test_antibiotics as $antibiotic)
                                    <option value="{{ $antibiotic->id }}">{{ $antibiotic->name }}</option>
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

        <!-- Baris 2: Antibiotics -->
        <div class="col-lg-12">
            <div class="card">
                <h5 class="card-header">Antibiotics</h5>
                <div class="card-body">
                    <form action="{{ route('test-antibiotics.store') }}" method="POST" class="mb-3">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="name" class="form-control" placeholder="Add new antibiotic"
                                required>
                            <button class="btn btn-primary" type="submit">Add</button>
                        </div>
                    </form>
                    <div class="table-responsive text-nowrap">
                        <table class="datatables-antibiotics table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($test_antibiotics as $antibiotic)
                                    <tr>
                                        <td>{{ $antibiotic->name }}</td>
                                        <td>
                                            <div class="d-flex">
                                                {{-- Edit bisa ditambahkan dengan modal jika perlu --}}
                                                <form action="{{ route('test-antibiotics.destroy', $antibiotic->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-icon btn-danger"
                                                        onclick="return confirm('Yakin hapus?');">
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
        </div>
    </div>
@endsection
