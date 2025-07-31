@extends('layouts/layoutMaster')

@section('title', 'Pengaturan Rumah Sakit')

@section('content')
    <h4 class="mb-4">Pengaturan Umum Rumah Sakit</h4>

    {{-- Menampilkan notifikasi sukses atau error --}}
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

    <div class="row">
        <!-- Kolom Kiri: Pengaturan Utama Rumah Sakit -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <h5 class="card-header">Hospital Settings</h5>
                <div class="card-body">
                    @if ($hospital && $hospital->exists)
                        <form action="{{ route('hospital.update', $hospital->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Hospital Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="{{ $hospital->name ?? '' }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="slogan" class="form-label">Hospital Slogan</label>
                                    <input type="text" class="form-control" id="slogan" name="slogan"
                                        value="{{ $hospital->slogan ?? '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="logo" class="form-label">Change Logo</label>
                                <input class="form-control" type="file" id="logo" name="logo">
                                <div class="form-text">Biarkan kosong jika tidak ingin mengubah logo.</div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="{{ $hospital->email ?? '' }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="website" class="form-label">Website</label>
                                    <input type="url" class="form-control" id="website" name="website"
                                        value="{{ $hospital->website ?? '' }}" placeholder="https://contoh.com">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required>{{ $hospital->address ?? '' }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="contact" class="form-label">Contact</label>
                                <input type="text" class="form-control" id="contact" name="contact"
                                    value="{{ $hospital->contact ?? '' }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="about_us" class="form-label">About Us</label>
                                <textarea class="form-control" id="about_us" name="about_us" rows="4">{{ $hospital->about_us ?? '' }}</textarea>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                                <button type="reset" class="btn btn-label-secondary">Default</button>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-warning" role="alert">
                            <h6 class="alert-heading mb-1">Data Tidak Ditemukan!</h6>
                            <span>Data rumah sakit belum ada. Silakan buat data awal melalui database seeder atau
                                langsung di database.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Pengaturan Tambahan -->
        <div class="col-lg-4">
            <!-- PAN Settings -->
            <div class="card mb-4">
                <h5 class="card-header">Pan Settings</h5>
                <div class="card-body">
                    {{-- Anda perlu menambahkan route dan method di controller untuk form ini --}}
                    <form action="#" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="pan_number" class="form-label">Pan Number</label>
                            <input type="text" class="form-control" id="pan_number" name="pan_number"
                                value="{{ $hospital->pan_number ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label for="registration_number" class="form-label">Registration Number</label>
                            <input type="text" class="form-control" id="registration_number"
                                name="registration_number" value="{{ $hospital->registration_number ?? '' }}">
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                            <button type="reset" class="btn btn-label-secondary">Default</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tax Settings -->
            <div class="card mb-4">
                <h5 class="card-header">Tax Settings</h5>
                <div class="card-body">
                    {{-- PERBAIKAN DI SINI: Cukup cek $hospital ada, lalu tampilkan data pajaknya langsung --}}
                    @if ($hospital && $hospital->exists)
                        <form action="{{ route('hospital.update', $hospital->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="tax_type" class="form-label">Tax Type</label>
                                {{-- PERBAIKAN: Akses tax_type langsung dari $hospital --}}
                                <input type="text" class="form-control" id="tax_type" name="tax_type"
                                    value="{{ old('tax_type', $hospital->tax_type ?? '') }}" required>
                                @error('tax_type')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="tax_percent" class="form-label">Percent (%)</label>
                                {{-- PERBAIKAN: Akses tax_percent langsung dari $hospital --}}
                                <input type="number" step="0.01" class="form-control" id="tax_percent"
                                    name="tax_percent" value="{{ old('tax_percent', $hospital->tax_percent ?? '') }}"
                                    required>
                                @error('tax_percent')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                                <button type="reset" class="btn btn-label-secondary">Default</button>
                            </div>
                        </form>
                    @else
                        {{-- Pesan ini akan muncul jika tidak ada data hospital sama sekali --}}
                        <p class="text-muted">Data pengaturan rumah sakit tidak ditemukan. Silakan tambahkan data di
                            Hospital Settings.</p>
                    @endif
                </div>
            </div>

            <!-- Prefix Settings -->
            <div class="card">
                <h5 class="card-header">Prefix Setting</h5>
                <div class="card-body">
                    {{-- Anda perlu menambahkan route dan method di controller untuk form ini --}}
                    <form action="#" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="invoice_prefix" class="form-label">Invoice Prefix</label>
                            <input type="text" class="form-control" id="invoice_prefix" name="invoice_prefix"
                                value="{{-- config('hms.prefix.invoice') --}}">
                        </div>
                        <div class="mb-3">
                            <label for="patient_id_prefix" class="form-label">Patient ID Prefix</label>
                            <input type="text" class="form-control" id="patient_id_prefix" name="patient_id_prefix"
                                value="{{-- config('hms.prefix.patient_id') --}}">
                        </div>
                        <div class="mb-3">
                            <label for="invoice_message" class="form-label">Invoice Message</label>
                            <textarea class="form-control" id="invoice_message" name="invoice_message" rows="2">{{-- config('hms.prefix.invoice_message') --}}</textarea>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                            <button type="reset" class="btn btn-label-secondary">Default</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
