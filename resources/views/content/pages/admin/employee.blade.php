@php
    use Illuminate\Support\Facades\Auth;
    // Mendapatkan role pengguna, default 'Guest' jika tidak login
    $role = Auth::check() ? Auth::user()->role : 'Guest';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Manajemen Karyawan')

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
    {{-- Menyimpan data karyawan sebagai JSON untuk digunakan oleh JavaScript --}}
    <script>
        const employeesData = @json($employees->keyBy('id'));
    </script>
    {{-- Anda bisa memindahkan script ini ke file JS terpisah seperti app-employee-list.js --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi DataTables
            $('.datatables-basic').DataTable();

            const employeeFormOffcanvas = document.getElementById('employee-form-offcanvas');
            const bsOffcanvas = new bootstrap.Offcanvas(employeeFormOffcanvas);
            const employeeForm = document.getElementById('employee-form');
            const offcanvasTitle = document.getElementById('offcanvas-title');
            const employeeViewModal = new bootstrap.Modal(document.getElementById('employee-view-modal'));

            // Menangani tombol "Tambah Karyawan"
            document.getElementById('add-employee-btn').addEventListener('click', function() {
                employeeForm.reset(); // Mengosongkan form
                offcanvasTitle.innerText = 'Tambah Karyawan Baru';
                employeeForm.action = "{{ route('employees.store') }}";
                // Pastikan method spoofing (_method) dihapus jika ada
                if (employeeForm.querySelector('input[name="_method"]')) {
                    employeeForm.querySelector('input[name="_method"]').remove();
                }
                bsOffcanvas.show();
            });

            // Menangani tombol "Edit" dan "Lihat" pada tabel
            document.querySelector('.datatables-basic tbody').addEventListener('click', function(event) {
                const editButton = event.target.closest('.edit-btn');
                const viewButton = event.target.closest('.view-btn');
                const employeeId = editButton?.dataset.id || viewButton?.dataset.id;

                if (!employeeId) return;

                const employee = employeesData[employeeId];
                if (!employee) {
                    console.error('Data karyawan tidak ditemukan untuk ID:', employeeId);
                    return;
                }

                // Jika tombol EDIT yang diklik
                if (editButton) {
                    employeeForm.reset();
                    offcanvasTitle.innerText = 'Edit Karyawan';

                    const updateUrl = "{{ route('employees.update', ['employee' => 'PLACEHOLDER']) }}"
                        .replace('PLACEHOLDER', employee.id);
                    employeeForm.action = updateUrl;

                    // Mengatur method spoofing ke 'PUT' agar sesuai dengan Route::resource
                    if (!employeeForm.querySelector('input[name="_method"]')) {
                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'PUT';
                        employeeForm.prepend(methodInput);
                    } else {
                        employeeForm.querySelector('input[name="_method"]').value = 'PUT';
                    }

                    // Mengisi form dengan data
                    employeeForm.querySelector('[name="first_name"]').value = employee.first_name || '';
                    employeeForm.querySelector('[name="middle_name"]').value = employee.middle_name || '';
                    employeeForm.querySelector('[name="last_name"]').value = employee.last_name || '';
                    employeeForm.querySelector('[name="phone"]').value = employee.phone || '';
                    employeeForm.querySelector('[name="email"]').value = employee.email || '';
                    employeeForm.querySelector('[name="address"]').value = employee.address || '';
                    employeeForm.querySelector('[name="type"]').value = employee.type || '';
                    employeeForm.querySelector('[name="department_id"]').value = employee.department_id ||
                        '';
                    employeeForm.querySelector('[name="status"]').value = employee.status;
                    employeeForm.querySelector('[name="in_time"]').value = employee.in_time || '';
                    employeeForm.querySelector('[name="out_time"]').value = employee.out_time || '';
                    // PERUBAHAN: Mengisi field baru
                    employeeForm.querySelector('[name="speciality"]').value = employee.speciality || '';
                    employeeForm.querySelector('[name="education"]').value = employee.education || '';
                    employeeForm.querySelector('[name="certificate"]').value = employee.certificate || '';
                    employeeForm.querySelector('[name="description"]').value = employee.description || '';


                    // Menangani checkbox hari kerja
                    const workingDays = employee.working_day ? employee.working_day.split(',') : [];
                    employeeForm.querySelectorAll('input[name="working_day[]"]').forEach(checkbox => {
                        checkbox.checked = workingDays.includes(checkbox.value);
                    });

                    bsOffcanvas.show();
                }

                // Jika tombol LIHAT yang diklik
                if (viewButton) {
                    document.getElementById('view-name').innerText =
                        `${employee.first_name || ''} ${employee.middle_name || ''} ${employee.last_name || ''}`
                        .trim();
                    document.getElementById('view-type').innerText = employee.type || '-';
                    document.getElementById('view-department').innerText = employee.department ? employee
                        .department.name : 'N/A';
                    document.getElementById('view-phone').innerText = employee.phone || '-';
                    document.getElementById('view-email').innerText = employee.email || '-';
                    document.getElementById('view-address').innerText = employee.address || '-';
                    document.getElementById('view-working-hours').innerText = (employee.in_time && employee
                        .out_time) ? `${employee.in_time} - ${employee.out_time}` : '-';
                    // PERUBAHAN: Menampilkan field baru di modal
                    document.getElementById('view-speciality').innerText = employee.speciality || '-';
                    document.getElementById('view-education').innerText = employee.education || '-';
                    document.getElementById('view-certificate').innerText = employee.certificate || '-';
                    document.getElementById('view-description').innerText = employee.description || '-';
                    document.getElementById('view-status').innerHTML = employee.status == 1 ?
                        '<span class="badge bg-label-success">Aktif</span>' :
                        '<span class="badge bg-label-danger">Tidak Aktif</span>';
                    employeeViewModal.show();
                }
            });
        });
    </script>
@endsection


@section('content')
    <h4>Manajemen Data Karyawan</h4>
    <p>Role yang sedang login: <strong>{{ $role }}</strong></p>

    <!-- DataTable with Buttons -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Daftar Karyawan</h5>
            {{-- Tombol ini sekarang memicu Offcanvas --}}
            <button id="add-employee-btn" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Tambah Karyawan
            </button>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Departemen</th>
                        <th>Jam Kerja</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employees as $employee)
                        <tr>
                            <td>{{ $employee->id }}</td>
                            <td>{{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }} </td>
                            {{-- @if ($employee->id == 2)
                                @php
                                    // Lebih baik dd seluruh objek untuk melihat semua datanya
                                    dd($employee->first_name, $employee->middle_name, $employee->last_name);
                                @endphp
                            @endif --}}

                            <td>{{ $employee->department->name ?? 'Belum ada departemen' }}</td>
                            <td>
                                @if ($employee->in_time && $employee->out_time)
                                    <span class="badge bg-label-secondary">{{ $employee->in_time }} -
                                        {{ $employee->out_time }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($employee->status == 1)
                                    <span class="badge bg-label-success">Aktif</span>
                                @else
                                    <span class="badge bg-label-danger">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    {{-- Tombol Lihat Detail sekarang memicu Modal --}}
                                    <button type="button" class="btn btn-sm btn-icon view-btn"
                                        data-id="{{ $employee->id }}" data-bs-toggle="tooltip"
                                        data-bs-original-title="Lihat Profil">
                                        <i class="text-info ti ti-eye"></i>
                                    </button>
                                    {{-- Tombol Edit sekarang memicu Offcanvas --}}
                                    <button type="button" class="btn btn-sm btn-icon edit-btn"
                                        data-id="{{ $employee->id }}" data-bs-toggle="tooltip"
                                        data-bs-original-title="Edit Karyawan">
                                        <i class="text-primary ti ti-pencil"></i>
                                    </button>
                                    {{-- Form untuk Hapus Data --}}
                                    <form action="{{ route('employees.destroy', $employee->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon" data-bs-toggle="tooltip"
                                            data-bs-original-title="Hapus Karyawan"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus karyawan ini?');">
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

    <!-- Offcanvas Form untuk Tambah/Edit Karyawan -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="employee-form-offcanvas">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvas-title">Form Karyawan</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form id="employee-form" method="POST">
                @csrf
                {{-- Method PUT akan ditambahkan oleh JS saat edit --}}

                <div class="mb-3">
                    <label class="form-label" for="first_name">Nama Depan</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                {{-- PERUBAHAN: Menambahkan input Nama Tengah --}}
                <div class="mb-3">
                    <label class="form-label" for="middle_name">Nama Tengah</label>
                    <input type="text" class="form-control" id="middle_name" name="middle_name">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="last_name">Nama Belakang</label>
                    <input type="text" class="form-control" id="last_name" name="last_name">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="phone">Telepon</label>
                    <input type="text" class="form-control" id="phone" name="phone">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="address">Alamat</label>
                    <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                </div>
                {{-- PERUBAHAN: Menambahkan field baru --}}
                <div class="mb-3">
                    <label class="form-label" for="speciality">Spesialisasi</label>
                    <input type="text" class="form-control" id="speciality" name="speciality">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="education">Pendidikan</label>
                    <input type="text" class="form-control" id="education" name="education">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="certificate">Sertifikasi</label>
                    <input type="text" class="form-control" id="certificate" name="certificate">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="description">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="type">Tipe</label>
                    <select class="form-select" id="type" name="type">
                        <option value="Doctor">Dokter</option>
                        <option value="Nurse">Perawat</option>
                        <option value="Other">Lainnya</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="department_id">Departemen</label>
                    <select class="form-select" id="department_id" name="department_id" required>
                        <option value="" disabled selected>Pilih Departemen</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Hari Kerja</label>
                    <div>
                        @foreach (['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="working_day[]"
                                    value="{{ $day }}" id="day-{{ $day }}">
                                <label class="form-check-label"
                                    for="day-{{ $day }}">{{ substr($day, 0, 3) }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="in_time" class="form-label">Jam Masuk</label>
                        <input class="form-control" type="time" name="in_time" id="in_time" />
                    </div>
                    <div class="col-md-6">
                        <label for="out_time" class="form-label">Jam Keluar</label>
                        <input class="form-control" type="time" name="out_time" id="out_time" />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="1">Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary me-sm-3 me-1">Simpan</button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>

    <!-- Modal untuk Lihat Profil Karyawan -->
    <div class="modal fade" id="employee-view-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Profil Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between"><strong>Nama:</strong> <span
                                id="view-name">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Tipe:</strong> <span
                                id="view-type">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Departemen:</strong> <span
                                id="view-department">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Telepon:</strong> <span
                                id="view-phone">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Jam Kerja:</strong> <span
                                id="view-working-hours">-</span></li>
                        {{-- PERUBAHAN: Menambahkan field baru di modal --}}
                        <li class="list-group-item d-flex justify-content-between"><strong>Spesialisasi:</strong> <span
                                id="view-speciality">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Pendidikan:</strong> <span
                                id="view-education">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Sertifikasi:</strong> <span
                                id="view-certificate">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Email:</strong> <span
                                id="view-email">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Alamat:</strong> <span
                                id="view-address">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Deskripsi:</strong> <span
                                id="view-description">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Status:</strong> <span
                                id="view-status">-</span></li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

@endsection
