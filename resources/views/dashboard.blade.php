@php
    // Cek role user yang sedang login
    $role = \Illuminate\Support\Facades\Auth::user()->role ?? 'Guest';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Dashboard')

{{-- Style Khusus Halaman --}}
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

{{-- Script Khusus Halaman --}}
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
    <script>
        // Inisialisasi DataTables untuk setiap tabel setelah halaman dimuat
        $(document).ready(function() {
            // Inisialisasi untuk tabel-tabel dengan data dari sisi klien (Blade)
            $('.datatables-basic-appointments').DataTable();
            $('.datatables-basic-invoices').DataTable();
            $('.datatables-basic-opd').DataTable();
        });
    </script>
@endsection


@section('content')
    <h4>Dashboard</h4>
    <p>Selamat datang kembali, Anda login sebagai: <strong>{{ $role }}</strong></p>

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Total Patients</span>
                            <div class="d-flex align-items-end mt-2">
                                {{-- <?php
                                @dd($total_patients);
                                ?> --}}
                                <h3 class="mb-0 me-2">{{ $total_patients ?? 0 }}</h3>
                            </div>
                            <small>All registered patients</small>
                        </div>
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="ti ti-users ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Total Doctors</span>
                            <div class="d-flex align-items-end mt-2">
                                <h3 class="mb-0 me-2">{{ $total_doctors ?? 0 }}</h3>
                            </div>
                            <small>All available doctors</small>
                        </div>
                        <span class="badge bg-label-success rounded p-2">
                            <i class="ti ti-user-plus ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Pending Appointments</span>
                            <div class="d-flex align-items-end mt-2">
                                <h3 class="mb-0 me-2">{{ $pending_appointments ?? 0 }}</h3>
                            </div>
                            <small>Total pending appointments</small>
                        </div>
                        <span class="badge bg-label-warning rounded p-2">
                            <i class="ti ti-clock ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Total Lab Tests</span>
                            <div class="d-flex align-items-end mt-2">
                                <h3 class="mb-0 me-2">{{ $total_tests ?? 0 }}</h3>
                            </div>
                            <small>All available tests</small>
                        </div>
                        <span class="badge bg-label-info rounded p-2">
                            <i class="ti ti-flask ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- --- Tabel Appointment Hari Ini --- --}}
    <div class="card mb-4">
        <h5 class="card-header">Todays Appointment</h5>
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic-appointments table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $index => $appointment)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $appointment->name ?? 'N/A' }}</td>
                            <td>{{ $appointment->patient->name ?? 'Patient Not Found' }}</td>
                            <td>{{ $appointment->doctor->name ?? 'Doctor Not Found' }}</td>
                            <td>{{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') : 'N/A' }}
                            </td>
                            <td>
                                @if ($appointment->status == 0)
                                    <span class="badge bg-label-warning">Pending</span>
                                @elseif($appointment->status == 1)
                                    <span class="badge bg-label-success">Confirmed</span>
                                @else
                                    <span class="badge bg-label-secondary">Unknown</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No appointments found for today.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- --- Tabel Koleksi/Invoice Hari Ini --- --}}
    <div class="card mb-4">
        <h5 class="card-header">Todays Collection (Invoice)</h5>
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic-invoices table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice No</th>
                        <th>Payment Status</th>
                        <th>Sub Total</th>
                        <th>Discount</th>
                        <th>Tax</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $index => $invoice)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $invoice->invoice_no ?? 'N/A' }}</td>
                            <td>
                                @if ($invoice->payment_status == 'paid')
                                    <span class="badge bg-label-success">Paid</span>
                                @elseif($invoice->payment_status == 'pending')
                                    <span class="badge bg-label-warning">Pending</span>
                                @else
                                    <span class="badge bg-label-danger">Unpaid</span>
                                @endif
                            </td>
                            <td>Rp{{ number_format($invoice->sub_total ?? 0, 0, ',', '.') }}</td>
                            <td>Rp{{ number_format($invoice->discount ?? 0, 0, ',', '.') }}</td>
                            <td>Rp{{ number_format($invoice->tax_amount ?? 0, 0, ',', '.') }}</td>
                            <td>Rp{{ number_format($invoice->total_amount ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No invoices found for today.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total Today:</th>
                        <th>Rp{{ number_format($invoiceTotals['sub_total'] ?? 0, 0, ',', '.') }}</th>
                        <th>Rp{{ number_format($invoiceTotals['discount'] ?? 0, 0, ',', '.') }}</th>
                        <th>Rp{{ number_format($invoiceTotals['tax_amount'] ?? 0, 0, ',', '.') }}</th>
                        <th>Rp{{ number_format($invoiceTotals['total_amount'] ?? 0, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- --- Tabel OPD Hari Ini --- --}}
    <div class="card mb-4">
        <h5 class="card-header">Todays OPD</h5>
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic-opd table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Register At</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($opds as $index => $opd)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $opd->patient->name ?? 'Patient Not Found' }}</td>
                            <td>{{ $opd->doctor->name ?? 'Doctor Not Found' }}</td>
                            <td>{{ $opd->created_at ? $opd->created_at->format('d M Y, H:i') : 'N/A' }}</td>
                            <td>
                                @if ($opd->status == 'completed')
                                    <span class="badge bg-label-success">Completed</span>
                                @else
                                    <span class="badge bg-label-info">In Progress</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No OPD records found for today.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
