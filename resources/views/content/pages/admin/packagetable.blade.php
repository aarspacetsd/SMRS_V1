@php
    use Illuminate\Support\Facades\Auth;
    // Get user role, default to 'Guest' if not logged in
    $role = Auth::check() ? Auth::user()->role : 'Guest';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Package Management')

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
        // Store package and test data as JSON
        const packagesData = @json($packages->keyBy('id'));
        const testsData = @json($tests);

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            $('.datatables-basic').DataTable();

            // Offcanvas and Modal Elements
            const packageFormOffcanvasElement = document.getElementById('package-form-offcanvas');
            const bsOffcanvas = new bootstrap.Offcanvas(packageFormOffcanvasElement);
            const packageForm = document.getElementById('package-form');
            const offcanvasTitle = document.getElementById('offcanvas-title');
            const packageViewModal = new bootstrap.Modal(document.getElementById('package-view-modal'));
            const packageIdInput = document.getElementById('package_id');


            // Get URLs from the form's data attributes
            const storeUrl = packageForm.dataset.storeUrl;
            const updateUrlTemplate = packageForm.dataset.updateUrl; // Using update URL template
            const deleteTestUrl = packageForm.dataset.deleteTestUrl;

            // Initialize Select2
            const select2 = $('.select2');
            if (select2.length) {
                select2.each(function() {
                    var $this = $(this);
                    $this.wrap('<div class="position-relative"></div>');
                    $this.select2({
                        placeholder: 'Select tests',
                        dropdownParent: $this.closest('.offcanvas').length ? $this.closest(
                            '.offcanvas') : $this.parent()
                    });
                });
            }

            // Handle "Add Package" button
            document.getElementById('add-package-btn').addEventListener('click', function() {
                packageForm.reset();
                $('#select2-multiple').val(null).trigger('change');
                $('#package-tests-container').hide();
                offcanvasTitle.innerText = 'Add New Package';
                packageForm.action = storeUrl;
                packageIdInput.value = '';

                // Ensure _method spoofing is removed for adding
                const methodInput = packageForm.querySelector('input[name="_method"]');
                if (methodInput) {
                    methodInput.remove();
                }
                bsOffcanvas.show();
            });

            // Handle "Edit" and "View" buttons in the table
            document.querySelector('.datatables-basic tbody').addEventListener('click', function(event) {
                const editButton = event.target.closest('.edit-btn');
                const viewButton = event.target.closest('.view-btn');
                const packageId = editButton?.dataset.id || viewButton?.dataset.id;

                if (!packageId) return;

                const packageData = packagesData[packageId];
                if (!packageData) {
                    console.error('Package data not found for ID:', packageId);
                    return;
                }

                // If EDIT button is clicked
                if (editButton) {
                    packageForm.reset();
                    offcanvasTitle.innerText = 'Edit Package';

                    // Create the final update URL by replacing the placeholder
                    const finalUpdateUrl = updateUrlTemplate.replace('PLACEHOLDER', packageData.id);
                    packageForm.action = finalUpdateUrl;

                    // Add _method spoofing for PUT request
                    if (!packageForm.querySelector('input[name="_method"]')) {
                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'PUT';
                        packageForm.prepend(methodInput);
                    } else {
                        packageForm.querySelector('input[name="_method"]').value = 'PUT';
                    }

                    // Fill form with data
                    packageForm.querySelector('#name').value = packageData.name || '';
                    packageForm.querySelector('#price').value = packageData.price || '';
                    packageForm.querySelector('#description').value = packageData.description || '';

                    const testsListContainer = document.getElementById('package-tests-list');
                    const testsContainer = document.getElementById('package-tests-container');
                    testsListContainer.innerHTML = '';

                    if (packageData.package_tests && packageData.package_tests.length > 0) {
                        packageData.package_tests.forEach(pt => {
                            const listItem = document.createElement('li');
                            listItem.className =
                                'list-group-item d-flex justify-content-between align-items-center';
                            listItem.innerText = pt.test.name;

                            const deleteBtn = document.createElement('button');
                            deleteBtn.type = 'button';
                            deleteBtn.className = 'btn btn-danger btn-sm delete-package-test-btn';
                            deleteBtn.dataset.id = pt.id;
                            deleteBtn.innerHTML = '<i class="ti ti-x"></i>';

                            listItem.appendChild(deleteBtn);
                            testsListContainer.appendChild(listItem);
                        });
                        testsContainer.style.display = 'block';
                    } else {
                        testsContainer.style.display = 'none';
                    }

                    $('#select2-multiple').val(null).trigger('change');
                    bsOffcanvas.show();
                }

                // If VIEW button is clicked
                if (viewButton) {
                    document.getElementById('view-package-name').innerText = packageData.name || '-';
                    document.getElementById('view-package-price').innerText =
                        `Rp ${new Intl.NumberFormat('id-ID').format(packageData.price)}`;
                    document.getElementById('view-package-description').innerText = packageData
                        .description || '-';

                    const viewTestsList = document.getElementById('view-package-tests-list');
                    viewTestsList.innerHTML = ''; // Clear previous list

                    if (packageData.package_tests && packageData.package_tests.length > 0) {
                        packageData.package_tests.forEach(pt => {
                            const listItem = document.createElement('li');
                            listItem.className = 'list-group-item border-0 ps-0';
                            listItem.innerText = `- ${pt.test.name}`;
                            viewTestsList.appendChild(listItem);
                        });
                    } else {
                        const listItem = document.createElement('li');
                        listItem.className = 'list-group-item border-0 ps-0 fst-italic';
                        listItem.innerText = 'No tests included in this package.';
                        viewTestsList.appendChild(listItem);
                    }
                    packageViewModal.show();
                }
            });

            // Handle deleting a test from a package
            document.getElementById('package-tests-list').addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('.delete-package-test-btn');
                if (deleteBtn) {
                    const packageTestId = deleteBtn.dataset.id;
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This test will be removed from the package!",
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
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = deleteTestUrl;
                            const csrfToken = document.querySelector(
                                'input[name="_token"]').value;
                            form.innerHTML =
                                `<input type="hidden" name="_token" value="${csrfToken}"><input type="hidden" name="id" value="${packageTestId}">`;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                }
            });

            // Handle main package deletion
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "Deleted packages cannot be recovered!",
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
    <h4>Package Data Management</h4>
    <p>Logged in as role: <strong>{{ $role }}</strong></p>

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
            <h5 class="card-title mb-0">Package List</h5>
            <button id="add-package-btn" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Add Package
            </button>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Package Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($packages as $package)
                        <tr>
                            <td>{{ $package->id }}</td>
                            <td>{{ $package->name }}</td>
                            <td>{{ $package->description }}</td>
                            <td>Rp {{ number_format($package->price, 0, ',', '.') }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-icon view-btn"
                                        data-id="{{ $package->id }}" data-bs-toggle="tooltip"
                                        data-bs-original-title="View Details">
                                        <i class="text-info ti ti-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon edit-btn"
                                        data-id="{{ $package->id }}" data-bs-toggle="tooltip"
                                        data-bs-original-title="Edit Package">
                                        <i class="text-primary ti ti-pencil"></i>
                                    </button>
                                    <form action="{{ route('packages.destroy', $package->id) }}" method="POST"
                                        class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon" data-bs-toggle="tooltip"
                                            data-bs-original-title="Delete Package">
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

    <!-- Add/Edit Package Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="package-form-offcanvas">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvas-title">Package Form</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form id="package-form" method="POST" data-store-url="{{ route('packages.store') }}"
                data-update-url="{{ route('packages.update', ['package' => 'PLACEHOLDER']) }}"
                data-delete-test-url="{{ route('packages.test.delete') }}">
                @csrf
                <input type="hidden" name="id" id="package_id">

                <div class="mb-3">
                    <label class="form-label" for="name">Package Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="price">Price</label>
                    <input type="number" class="form-control" id="price" name="price" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                </div>

                <div class="mb-3">
                    <label for="select2-multiple" class="form-label">Select Tests to Add</label>
                    <select id="select2-multiple" class="select2 form-select" name="test_id[]" multiple>
                        @foreach ($tests as $test)
                            <option value="{{ $test->id }}">{{ $test->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3" id="package-tests-container">
                    <label class="form-label">Tests included in the package</label>
                    <ul class="list-group" id="package-tests-list">
                        <!-- List of tests will be injected by JS here -->
                    </ul>
                </div>

                <button type="submit" class="btn btn-primary me-sm-3 me-1">Save</button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            </form>
        </div>
    </div>


    <!-- View Package Details Modal -->
    <div class="modal fade" id="package-view-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Package Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Package Name:</strong>
                            <span id="view-package-name">-</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Price:</strong>
                            <span id="view-package-price">-</span>
                        </li>
                        <li class="list-group-item">
                            <strong class="d-block mb-1">Description:</strong>
                            <p id="view-package-description" class="mb-0">-</p>
                        </li>
                        <li class="list-group-item">
                            <strong class="d-block mb-1">Included Tests:</strong>
                            <ul class="list-group" id="view-package-tests-list">
                                <!-- Test list will be populated by JS -->
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection
