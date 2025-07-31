@php
    use Illuminate\Support\Facades\Auth;
    use Carbon\Carbon;
    // Mendapatkan role pengguna, default 'Guest' jika tidak login
    $role = Auth::check() ? Auth::user()->role : 'Guest';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Manajemen Janji Temu')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    {{-- Menyimpan data sebagai JSON untuk digunakan oleh JavaScript --}}
    <script>
        const appointmentsData = @json($appointments->keyBy('id'));
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi DataTable
            $('.datatables-basic').DataTable({
                order: [
                    [0, 'desc']
                ] // Urutkan berdasarkan ID terbaru
            });

            // Inisialisasi Select2
            const selectPatient = $('#patient_id');
            const selectDoctor = $('#doctor_id');
            const availableTimeSelect = document.getElementById('available_time');


            if (selectPatient.length) {
                selectPatient.wrap('<div class="position-relative"></div>').select2({
                    placeholder: 'Pilih Pasien',
                    dropdownParent: selectPatient.closest('.offcanvas')
                });
            }
            if (selectDoctor.length) {
                selectDoctor.wrap('<div class="position-relative"></div>').select2({
                    placeholder: 'Pilih Dokter',
                    dropdownParent: selectDoctor.closest('.offcanvas')
                });
            }


            const appointmentFormOffcanvasEl = document.getElementById('appointment-form-offcanvas');
            const bsOffcanvas = new bootstrap.Offcanvas(appointmentFormOffcanvasEl);
            const appointmentForm = document.getElementById('appointment-form');
            const offcanvasTitle = document.getElementById('offcanvas-title');

            // --- FUNGSI UNTUK MENGAMBIL JADWAL DOKTER ---
            const fetchDoctorSchedule = (doctorId) => {
                availableTimeSelect.innerHTML = '<option value="">Memuat jadwal...</option>';
                availableTimeSelect.disabled = true;

                if (!doctorId) {
                    availableTimeSelect.innerHTML = '<option value="">Pilih dokter terlebih dahulu</option>';
                    return;
                }

                // --- PERBAIKAN ---
                // URL disesuaikan agar tidak menggunakan prefix /api, sesuai dengan file routes/web.php Anda
                fetch(`/doctors/${doctorId}/schedule`)
                    .then(response => {
                        if (!response.ok) throw new Error(
                            'Jadwal tidak ditemukan atau terjadi error. Status: ' + response.status);
                        return response.json();
                    })
                    .then(data => {
                        availableTimeSelect.innerHTML = ''; // Hapus opsi lama
                        if (data.schedule && data.schedule.length > 0) {
                            availableTimeSelect.disabled = false;
                            data.schedule.forEach(slot => {
                                const option = document.createElement('option');
                                option.value = slot;
                                option.textContent = slot;
                                availableTimeSelect.appendChild(option);
                            });
                        } else {
                            availableTimeSelect.innerHTML =
                                '<option value="">Dokter tidak memiliki jadwal</option>';
                            availableTimeSelect.disabled = true;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching schedule:', error);
                        availableTimeSelect.innerHTML = '<option value="">Gagal memuat jadwal</option>';
                        availableTimeSelect.disabled = true;
                    });
            };

            // Event listener saat dokter dipilih
            selectDoctor.on('change', function() {
                const doctorId = $(this).val();
                fetchDoctorSchedule(doctorId);
            });


            // Menangani tombol "Tambah Janji Temu"
            document.getElementById('add-appointment-btn').addEventListener('click', function() {
                appointmentForm.reset();
                selectPatient.val(null).trigger('change');
                selectDoctor.val(null).trigger('change');
                availableTimeSelect.innerHTML = '<option value="">Pilih dokter terlebih dahulu</option>';
                availableTimeSelect.disabled = true;
                offcanvasTitle.innerText = 'Buat Janji Temu Baru';
                appointmentForm.action = "{{ route('appointments.store') }}";

                if (appointmentForm.querySelector('input[name="_method"]')) {
                    appointmentForm.querySelector('input[name="_method"]').remove();
                }
                bsOffcanvas.show();
            });

            // Menangani tombol "Edit"
            document.querySelector('.datatables-basic tbody').addEventListener('click', function(event) {
                const editButton = event.target.closest('.edit-btn');
                if (!editButton) return;

                const appointmentId = editButton.dataset.id;
                const appointment = appointmentsData[appointmentId];
                if (!appointment) return;

                appointmentForm.reset();
                offcanvasTitle.innerText = 'Edit Janji Temu';

                const updateUrl = "{{ route('appointments.update', ['appointment' => 'PLACEHOLDER']) }}"
                    .replace('PLACEHOLDER', appointment.id);
                appointmentForm.action = updateUrl;

                if (!appointmentForm.querySelector('input[name="_method"]')) {
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PUT';
                    appointmentForm.prepend(methodInput);
                } else {
                    appointmentForm.querySelector('input[name="_method"]').value = 'PUT';
                }

                // Mengisi form
                selectPatient.val(appointment.patient_id).trigger('change');
                selectDoctor.val(appointment.doctor_id).trigger(
                    'change'); // Ini akan otomatis memicu fetch jadwal

                if (appointment.appointment_date) {
                    const dateForInput = appointment.appointment_date.substring(0, 10);
                    appointmentForm.querySelector('[name="appointment_date"]').value = dateForInput;
                }

                appointmentForm.querySelector('[name="name"]').value = appointment.name || '';
                appointmentForm.querySelector('[name="time"]').value = appointment.time || '';
                appointmentForm.querySelector('[name="notes"]').value = appointment.notes || '';

                // Set jadwal yang dipilih saat edit
                // Ini butuh sedikit jeda agar opsi jadwal selesai dimuat oleh fetch
                setTimeout(() => {
                    if (appointment.available_time) {
                        // Ganti 'available_time' dengan nama kolom yang benar jika berbeda
                        appointmentForm.querySelector('[name="available_time"]').value = appointment
                            .available_time;
                    }
                }, 500); // Jeda 500ms

                bsOffcanvas.show();
            });
        });
    </script>
@endsection


@section('content')
    <h4>Manajemen Janji Temu</h4>
    <p>Role yang sedang login: <strong>{{ $role }}</strong></p>

    <!-- Menampilkan pesan sukses atau error -->
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
            <h5 class="card-title mb-0">Daftar Janji Temu</h5>
            <button id="add-appointment-btn" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Buat Janji Temu
            </button>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Pasien</th>
                        <th>Nama Dokter</th>
                        <th>Nama Janji Temu</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->id }}</td>
                            <td>
                                @if ($appointment->patient)
                                    {{-- Gabungkan semua nama dengan spasi --}}
                                    {{ $appointment->patient->first_name }}
                                    {{ $appointment->patient->middle_name }}
                                    {{ $appointment->patient->last_name }}
                                @else
                                    {{-- Tampilkan ini jika data dokter atau karyawan tidak ada --}}
                                    Dokter Dihapus
                                @endif
                            </td>
                            <td>
                                @if ($appointment->doctor && $appointment->doctor->employee)
                                    {{-- Gabungkan semua nama dengan spasi --}}
                                    {{ $appointment->doctor->employee->first_name }}
                                    {{ $appointment->doctor->employee->middle_name }}
                                    {{ $appointment->doctor->employee->last_name }}
                                @else
                                    {{-- Tampilkan ini jika data dokter atau karyawan tidak ada --}}
                                    Dokter Dihapus
                                @endif
                            </td>

                            <td>{{ $appointment->name }}</td>
                            <td>{{ Carbon::parse($appointment->appointment_date)->isoFormat('D MMMM YYYY') }}</td>
                            <td>{{ $appointment->time ? Carbon::parse($appointment->time)->format('H:i') : '-' }}</td>
                            <td>
                                @if ($appointment->status)
                                    <span class="badge bg-label-success">Selesai</span>
                                @else
                                    <span class="badge bg-label-warning">Tertunda</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-icon edit-btn"
                                        data-id="{{ $appointment->id }}" data-bs-toggle="tooltip"
                                        data-bs-original-title="Edit Janji Temu">
                                        <i class="text-primary ti ti-pencil"></i>
                                    </button>
                                    <form action="{{ route('appointments.toggleStatus', $appointment->id) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-icon" data-bs-toggle="tooltip"
                                            data-bs-original-title="Ubah Status">
                                            <i class="text-warning ti ti-toggle-left"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('appointments.destroy', $appointment->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon" data-bs-toggle="tooltip"
                                            data-bs-original-title="Hapus Janji Temu"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus janji temu ini?');">
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

    <!-- Offcanvas Form untuk Tambah/Edit Janji Temu -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="appointment-form-offcanvas">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvas-title">Form Janji Temu</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form id="appointment-form" method="POST">
                @csrf
                {{-- Method PUT akan ditambahkan oleh JS saat edit --}}

                <div class="mb-3">
                    <label class="form-label" for="patient_id">Pilih Pasien</label>
                    <select id="patient_id" name="patient_id" class="select2 form-select" required>
                        <option value=""></option>
                        @foreach ($patients as $patient)
                            <option value="{{ $patient->id }}">{{ $patient->first_name }} {{ $patient->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="doctor_id">Pilih Dokter</label>
                    <select id="doctor_id" name="doctor_id" class="select2 form-select" required>
                        <option value=""></option>
                        @foreach ($doctors as $doctor)
                            <option value="{{ $doctor->id }}">DR. {{ $doctor->employee->first_name ?? '' }}
                                {{ $doctor->employee->last_name ?? '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="available_time">Jadwal Tersedia</label>
                    <select id="available_time" name="available_time" class="form-select" disabled>
                        <option value="">Pilih dokter terlebih dahulu</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="appointment_date">Tanggal Janji Temu</label>
                    <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="name">Nama Janji Temu</label>
                    <input type="text" class="form-control" id="name" name="name"
                        placeholder="Contoh: Konsultasi Rutin" required />
                </div>

                <div class="mb-3">
                    <label class="form-label" for="time">Waktu (Contoh: 10:30)</label>
                    <input type="time" class="form-control" id="time" name="time">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="notes">Deskripsi</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-primary me-sm-3 me-1">Simpan</button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>
@endsection
