@php
    use Carbon\Carbon;
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Laporan Penjualan OPD')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('.datatables-basic').DataTable({
                order: [
                    [0, 'desc']
                ],
                dom: '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                buttons: [{
                    extend: 'collection',
                    className: 'btn btn-label-primary dropdown-toggle me-2',
                    text: '<i class="ti ti-file-export me-sm-1"></i> <span class="d-none d-sm-inline-block">Export</span>',
                    buttons: [{
                        extend: 'print',
                        text: '<i class="ti ti-printer me-1" ></i>Cetak'
                    }, {
                        extend: 'csv',
                        text: '<i class="ti ti-file-text me-1" ></i>CSV'
                    }, {
                        extend: 'excel',
                        text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel'
                    }, {
                        extend: 'pdf',
                        text: '<i class="ti ti-file-description me-1"></i>PDF'
                    }]
                }],
            });
            $('div.head-label').html('<h5 class="card-title mb-0">Laporan Penjualan OPD</h5>');
        });
    </script>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Laporan Penjualan OPD</h4>
        <a href="{{ route('invoice-bill.opd.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Buat Faktur OPD Baru
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Particular</th>
                        <th>Doctor Fee</th>
                        <th>OPD Charge</th>
                        <th>User</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($opd_sales as $sale)
                        <tr>
                            <td>#{{ $sale->invoice->invoice_no ?? 'N/A' }}</td>
                            <td>{{ $sale->opd_name }}</td>
                            <td>Rp {{ number_format($sale->doctor_fee, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($sale->opd_charge, 0, ',', '.') }}</td>
                            <td>{{ $sale->invoice->user->name ?? 'N/A' }}</td>
                            <td>{{ $sale->invoice ? Carbon::parse($sale->invoice->created_at)->isoFormat('D MMM YYYY, HH:mm') : 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data penjualan OPD yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
