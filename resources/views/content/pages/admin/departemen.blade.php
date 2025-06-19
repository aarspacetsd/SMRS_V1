@php
    use Illuminate\Support\Facades\Auth;
    // Mendapatkan role pengguna, default 'Guest' jika tidak login
    $role = Auth::check() ? Auth::user()->role : 'Guest';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Manajemen Departemen')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    <script>
        // Inisialisasi DataTables
        document.addEventListener('DOMContentLoaded', function() {
            $('.datatables-basic').DataTable();

            // Script untuk mengirim data ke modal edit
            const editModal = document.getElementById('editDepartmentModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function(event) {
                    console.log('Modal edit terbuka...'); // PESAN DEBUG 1

                    const button = event.relatedTarget;
                    const action = button.getAttribute('data-action');
                    const name = button.getAttribute('data-name');

                    console.log('Action URL yang akan digunakan:', action); // PESAN DEBUG 2

                    const modalTitle = editModal.querySelector('.modal-title');
                    const modalBodyInput = editModal.querySelector('.modal-body #edit_department_name');
                    const editForm = editModal.querySelector('#editDepartmentForm');

                    if (editForm) {
                        modalTitle.textContent = 'Edit Departemen: ' + name;
                        modalBodyInput.value = name;
                        editForm.action = action; // Mengatur action form
                    } else {
                        console.error(
                            'Form dengan ID "editDepartmentForm" tidak ditemukan di dalam modal.');
                    }
                });
            } else {
                console.error('Modal dengan ID "editDepartmentModal" tidak ditemukan.');
            }
        });
    </script>
@endsection


@section('content')
    <h4>Manajemen Data Departemen</h4>
    <p>Role yang sedang login: <strong>{{ $role }}</strong></p>

    <!-- DataTable with Buttons -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Daftar Departemen</h5>
            {{-- Tombol untuk memicu Offcanvas Tambah Data --}}
            <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#add-new-record">
                <i class="ti ti-plus me-1"></i> Tambah Departemen
            </button>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ID</th>
                        <th>Nama Departemen</th>
                        <th>Jumlah Staf</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($departments as $department)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $department->id }}</td>
                            <td>{{ $department->name }}</td>
                            <td>
                                {{-- Menghitung jumlah relasi employees. Pastikan relasi 'employees' ada di model Department --}}
                                <span
                                    class="badge bg-label-primary">{{ $department->employees_count ?? count($department->employees) }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    {{-- Tombol untuk memicu Modal Edit --}}
                                    <button type="button" class="btn btn-sm btn-icon" data-bs-toggle="modal"
                                        data-bs-target="#editDepartmentModal" data-name="{{ $department->name }}"
                                        data-action="{{ route('departments.update', $department->id) }}">
                                        <i class="text-primary ti ti-pencil"></i>
                                    </button>

                                    {{-- Form untuk Hapus Data --}}
                                    <form action="{{ route('departments.destroy', $department->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus departemen ini?');">
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

    <!-- Offcanvas untuk Tambah Departemen Baru -->
    <div class="offcanvas offcanvas-end" id="add-new-record">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="exampleModalLabel">Tambah Departemen Baru</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            {{-- Form ini akan mengirim data ke fungsi 'store' di DepartmentController --}}
            <form class="add-new-record pt-0 row g-3" id="form-add-new-record" action="{{ route('departments.store') }}"
                method="POST">
                @csrf
                <div class="col-sm-12">
                    <label class="form-label" for="department_name">Nama Departemen</label>
                    <div class="input-group input-group-merge">
                        <span id="department_name2" class="input-group-text"><i class="ti ti-building-hospital"></i></span>
                        <input type="text" id="department_name" class="form-control" name="name"
                            placeholder="Contoh: Kardiologi" required />
                    </div>
                </div>
                <div class="col-sm-12 mt-4">
                    <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">Simpan</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Batal</button>
                </div>
            </form>
        </div>
    </div>
    <!--/ Offcanvas -->

    <!-- Modal untuk Edit Departemen -->
    <div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Departemen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editDepartmentForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col mb-3">
                                <label for="edit_department_name" class="form-label">Nama Departemen</label>
                                <input type="text" id="edit_department_name" name="name" class="form-control"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--/ Modal -->

@endsection
