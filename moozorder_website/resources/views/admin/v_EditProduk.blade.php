@extends('layout.v_template')

@section('content')
<style>
    body {
        background-color: #f8f9fa;
    }

    .form-label {
        font-weight: 600;
        color: #212529;
    }

    .form-control,
    .form-select {
        background-color: #fff;
        border: 1.5px solid #ced4da;
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #ff8000;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }

    .btn-primary {
        background-color: #ff8000;
        border-color: #ff8000;
        color: #212529;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #ff8000;
        border-color: #ff8000;
        color: #000;
    }

    .btn-secondary {
        background-color: #212529;
        border-color: #ff8000;
        color: #ff8000;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background-color: #343a40;
        color: #ff8000;
    }

    .card {
        border-radius: 0.75rem;
        box-shadow: 0 0.125rem 0.75rem rgba(0, 0, 0, 0.05);
        border: none;
    }

    .card-header {
        background-color: #ff8000;
        border-bottom: none;
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
    }

    .card-header h6 {
        margin: 0;
        font-weight: bold;
        font-size: 1.25rem;
        color: #000;
    }

    .img-thumbnail {
        border: 2px solid #dee2e6;
        transition: transform 0.2s ease-in-out;
    }

    .img-thumbnail:hover {
        transform: scale(1.05);
    }
</style>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h6>Edit Produk</h6>
                </div>
                <div class="card-body">
                    {{-- Tampilkan error validasi dari backend --}}
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Oops!</strong> Ada beberapa masalah dengan input Anda.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('produk.update', $produk->id_produk) }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        @csrf
                        @method('PUT') {{-- Menggunakan metode PUT untuk update --}}

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kategori_produk" class="form-label">Kategori Produk</label>
                                    <select class="form-select" id="kategori_produk" name="kategori_produk" required>
                                        <option value="" disabled>Pilih Kategori Produk</option>
                                        @foreach($daftarKategori as $kategori)
                                            <option value="{{ $kategori->nama_kategori }}"
                                                {{ (old('kategori_produk') ?? $produk->kategori_produk) == $kategori->nama_kategori ? 'selected' : '' }}>
                                                {{ $kategori->nama_kategori }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Kategori produk harus dipilih</div>
                                </div>

                                <div class="mb-3">
                                    <label for="nama_produk" class="form-label">Nama Produk</label>
                                    <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="{{ old('nama_produk') ?? $produk->nama_produk }}" required>
                                    <div class="invalid-feedback">Nama produk harus diisi</div>
                                </div>

                                <div class="mb-3">
                                    <label for="ukuran_produk" class="form-label">Ukuran Produk</label>
                                    <input type="text" class="form-control" id="ukuran_produk" name="ukuran_produk" value="{{ old('ukuran_produk') ?? $produk->ukuran_produk }}" required>
                                    <div class="invalid-feedback">Ukuran produk harus diisi</div>
                                </div>

                                <div class="mb-3">
                                    <label for="harga_produk" class="form-label">Harga Produk</label>
                                    <input type="number" class="form-control" id="harga_produk" name="harga_produk" value="{{ old('harga_produk') ?? $produk->harga_produk }}" required>
                                    <div class="invalid-feedback">Harga produk harus diisi</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="keterangan_produk" class="form-label">Keterangan Produk</label>
                                    <textarea class="form-control" id="keterangan_produk" name="keterangan_produk" rows="5" required>{{ old('keterangan_produk') ?? $produk->keterangan_produk }}</textarea>
                                    <div class="invalid-feedback">Keterangan produk harus diisi</div>
                                </div>

                                @if($produk->foto_produk)
                                    <div class="mb-3">
                                        <label class="form-label">Foto Saat Ini</label>
                                        <div class="row g-2">
                                            @php $daftarFoto = json_decode($produk->foto_produk, true) @endphp
                                            @foreach($daftarFoto as $foto)
                                                <div class="col-3">
                                                    <img src="data:image/{{ $foto['tipe'] }};base64,{{ $foto['foto_base64'] }}" class="img-thumbnail" style="height: 80px; object-fit: cover;" alt="Foto Produk">
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label for="foto_produk" class="form-label">Upload Foto Baru</label>
                                    <input type="file" class="form-control" id="foto_produk" name="foto_produk[]" multiple>
                                    <div class="form-text">Upload foto baru akan menggantikan foto yang lama. **Ukuran maksimal 2MB per foto.**</div>
                                    {{-- Tampilkan error spesifik untuk foto produk --}}
                                    @error('foto_produk.*')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    {{-- Div untuk menampilkan pesan error JavaScript --}}
                                    <div class="invalid-feedback" id="foto-produk-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <a href="{{ route('produk.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    let fileInput = document.getElementById('foto_produk');
                    let feedbackDiv = document.getElementById('foto-produk-feedback');
                    let files = fileInput.files;
                    const maxFileSize = 2 * 1024 * 1024; // 2 MB dalam bytes

                    let filesExceedLimit = false;
                    let fileNamesExceedingLimit = [];

                    // Cek setiap file yang diunggah
                    for (let i = 0; i < files.length; i++) {
                        if (files[i].size > maxFileSize) {
                            filesExceedLimit = true;
                            fileNamesExceedingLimit.push(files[i].name);
                        }
                    }

                    if (filesExceedLimit) {
                        feedbackDiv.textContent = 'Ukuran foto "' + fileNamesExceedingLimit.join(', ') + '" melebihi batas 2MB.';
                        fileInput.classList.add('is-invalid');
                        event.preventDefault();
                        event.stopPropagation();
                    } else {
                        fileInput.classList.remove('is-invalid');
                        feedbackDiv.textContent = ''; // Hapus pesan error jika valid
                    }

                    // Lanjutkan validasi bawaan Bootstrap
                    if (!form.checkValidity() || filesExceedLimit) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);

                // Hapus pesan validasi ketika input file berubah
                document.getElementById('foto_produk').addEventListener('change', function() {
                    let fileInput = document.getElementById('foto_produk');
                    let feedbackDiv = document.getElementById('foto-produk-feedback');
                    fileInput.classList.remove('is-invalid');
                    feedbackDiv.textContent = '';
                });
            });
    })();
</script>
@endpush
@endsection
