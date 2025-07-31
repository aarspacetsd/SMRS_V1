@extends('layouts/layoutMaster')

@section('title', 'Laporan Tes')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi DataTable
            $('.datatables-basic').DataTable({
                order: [
                    [3, 'desc']
                ] // Urutkan berdasarkan tanggal terbaru
            });
        });
    </script>
@endsection

@section('content')
    <h4 class="mb-4">Report</h4>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @if (session('info'))
        <div class="alert alert-info" role="alert">{{ session('info') }}</div>
    @endif

    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>S.N.</th>
                        <th>Report No.</th>
                        <th>Patient Name</th>
                        <th>Register Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $index => $report)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>LWC-{{ $report->id }}</td>
                            <td>{{ $report->patient->first_name ?? 'N/A' }} {{ $report->patient->last_name ?? '' }}</td>
                            <td>{{ $report->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @if ($report->status == 1)
                                    <span class="badge bg-label-success">Completed</span>
                                @else
                                    <span class="badge bg-label-warning">Pending</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <a href="{{ route('reports.edit', $report->id) }}"
                                        class="btn btn-sm btn-success me-2">Edit</a>
                                    <a href="{{ route('reports.generatePdf', $report->id) }}" class="btn btn-sm btn-danger"
                                        target="_blank">Generate</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
