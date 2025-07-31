@extends('layouts/layoutMaster')

@section('title', 'Buat Service Bill')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi Select2
            $('.select2').each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: $this.data('placeholder'),
                    dropdownParent: $this.parent()
                });
            });

            const serviceSelect = $('#service_id');
            const cartContainer = document.getElementById('cart-container');
            const discountInput = document.getElementById('discount');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Fungsi untuk memformat angka
            const formatRupiah = (number) => new Intl.NumberFormat('id-ID').format(number);

            // Fungsi untuk me-render tabel keranjang dan total
            const renderCart = (data) => {
                let html = '<p>Pilih layanan untuk memulai.</p>';
                if (data.cart && data.cart.length > 0) {
                    html = `
                <div class="table-responsive text-nowrap">
                    <table class="table">
                        <thead>
                            <tr><th>SN.</th><th>Particular</th><th>Amount</th><th>Remove</th></tr>
                        </thead>
                        <tbody>`;
                    data.cart.forEach((item, index) => {
                        html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.name}</td>
                        <td>Rp ${formatRupiah(item.amount)}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-icon btn-danger remove-from-cart-btn" data-id="${item.id}">
                                <i class="ti ti-x"></i>
                            </button>
                        </td>
                    </tr>`;
                    });

                    const discount = parseFloat(discountInput.value) || 0;
                    const subTotalAfterDiscount = data.sub_total - discount;
                    const taxAmount = subTotalAfterDiscount * (data.tax_percent / 100);
                    const totalAmount = subTotalAfterDiscount + taxAmount;

                    html += `
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end">Sub Total</td>
                                <td>Rp ${formatRupiah(data.sub_total)}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end">Pajak (${data.tax_percent}%)</td>
                                <td>Rp ${formatRupiah(taxAmount)}</td>
                            </tr>
                            <tr class="bg-light">
                                <td colspan="3" class="text-end fw-bold">Total Amount</td>
                                <td class="fw-bold">Rp ${formatRupiah(totalAmount)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>`;
                }
                cartContainer.innerHTML = html;
            };

            // Fungsi untuk berinteraksi dengan API keranjang
            const updateCart = async (url, serviceId) => {
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            service_id: serviceId
                        })
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        alert(data.error || 'Terjadi kesalahan');
                        return;
                    }
                    renderCart(data);
                } catch (error) {
                    console.error('Error:', error);
                    alert('Gagal menghubungi server.');
                }
            };

            // Event listener untuk menambah layanan
            serviceSelect.on('change', function() {
                const serviceId = $(this).val();
                if (serviceId) {
                    updateCart("{{ route('api.service-bill.cart.add') }}", serviceId);
                    $(this).val(null).trigger('change');
                }
            });

            // Event listener untuk menghapus layanan (delegasi event)
            cartContainer.addEventListener('click', function(event) {
                const removeButton = event.target.closest('.remove-from-cart-btn');
                if (removeButton) {
                    updateCart("{{ route('api.service-bill.cart.remove') }}", removeButton.dataset.id);
                }
            });

            // Event listener untuk diskon
            discountInput.addEventListener('input', function() {
                fetch("{{ route('api.service-bill.cart.get') }}")
                    .then(res => res.json())
                    .then(data => renderCart(data));
            });
        });
    </script>
@endsection

@section('content')
    <h4 class="mb-4">Service Bill</h4>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('invoice-bill.service-bill.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="service_id" class="form-label">Layanan</label>
                        <select id="service_id" class="select2 form-select" data-placeholder="Pilih Layanan">
                            <option value=""></option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="patient_id" class="form-label">Pasien</label>
                        <select id="patient_id" name="patient_id" class="select2 form-select"
                            data-placeholder="Pilih Pasien" required>
                            <option value=""></option>
                            @foreach ($patients as $patient)
                                <option value="{{ $patient->id }}">{{ $patient->first_name }} {{ $patient->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="payment_type" class="form-label">Metode Pembayaran</label>
                        <select id="payment_type" name="payment_type" class="form-select" required>
                            <option value="cash">Tunai</option>
                            <option value="card">Kartu</option>
                            <option value="cheque">Cek</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="invoice_no" class="form-label">No. Faktur</label>
                        <input type="text" class="form-control" name="invoice_no" value="{{ $invoice_no }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Detail Pembayaran</h5>
            </div>
            <div class="card-body" id="cart-container">
                <p>Pilih layanan untuk memulai.</p>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        <label for="discount" class="form-label">Diskon</label>
                        <input type="number" id="discount" name="discount" class="form-control" placeholder="0"
                            value="0">
                    </div>
                    <div class="col-md-6">
                        <label for="cash" class="form-label">Uang Tunai</label>
                        <input type="number" id="cash" name="cash" class="form-control" placeholder="0">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Buat Tagihan</button>
                </div>
            </div>
        </div>
    </form>
@endsection
