@php
    use Illuminate\Support\Facades\Auth;
    // Get user role, default to 'Guest' if not logged in
    $role = Auth::check() ? Auth::user()->role : 'Guest';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Service Management')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/jquery-validation/jquery.validate.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    <script>
        // Store services data as JSON
        const servicesData = @json($services->keyBy('id'));

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            $('.datatables-basic').DataTable();

            // Offcanvas and Form Elements
            const serviceFormOffcanvasElement = document.getElementById('service-form-offcanvas');
            const bsOffcanvas = new bootstrap.Offcanvas(serviceFormOffcanvasElement);
            const serviceForm = document.getElementById('service-form');
            const offcanvasTitle = document.getElementById('offcanvas-title');

            // Get URLs from the form's data attributes
            const storeUrl = serviceForm.dataset.storeUrl;
            const updateUrlTemplate = serviceForm.dataset.updateUrl;

            // Initialize Select2
            const select2 = $('.select2');
            if (select2.length) {
                select2.each(function() {
                    var $this = $(this);
                    $this.wrap('<div class="position-relative"></div>');
                    $this.select2({
                        placeholder: 'Select department',
                        dropdownParent: $this.closest('.offcanvas')
                    });
                });
            }

            // Handle "Add Service" button
            document.getElementById('add-service-btn').addEventListener('click', function() {
                serviceForm.reset();
                $('#department_id').val(null).trigger('change');
                offcanvasTitle.innerText = 'Add New Service';
                serviceForm.action = storeUrl;

                const methodInput = serviceForm.querySelector('input[name="_method"]');
                if (methodInput) {
                    methodInput.remove();
                }
                bsOffcanvas.show();
            });

            // Handle "Edit" button
            document.querySelector('.datatables-basic tbody').addEventListener('click', function(event) {
                const editButton = event.target.closest('.edit-btn');
                if (!editButton) return;

                const serviceId = editButton.dataset.id;
                const serviceData = servicesData[serviceId];

                if (!serviceData) {
                    console.error('Service data not found for ID:', serviceId);
                    return;
                }

                serviceForm.reset();
                offcanvasTitle.innerText = 'Edit Service';

                // Create the final update URL by replacing the placeholder
                const finalUpdateUrl = updateUrlTemplate.replace('PLACEHOLDER', serviceData.id);
                serviceForm.action = finalUpdateUrl;

                // Add _method spoofing for PUT request
                if (!serviceForm.querySelector('input[name="_method"]')) {
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PUT';
                    serviceForm.prepend(methodInput);
                } else {
                    serviceForm.querySelector('input[name="_method"]').value = 'PUT';
                }

                // Fill form with data
                serviceForm.querySelector('[name="name"]').value = serviceData.name || '';
                serviceForm.querySelector('[name="amount"]').value = serviceData.amount || '';

                // Set department and trigger change for Select2
                $('#department_id').val(serviceData.department_id).trigger('change');

                bsOffcanvas.show();
            });

            // Handle main service deletion
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "Deleted services cannot be recovered!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel',
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
    <h4>Service Data Management</h4>
    <p>Logged in as role: <strong>{{ $role }}</strong></p>

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
            <h5 class="card-title mb-0">Service List</h5>
            <button id="add-service-btn" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Add Service
            </button>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Service Name</th>
                        <th>Department</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($services as $service)
                        <tr>
                            <td>{{ $service->id }}</td>
                            <td>{{ $service->name }}</td>
                            <td>{{ $service->department->name ?? 'N/A' }}</td>
                            <td>Rp {{ number_format($service->amount, 0, ',', '.') }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-icon edit-btn"
                                        data-id="{{ $service->id }}" data-bs-toggle="tooltip"
                                        data-bs-original-title="Edit Service">
                                        <i class="text-primary ti ti-pencil"></i>
                                    </button>
                                    <form action="{{ route('services.destroy', $service->id) }}" method="POST"
                                        class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon" data-bs-toggle="tooltip"
                                            data-bs-original-title="Delete Service">
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

    <!-- Add/Edit Service Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="service-form-offcanvas">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvas-title">Service Form</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form id="service-form" method="POST" data-store-url="{{ route('services.store') }}"
                data-update-url="{{ route('services.update', ['service' => 'PLACEHOLDER']) }}">
                @csrf
                {{-- _method field for 'PUT' will be added by JS on edit --}}

                <div class="mb-3">
                    <label class="form-label" for="name">Service Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="amount">Amount</label>
                    <input type="number" class="form-control" id="amount" name="amount" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="department_id">Department</label>
                    <select class="select2 form-select" id="department_id" name="department_id" required>
                        <option value="" disabled selected>Select Department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="with_tax" value="1" id="with_tax">
                        <label class="form-check-label" for="with_tax">
                            Price includes tax
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary me-sm-3 me-1">Save</button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            </form>
        </div>
    </div>
@endsection
