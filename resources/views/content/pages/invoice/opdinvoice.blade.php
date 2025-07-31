@extends('layouts/layoutMaster')

@section('title', 'Buat Faktur OPD')

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
            const selectPatient = $('#patient_id');
            const selectDoctor = $('#doctor_id');

            if (selectPatient.length) {
                selectPatient.wrap('<div class="position-relative"></div>').select2({
                    placeholder: 'Cari dan pilih pasien...',
                    dropdownParent: selectPatient.parent()
                });
            }
            if (selectDoctor.length) {
                selectDoctor.wrap('<div class="position-relative"></div>').select2({
                    placeholder: 'Cari dan pilih dokter...',
                    dropdownParent: selectDoctor.parent()
                });
            }

            // Elemen UI
            const chargeDetailsContainer = document.getElementById('charge-details-container');
            const subTotalEl = document.getElementById('sub-total');
            const taxEl = document.getElementById('tax');
            const totalAmountEl = document.getElementById('total-amount');
            const discountInput = document.getElementById('discount');
            const cashInput = document.getElementById('cash');
            const returnAmountEl = document.getElementById('return-amount');
            const submitButton = document.getElementById('submit-button');

            let currentCharge = 0;
            let currentTaxPercent = 0;
            let currentTotal = 0;

            const calculateTotal = () => {
                const discount = parseFloat(discountInput.value) || 0;
                const cash = parseFloat(cashInput.value) || 0;

                if (currentCharge > 0) {
                    const subTotalAfterDiscount = currentCharge - discount;
                    const taxAmount = subTotalAfterDiscount * currentTaxPercent / 100;
                    currentTotal = subTotalAfterDiscount + taxAmount;

                    subTotalEl.textContent = `Rp ${currentCharge.toLocaleString('id-ID')}`;
                    taxEl.textContent = `Rp ${taxAmount.toLocaleString('id-ID')} (${currentTaxPercent}%)`;
                    totalAmountEl.textContent = `Rp ${currentTotal.toLocaleString('id-ID')}`;

                    if (cash > 0) {
                        returnAmountEl.textContent = `Rp ${(cash - currentTotal).toLocaleString('id-ID')}`;
                    } else {
                        returnAmountEl.textContent = 'Rp 0';
                    }
                    submitButton.disabled = false;
                }
            };

            selectDoctor.on('change', function() {
                const doctorId = $(this).val();
                chargeDetailsContainer.innerHTML = '<p class="text-center">Memuat biaya...</p>';
                submitButton.disabled = true;

                if (!doctorId) {
                    chargeDetailsContainer.innerHTML = '';
                    return;
                }

                // PERBAIKAN: Menggunakan URL API yang sudah disederhanakan dan dipisah
                const apiUrl = `/api/opd-sales/${doctorId}`;

                fetch(apiUrl)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Data biaya tidak ditemukan (Status: ${response.status})`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        currentCharge = data.opd_charge;
                        currentTaxPercent = data.tax_percent;
                        const detailsHtml =
                            `<div class="d-flex justify-content-between mb-2"><span>Biaya Konsultasi (DR. ${data.doctor_name})</span><span class="fw-medium">Rp ${data.opd_charge.toLocaleString('id-ID')}</span></div>`;
                        chargeDetailsContainer.innerHTML = detailsHtml;
                        calculateTotal();
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        chargeDetailsContainer.innerHTML =
                            `<p class="text-center text-danger">${error.message}</p>`;
                    });
            });

            discountInput.addEventListener('input', calculateTotal);
            cashInput.addEventListener('input', calculateTotal);
        });
    </script>
@endsection

@section('content')
    <h4 class="mb-4">Buat Faktur OPD Baru</h4>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card">
        <div class="card-body">
            <form action="{{ route('invoice-bill.opd.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-7">
                        <h5 class="mb-3">Informasi Pasien & Dokter</h5>
                        <div class="mb-3"><label for="patient_id" class="form-label">Pasien</label><select id="patient_id"
                                name="patient_id" class="select2 form-select" required>
                                <option value=""></option>
                                @foreach ($patients as $patient)
                                    <option value="{{ $patient->id }}">{{ $patient->first_name }}
                                        {{ $patient->middle_name }} {{ $patient->last_name }}
                                        (ID: {{ $patient->id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3"><label for="doctor_id" class="form-label">Dokter</label><select id="doctor_id"
                                name="doctor_id" class="select2 form-select" required>
                                <option value=""></option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}">{{ $doctor->employee->first_name ?? '' }}
                                        {{ $doctor->employee->middle_name ?? '' }}
                                        {{ $doctor->employee->last_name ?? '' }}</option>
                                @endforeach
                            </select></div>
                        <hr class="my-4">
                        <h5 class="mb-3">Rincian Biaya</h5>
                        <div id="charge-details-container"></div>
                    </div>
                    <div class="col-md-5">
                        <div class="border rounded p-3">
                            <h5 class="mb-3">Ringkasan Pembayaran</h5>
                            <input type="hidden" name="invoice_no" value="{{ $invoice_no }}">
                            <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span id="sub-total"
                                    class="fw-medium">Rp 0</span></div>
                            <div class="d-flex justify-content-between mb-2"><span>Pajak (HST)</span><span id="tax"
                                    class="fw-medium">Rp 0</span></div>
                            <div class="mb-3"><label for="discount" class="form-label">Diskon (Rp)</label><input
                                    type="number" class="form-control" id="discount" name="discount" placeholder="0"
                                    min="0"></div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <h6 class="mb-0">Total</h6>
                                <h6 id="total-amount" class="mb-0">Rp 0</h6>
                            </div>
                            <div class="mb-3"><label for="payment_type" class="form-label">Metode
                                    Pembayaran</label><select class="form-select" id="payment_type" name="payment_type"
                                    required>
                                    <option value="Cash" selected>Tunai (Cash)</option>
                                    <option value="Card">Kartu Debit/Kredit</option>
                                    <option value="Insurance">Asuransi</option>
                                </select></div>
                            <div class="mb-3"><label for="cash" class="form-label">Uang Tunai Diterima
                                    (Rp)</label><input type="number" class="form-control" id="cash" name="cash"
                                    placeholder="0" min="0"></div>
                            <div class="d-flex justify-content-between mb-3"><span>Kembalian</span><span id="return-amount"
                                    class="fw-medium">Rp 0</span></div>
                            <div class="mb-3"><label for="comment" class="form-label">Komentar (Opsional)</label>
                                <textarea class="form-control" id="comment" name="comment" rows="2"></textarea>
                            </div>
                            <button type="submit" id="submit-button" class="btn btn-primary w-100" disabled>Buat
                                Faktur</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
