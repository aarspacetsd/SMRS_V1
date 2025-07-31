@extends('layouts/layoutMaster')

@section('title', 'Laporan Semua Faktur')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi DataTable
            $('.datatables-basic').DataTable({
                order: [
                    [7, 'desc']
                ], // PERBAIKAN: Urutkan berdasarkan tanggal (kolom ke-8)
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ entri",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    "paginate": {
                        "previous": "Sebelumnya",
                        "next": "Berikutnya"
                    }
                }
            });

            // Inisialisasi Select2
            $('.select2').select2({
                placeholder: 'Pilih Pengguna'
            });

            // Inisialisasi Flatpickr (Date Picker)
            flatpickr('.flatpickr-date', {
                dateFormat: 'Y-m-d'
            });
        });
    </script>
@endsection

@section('content')
    <h4>Laporan Semua Faktur (All Report)</h4>
    <p>Anda login sebagai: <strong>{{ $role ?? 'Guest' }}</strong></p>

    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title">Filter Laporan</h5>
            <form action="{{ route('invoice-bill.all-invoice.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="user_id" class="form-label">Pilih Pengguna</label>
                        <select name="user_id" id="user_id" class="form-select select2">
                            <option value="">Semua Pengguna</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="from_date" class="form-label">Dari Tanggal</label>
                        <input type="text" class="form-control flatpickr-date" id="from_date" name="from_date"
                            placeholder="YYYY-MM-DD" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="to_date" class="form-label">Sampai Tanggal</label>
                        <input type="text" class="form-control flatpickr-date" id="to_date" name="to_date"
                            placeholder="YYYY-MM-DD" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-search me-1"></i> Cari Laporan
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>No. Faktur</th>
                        <th>Sub Total</th>
                        <th>Diskon</th>
                        <th>Pajak</th>
                        <th>Total</th>
                        <th>Pengguna</th>
                        <th>Role</th> {{-- KOLOM BARU --}}
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_no ?? 'N/A' }}</td>
                            <td>Rp {{ number_format($invoice->sub_total ?? 0, 2, ',', '.') }}</td>
                            <td>Rp {{ number_format($invoice->discount ?? 0, 2, ',', '.') }}</td>
                            <td>Rp {{ number_format($invoice->tax_amount ?? 0, 2, ',', '.') }}</td>
                            <td>Rp {{ number_format($invoice->total_amount ?? 0, 2, ',', '.') }}</td>
                            <td>{{ $invoice->user->name ?? 'N/A' }}</td>
                            {{-- MENAMPILKAN ROLE PENGGUNA --}}
                            <td>
                                <span
                                    class="badge bg-label-info">{{ $invoice->user->getRoleNames()->first() ?? 'N/A' }}</span>
                            </td>
                            <td>{{ $invoice->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data yang ditemukan untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-end fw-bold">Total:</th>
                        <th class="fw-bold">Rp {{ number_format($invoices->sum('sub_total'), 2, ',', '.') }}</th>
                        <th class="fw-bold">Rp {{ number_format($invoices->sum('discount'), 2, ',', '.') }}</th>
                        <th class="fw-bold">Rp {{ number_format($invoices->sum('tax_amount'), 2, ',', '.') }}</th>
                        <th class="fw-bold">Rp {{ number_format($invoices->sum('total_amount'), 2, ',', '.') }}</th>
                        <th colspan="3"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
