@php
    use Illuminate\Support\Facades\Auth;
    // Mendapatkan role pengguna, default 'Guest' jika tidak login
    $role = Auth::check() ? Auth::user()->role : 'Guest';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Manajemen Pasien')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/jquery-validation/jquery.validate.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    <script>
        // Simpan data pasien sebagai JSON
        const patientsData = @json($patients->keyBy('id'));

        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi DataTable
            $('.datatables-basic').DataTable();

            // Inisialisasi Select2
            const select2 = $('.select2');
            if (select2.length) {
                select2.each(function() {
                    var $this = $(this);
                    $this.wrap('<div class="position-relative"></div>');
                    $this.select2({
                        dropdownParent: $this.closest('.offcanvas')
                    });
                });
            }

            // Elemen UI
            const patientFormOffcanvasElement = document.getElementById('patient-form-offcanvas');
            const bsOffcanvas = new bootstrap.Offcanvas(patientFormOffcanvasElement);
            const patientForm = document.getElementById('patient-form');
            const offcanvasTitle = document.getElementById('offcanvas-title');
            const patientViewModal = new bootstrap.Modal(document.getElementById('patient-view-modal'));

            // URL dari atribut data-*
            const storeUrl = "{{ route('patients.store') }}";
            const updateUrlTemplate = "{{ route('patients.update', ['patient' => 'PLACEHOLDER']) }}";

            // Menangani tombol "Tambah Pasien"
            document.getElementById('add-patient-btn').addEventListener('click', function() {
                patientForm.reset();
                $('.select2').val(null).trigger('change');
                offcanvasTitle.innerText = 'Tambah Pasien Baru';
                patientForm.action = storeUrl;

                const methodInput = patientForm.querySelector('input[name="_method"]');
                if (methodInput) {
                    methodInput.remove();
                }
                bsOffcanvas.show();
            });

            // Menangani tombol "Edit Data" dan "Lihat Profil"
            document.querySelector('.datatables-basic tbody').addEventListener('click', function(event) {
                const editButton = event.target.closest('.edit-btn');
                const viewButton = event.target.closest('.view-btn'); // Tombol Lihat Profil

                const patientId = editButton?.dataset.id || viewButton?.dataset.id;
                if (!patientId) return;

                const patientData = patientsData[patientId];
                if (!patientData) {
                    console.error('Data pasien tidak ditemukan untuk ID:', patientId);
                    return;
                }

                // Jika tombol "Edit Data" diklik
                if (editButton) {
                    patientForm.reset();
                    offcanvasTitle.innerText = 'Edit Data Pasien';

                    const finalUpdateUrl = updateUrlTemplate.replace('PLACEHOLDER', patientData.id);
                    patientForm.action = finalUpdateUrl;

                    if (!patientForm.querySelector('input[name="_method"]')) {
                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'PUT';
                        patientForm.prepend(methodInput);
                    } else {
                        patientForm.querySelector('input[name="_method"]').value = 'PUT';
                    }

                    // Mengisi semua field di form dengan data
                    patientForm.querySelector('[name="first_name"]').value = patientData.first_name || '';
                    patientForm.querySelector('[name="middle_name"]').value = patientData.middle_name || '';
                    patientForm.querySelector('[name="last_name"]').value = patientData.last_name || '';
                    patientForm.querySelector('[name="email"]').value = patientData.email || '';
                    patientForm.querySelector('[name="age"]').value = patientData.age || '';
                    patientForm.querySelector('[name="phone"]').value = patientData.phone || '';
                    patientForm.querySelector('[name="birth_date"]').value = patientData.birth_date || '';
                    patientForm.querySelector('[name="country"]').value = patientData.country || '';
                    patientForm.querySelector('[name="state"]').value = patientData.state || '';
                    patientForm.querySelector('[name="district"]').value = patientData.district || '';
                    patientForm.querySelector('[name="location"]').value = patientData.location || '';
                    patientForm.querySelector('[name="occupation"]').value = patientData.occupation || '';
                    patientForm.querySelector('[name="description"]').value = patientData.description || '';
                    patientForm.querySelector('[name="relative_name"]').value = patientData.relative_name ||
                        '';
                    patientForm.querySelector('[name="relative_phone"]').value = patientData
                        .relative_phone || '';

                    $('#gender').val(patientData.gender).trigger('change');
                    $('#marital_status').val(patientData.marital_status).trigger('change');
                    $('#blood_group').val(patientData.blood_group).trigger('change');

                    bsOffcanvas.show();
                }

                // Jika tombol "Lihat Profil" diklik
                if (viewButton) {
                    const fullName =
                        `${patientData.first_name || ''} ${patientData.middle_name || ''} ${patientData.last_name || ''}`
                        .trim();
                    document.getElementById('view-full-name').innerText = fullName || '-';
                    document.getElementById('view-email').innerText = patientData.email || '-';
                    document.getElementById('view-phone').innerText = patientData.phone || '-';
                    document.getElementById('view-age').innerText = patientData.age || '-';
                    document.getElementById('view-gender').innerText = patientData.gender || '-';
                    document.getElementById('view-birth-date').innerText = patientData.birth_date || '-';
                    document.getElementById('view-marital-status').innerText = patientData.marital_status ||
                        '-';
                    document.getElementById('view-blood-group').innerText = patientData.blood_group || '-';
                    const fullAddress = [patientData.location, patientData.district, patientData.state,
                        patientData.country
                    ].filter(Boolean).join(', ');
                    document.getElementById('view-full-address').innerText = fullAddress || '-';
                    document.getElementById('view-occupation').innerText = patientData.occupation || '-';
                    document.getElementById('view-relative-name').innerText = patientData.relative_name ||
                        '-';
                    document.getElementById('view-relative-phone').innerText = patientData.relative_phone ||
                        '-';
                    document.getElementById('view-description').innerText = patientData.description || '-';

                    patientViewModal.show();
                }
            });

            // Menangani form hapus
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
                    }).then(function(result) {
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
    <h4>Manajemen Data Pasien</h4>
    <p>Role yang sedang login: <strong>{{ $role }}</strong></p>

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
    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif


    <!-- DataTable with Buttons -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Daftar Pasien</h5>
            <button id="add-patient-btn" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Tambah Pasien
            </button>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($patients as $patient)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $patient->first_name }} {{ $patient->middle_name }} {{ $patient->last_name }}</td>
                            <td>{{ $patient->email ?? 'N/A' }}</td>
                            <td>{{ $patient->phone ?? 'N/A' }}</td>
                            <td>
                                @if ($patient->status)
                                    <span class="badge bg-label-success">Aktif</span>
                                @else
                                    <span class="badge bg-label-danger">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-icon view-btn"
                                        data-id="{{ $patient->id }}" data-bs-toggle="tooltip"
                                        data-bs-original-title="Lihat Profil">
                                        <i class="text-info ti ti-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon edit-btn"
                                        data-id="{{ $patient->id }}" data-bs-toggle="tooltip"
                                        data-bs-original-title="Edit Data">
                                        <i class="text-primary ti ti-pencil"></i>
                                    </button>
                                    <a href="{{ route('patients.edit', $patient->id) }}" class="btn btn-sm btn-icon"
                                        data-bs-toggle="tooltip" data-bs-original-title="Ubah Status">
                                        <i class="text-warning ti ti-toggle-left"></i>
                                    </a>
                                    <form action="{{ route('patients.destroy', $patient->id) }}" method="POST"
                                        class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon" data-bs-toggle="tooltip"
                                            data-bs-original-title="Hapus Pasien">
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

    <!-- Add/Edit Patient Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="patient-form-offcanvas">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvas-title">Form Pasien</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form id="patient-form" method="POST">
                @csrf
                {{-- _method field for 'PUT' will be added by JS on edit --}}

                <div class="mb-3">
                    <label class="form-label" for="first_name">Nama Depan</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="middle_name">Nama Tengah</label>
                    <input type="text" class="form-control" id="middle_name" name="middle_name">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="last_name">Nama Belakang</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="phone">Telepon</label>
                    <input type="text" class="form-control" id="phone" name="phone">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="age">Umur</label>
                        <input type="number" class="form-control" id="age" name="age">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="gender">Jenis Kelamin</label>
                        <select id="gender" name="gender" class="form-select">
                            <option value="" selected>Pilih...</option>
                            <option value="Male">Laki-laki</option>
                            <option value="Female">Perempuan</option>
                            <option value="Other">Lainnya</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="birth_date">Tanggal Lahir</label>
                    <input type="date" class="form-control" id="birth_date" name="birth_date">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="marital_status">Status Perkawinan</label>
                    <select id="marital_status" name="marital_status" class="form-select">
                        <option value="" selected>Pilih...</option>
                        <option value="Single">Belum Menikah</option>
                        <option value="Married">Menikah</option>
                        <option value="Divorced">Bercerai</option>
                        <option value="Widowed">Duda/Janda</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="blood_group">Golongan Darah</label>
                    <select id="blood_group" name="blood_group" class="form-select">
                        <option value="" selected>Pilih...</option>
                        <option>A+</option>
                        <option>A-</option>
                        <option>B+</option>
                        <option>B-</option>
                        <option>AB+</option>
                        <option>AB-</option>
                        <option>O+</option>
                        <option>O-</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="location">Kecamatan</label>
                    <input type="text" class="form-control" id="location" name="location">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="district">Kabupaten/Kota</label>
                    <input type="text" class="form-control" id="district" name="district">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="state">Provinsi</label>
                    <input type="text" class="form-control" id="state" name="state">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="country">Negara</label>
                    <input type="text" class="form-control" id="country" name="country">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="occupation">Pekerjaan</label>
                    <input type="text" class="form-control" id="occupation" name="occupation">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="relative_name">Nama Kerabat</label>
                    <input type="text" class="form-control" id="relative_name" name="relative_name">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="relative_phone">Telepon Kerabat</label>
                    <input type="text" class="form-control" id="relative_phone" name="relative_phone">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="description">Deskripsi Tambahan</label>
                    <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                </div>

                <button type="submit" class="btn btn-primary me-sm-3 me-1">Simpan</button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>

    <!-- View Patient Profile Modal -->
    <div class="modal fade" id="patient-view-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Profil Pasien</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between"><strong>Nama Lengkap:</strong> <span
                                id="view-full-name">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Email:</strong> <span
                                id="view-email">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Telepon:</strong> <span
                                id="view-phone">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Umur:</strong> <span
                                id="view-age">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Jenis Kelamin:</strong> <span
                                id="view-gender">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Tanggal Lahir:</strong> <span
                                id="view-birth-date">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Status Perkawinan:</strong>
                            <span id="view-marital-status">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Golongan Darah:</strong> <span
                                id="view-blood-group">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Wilayah:</strong> <span
                                id="view-full-address">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Pekerjaan:</strong> <span
                                id="view-occupation">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Nama Kerabat:</strong> <span
                                id="view-relative-name">-</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Telepon Kerabat:</strong> <span
                                id="view-relative-phone">-</span></li>
                        <li class="list-group-item">
                            <strong class="d-block mb-1">Deskripsi Tambahan:</strong>
                            <p id="view-description" class="mb-0">-</p>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

@endsection
