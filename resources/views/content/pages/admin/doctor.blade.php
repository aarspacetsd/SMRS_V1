@php
    use Illuminate\Support\Facades\Auth;
    // Mendapatkan role pengguna, default 'Guest' jika tidak login
    $role = Auth::check() ? Auth::user()->role : 'Guest';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Manajemen Dokter')

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
    {{-- Menyimpan data dokter dan karyawan sebagai JSON untuk digunakan oleh JavaScript --}}
    <script>
        const doctorsData = @json($doctors->keyBy('id'));
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi DataTables
            $('.datatables-basic').DataTable();

            const doctorFormOffcanvas = document.getElementById('doctor-form-offcanvas');
            const bsOffcanvas = new bootstrap.Offcanvas(doctorFormOffcanvas);
            const doctorForm = document.getElementById('doctor-form');
            const offcanvasTitle = document.getElementById('offcanvas-title');
            const viewDoctorModal = new bootstrap.Modal(document.getElementById('viewDoctorModal'));

            // Menangani tombol "Tambah Dokter Baru"
            document.getElementById('add-doctor-btn').addEventListener('click', function() {
                doctorForm.reset();
                offcanvasTitle.innerText = 'Tambah Dokter Baru';
                doctorForm.action = "{{ route('doctors.store') }}";
                if (doctorForm.querySelector('input[name="_method"]')) {
                    doctorForm.querySelector('input[name="_method"]').remove();
                }
                bsOffcanvas.show();
            });

            // Menangani tombol "Edit" dan "Lihat"
            document.querySelector('.datatables-basic tbody').addEventListener('click', function(event) {
                const viewBtn = event.target.closest('.view-btn');
                const editBtn = event.target.closest('.edit-btn');
                const doctorId = viewBtn?.dataset.id || editBtn?.dataset.id;

                if (!doctorId) return;

                const doctor = doctorsData[doctorId];
                if (!doctor) return;

                // Jika tombol LIHAT yang diklik
                if (viewBtn) {
                    const employee = doctor.employee || {};
                    const department = employee.department || {};

                    document.getElementById('view-name').innerText =
                        `${employee.first_name || ''} ${employee.last_name || ''}`;
                    document.getElementById('view-phone').innerText = employee.phone || '-';
                    document.getElementById('view-department').innerText = department.name || 'N/A';
                    document.getElementById('view-fee').innerText =
                        `Rp ${new Intl.NumberFormat('id-ID').format(doctor.fee || 0)}`;
                    document.getElementById('view-opd-charge').innerText =
                        `Rp ${new Intl.NumberFormat('id-ID').format(doctor.opd_charge || 0)}`;
                    document.getElementById('view-status').innerHTML = employee.status == 1 ?
                        '<span class="badge bg-label-success">Aktif</span>' :
                        '<span class="badge bg-label-danger">Tidak Aktif</span>';
                    viewDoctorModal.show();
                }

                // Jika tombol EDIT yang diklik
                if (editBtn) {
                    doctorForm.reset();
                    offcanvasTitle.innerText = 'Edit Dokter';

                    const updateUrl = "{{ route('doctors.update', ['doctor' => 'PLACEHOLDER']) }}".replace(
                        'PLACEHOLDER', doctor.id);
                    doctorForm.action = updateUrl;

                    if (!doctorForm.querySelector('input[name="_method"]')) {
                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'PUT';
                        doctorForm.prepend(methodInput);
                    } else {
                        doctorForm.querySelector('input[name="_method"]').value = 'PUT';
                    }

                    doctorForm.querySelector('[name="employee_id"]').value = doctor.employee_id || '';
                    doctorForm.querySelector('[name="fee"]').value = doctor.fee || '';
                    doctorForm.querySelector('[name="opd_charge"]').value = doctor.opd_charge || '';
                    doctorForm.querySelector('[name="with_tax"]').checked =
                        false; // Anda bisa sesuaikan ini jika ada data `with_tax`

                    bsOffcanvas.show();
                }
            });
        });
    </script>
@endsection


@section('content')
    <h4>Manajemen Data Dokter</h4>
    <p>Role yang sedang login: <strong>{{ $role }}</strong></p>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Daftar Dokter</h5>
            <button class="btn btn-primary" id="add-doctor-btn">
                <i class="ti ti-plus me-1"></i> Tambah Dokter Baru
            </button>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Dokter</th>
                        <th>Telepon</th>
                        <th>Departemen</th>
                        <th>Biaya OPD</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($doctors as $doctor)
                        <tr>
                            <td>{{ $doctor->id }}</td>
                            <td>
                                {{ $doctor->employee->first_name ?? '' }}
                                {{ $doctor->employee->middle_name ?? '' }}
                                {{ $doctor->employee->last_name ?? 'Data Karyawan Tdk Ditemukan' }}
                                {{-- @if ($doctor->id == 2)
                                    @php
                                        // Lebih baik dd seluruh objek untuk melihat semua datanya
                                        dd(
                                            $doctor->employee->first_name,
                                            $doctor->employee->middle_name,
                                            $doctor->employee->last_name,
                                        );
                                    @endphp
                                @endif --}}
                            </td>
                            <td>
                                {{ $doctor->employee->phone ?? '-' }}
                            </td>
                            <td>
                                {{ $doctor->employee && $doctor->employee->department ? $doctor->employee->department->name : 'Belum Ada Departemen' }}
                            </td>
                            <td>Rp {{ number_format($doctor->fee, 0, ',', '.') }}</td>
                            <td>
                                @if ($doctor->employee && $doctor->employee->status == 1)
                                    <span class="badge bg-label-success">Aktif</span>
                                @else
                                    <span class="badge bg-label-danger">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    {{-- Tombol Lihat Detail sekarang memicu Modal --}}
                                    <button type="button" class="btn btn-sm btn-icon view-btn"
                                        data-id="{{ $doctor->id }}" data-bs-toggle="tooltip"
                                        data-bs-original-title="Lihat Detail">
                                        <i class="text-info ti ti-eye"></i>
                                    </button>
                                    {{-- Tombol Edit sekarang memicu Offcanvas --}}
                                    <button type="button" class="btn btn-sm btn-icon edit-btn"
                                        data-id="{{ $doctor->id }}" data-bs-toggle="tooltip"
                                        data-bs-original-title="Edit Dokter">
                                        <i class="text-primary ti ti-pencil"></i>
                                    </button>
                                    {{-- Tombol Hapus --}}
                                    <form action="{{ route('doctors.destroy', $doctor->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon" data-bs-toggle="tooltip"
                                            data-bs-original-title="Hapus Dokter"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus dokter ini?');">
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

    <!-- Offcanvas untuk Tambah/Edit Dokter -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="doctor-form-offcanvas">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvas-title">Form Dokter</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form id="doctor-form" method="POST">
                @csrf
                {{-- Method PUT akan ditambahkan oleh JS saat edit --}}

                <div class="col-sm-12 mb-3">
                    <label class="form-label" for="employee_id">Pilih Karyawan (Dokter)</label>
                    <select id="employee_id" name="employee_id" class="form-select" required>
                        <option value="" selected disabled>Pilih Nama Dokter</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-12 mb-3">
                    <label class="form-label" for="fee">Biaya Konsultasi (Fee)</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" id="fee" name="fee" class="form-control"
                            placeholder="Contoh: 150000" required />
                    </div>
                </div>

                <div class="col-sm-12 mb-3">
                    <label class="form-label" for="opd_charge">Biaya OPD</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" id="opd_charge" name="opd_charge" class="form-control"
                            placeholder="Contoh: 50000" required />
                    </div>
                </div>

                <div class="col-sm-12 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="with_tax" id="with_tax" value="1">
                        <label class="form-check-label" for="with_tax">
                            Hitung dengan Pajak
                        </label>
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

    <!-- Modal untuk Lihat Detail Dokter -->
    <div class="modal fade" id="viewDoctorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Dokter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between"><strong>Nama:</strong> <span
                                id="view-name">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Departemen:</strong> <span
                                id="view-department">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Telepon:</strong> <span
                                id="view-phone">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Biaya Konsultasi:</strong> <span
                                id="view-fee">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Biaya OPD:</strong> <span
                                id="view-opd-charge">-</span></li>
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
    <!--/ Modal -->

@endsection
