@extends('layouts/layoutMaster')

@section('title', 'Buat Faktur Paket')

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

            const packageSelect = $('#package_id');
            const patientSelect = $('#patient_id');
            const detailsTable = document.getElementById('details-table');
            const discountInput = document.getElementById('discount');

            // Fungsi untuk memformat angka ke format Rupiah
            const formatRupiah = (number) => new Intl.NumberFormat('id-ID').format(number);

            // Fungsi untuk menghitung ulang total berdasarkan diskon
            const recalculateTotal = () => {
                const subTotal = parseFloat(detailsTable.dataset.price) || 0;
                const taxPercent = parseFloat(detailsTable.dataset.taxPercent) || 0;
                const discount = parseFloat(discountInput.value) || 0;

                const subTotalAfterDiscount = subTotal - discount;
                const taxAmount = subTotalAfterDiscount * (taxPercent / 100);
                const totalAmount = subTotalAfterDiscount + taxAmount;

                document.getElementById('sub-total-val').textContent = `Rp ${formatRupiah(subTotal)}`;
                document.getElementById('tax-info').textContent = `Pajak (${taxPercent}%)`;
                document.getElementById('tax-val').textContent = `Rp ${formatRupiah(taxAmount)}`;
                document.getElementById('total-amount-val').textContent = `Rp ${formatRupiah(totalAmount)}`;
            };

            // Event listener saat paket dipilih
            packageSelect.on('change', async function() {
                const packageId = $(this).val();
                detailsTable.querySelector('tbody').innerHTML = ''; // Kosongkan tabel

                if (!packageId) {
                    detailsTable.style.display = 'none';
                    return;
                }

                try {
                    const response = await fetch(`/api/package-details/${packageId}`);
                    if (!response.ok) throw new Error('Paket tidak ditemukan');
                    const data = await response.json();

                    // Tampilkan detail di tabel
                    const row = `
                <tr>
                    <td>1</td>
                    <td>${data.name}</td>
                    <td>Rp ${formatRupiah(data.price)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-icon btn-danger" onclick="window.location.reload()">
                            <i class="ti ti-x"></i>
                        </button>
                    </td>
                </tr>
            `;
                    detailsTable.querySelector('tbody').innerHTML = row;

                    // Simpan data harga dan pajak untuk kalkulasi
                    detailsTable.dataset.price = data.price;
                    detailsTable.dataset.taxPercent = data.tax_percent;
                    detailsTable.style.display = 'table';

                    recalculateTotal();

                } catch (error) {
                    console.error('Error:', error);
                    alert('Gagal memuat detail paket.');
                    detailsTable.style.display = 'none';
                }
            });

            // Event listener saat diskon diubah
            discountInput.addEventListener('input', recalculateTotal);
        });
    </script>
@endsection

@section('content')
    <h4 class="mb-4">Faktur Paket (Package Invoice)</h4>

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

    <form action="{{ route('invoice-bill.package-invoice.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="package_id" class="form-label">Pilih Paket</label>
                        <select id="package_id" name="package_id" class="select2 form-select" data-placeholder="Pilih Paket"
                            required>
                            <option value=""></option>
                            @foreach ($packages as $package)
                                <option value="{{ $package->id }}">{{ $package->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="patient_id" class="form-label">Pilih Pasien</label>
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
                    <div class="col-md-3">
                        <label for="discount" class="form-label">Diskon</label>
                        <input type="number" id="discount" name="discount" class="form-control" placeholder="0"
                            value="0">
                    </div>
                    <div class="col-md-3">
                        <label for="cash" class="form-label">Uang Tunai</label>
                        <input type="number" id="cash" name="cash" class="form-control" placeholder="0">
                    </div>
                </div>

                <div class="table-responsive text-nowrap mt-4">
                    <table id="details-table" class="table" style="display: none;">
                        <thead>
                            <tr>
                                <th>SN.</th>
                                <th>Particular</th>
                                <th>Amount</th>
                                <th>Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Diisi oleh JavaScript --}}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end">Sub Total</td>
                                <td id="sub-total-val">Rp 0</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end" id="tax-info">Pajak (0%)</td>
                                <td id="tax-val">Rp 0</td>
                            </tr>
                            <tr class="bg-light">
                                <td colspan="3" class="text-end fw-bold">Total Amount</td>
                                <td id="total-amount-val" class="fw-bold">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Buat Faktur</button>
                </div>
            </div>
        </div>
    </form>
@endsection
