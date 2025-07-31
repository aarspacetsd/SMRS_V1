@extends('layouts/layoutMaster')

@section('title', 'Laporan Penjualan Layanan')

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
                    [4, 'desc']
                ], // Urutkan berdasarkan tanggal terbaru
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
                placeholder: 'Pilih Layanan'
            });

            // Inisialisasi Flatpickr (Date Picker)
            flatpickr('.flatpickr-date', {
                dateFormat: 'Y-m-d'
            });
        });
    </script>
@endsection

@section('content')
    <h4>Laporan Penjualan Layanan</h4>

    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title">Filter Laporan</h5>
            <form action="{{ route('reports.service.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="service_id" class="form-label">Pilih Layanan</label>
                        <select name="service_id" id="service_id" class="form-select select2">
                            <option value="">Semua Layanan</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}"
                                    {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }}
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
                        <th>Nama Layanan</th>
                        <th>Biaya</th>
                        <th>Pengguna</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($service_sales as $sale)
                        <tr>
                            <td>{{ $sale->invoice->invoice_no ?? 'N/A' }}</td>
                            <td>{{ $sale->service->name ?? 'N/A' }}</td>
                            <td>Rp {{ number_format($sale->amount ?? 0, 0, ',', '.') }}</td>
                            <td>{{ $sale->invoice->user->name ?? 'N/A' }}</td>
                            <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data yang ditemukan untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" class="text-end fw-bold">Total:</th>
                        <th colspan="3" class="fw-bold">Rp
                            {{ number_format($service_sales->sum('amount'), 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
