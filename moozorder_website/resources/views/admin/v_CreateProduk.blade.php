@extends('layout.v_template')
@section('page')
@section('content')
<style>
    /* Styling Anda yang sudah ada */
    .bg-gold {
        background: linear-gradient(90deg, #ff8000 60%, #ff8000 100%);
    }
    .form-label {
        color: #333;
        font-weight: 500;
        margin-bottom: 6px;
    }
    .form-control, .form-select {
        border: 1.5px solid #e0e0e0 !important;
        border-radius: 8px !important;
        background: #fafafa;
        font-size: 1rem;
        color: #222;
        padding: 10px 14px;
        box-shadow: none;
        transition: border-color 0.2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #ff8000 !important;
        background: #fffbe6;
        box-shadow: 0 0 0 2px #ffe06633;
    }
    .input-group-text {
        background: #fffbe6;
        border: 1.5px solid #ff8000;
        color: #ff8000;
        font-weight: 600;
        border-radius: 8px 0 0 8px !important;
    }
    .btn-gold {
        background: linear-gradient(90deg, #ff8000 60%, #ff8000 100%);
        color: #fff;
        border: none;
        font-weight: 600;
        letter-spacing: 1px;
    }
    .btn-gold:hover {
        background: #ff8000;
        color: #fff;
    }
    .card-gold-shadow {
        box-shadow: 0 4px 24px 0 rgba(255, 215, 0, 0.10), 0 1.5px 4px 0 rgba(255, 215, 0, 0.10);
    }
    .text-danger-client { /* Class for client-side error message */
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 0.25rem;
    }
</style>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card card-gold-shadow border-0">
                <div class="card-header bg-gold text-white text-center py-4">
                    <h3 class="mb-0" style="font-weight:bold; letter-spacing:1px;">Tambah Produk Baru</h3>
                </div>
                <div class="card-body px-4 pt-4 pb-4">
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

                    <form action="{{ route('produk.store') }}" method="POST" enctype="multipart/form-data" id="createProdukForm" class="needs-validation" novalidate>
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="kategori_produk" class="form-label">Kategori Produk</label>
                                    <select class="form-select form-control-lg @error('kategori_produk') is-invalid @enderror" id="kategori_produk" name="kategori_produk" required>
                                        <option value="" selected disabled>Pilih Kategori Produk</option>
                                        @foreach($daftarKategori as $kategori)
                                            <option value="{{ $kategori->nama_kategori }}" {{ old('kategori_produk') == $kategori->nama_kategori ? 'selected' : '' }}>
                                                {{ $kategori->nama_kategori }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Kategori produk harus dipilih
                                    </div>
                                    @error('kategori_produk')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="nama_produk" class="form-label">Nama Produk</label>
                                    <input type="text" class="form-control form-control-lg @error('nama_produk') is-invalid @enderror" id="nama_produk" name="nama_produk" placeholder="Masukkan nama produk" required value="{{ old('nama_produk') }}">
                                    <div class="invalid-feedback">
                                        Nama produk harus diisi
                                    </div>
                                    @error('nama_produk')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="ukuran_produk" class="form-label">Ukuran Produk</label>
                                    <input type="text" class="form-control form-control-lg @error('ukuran_produk') is-invalid @enderror" id="ukuran_produk" name="ukuran_produk" placeholder="Masukkan ukuran produk (misal: 250ml, 1 porsi, dst)" required value="{{ old('ukuran_produk') }}">
                                    <div class="invalid-feedback">
                                        Ukuran produk harus diisi
                                    </div>
                                    @error('ukuran_produk')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="harga_produk" class="form-label">Harga Produk</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control form-control-lg @error('harga_produk') is-invalid @enderror" id="harga_produk" name="harga_produk" placeholder="Masukkan harga produk" required value="{{ old('harga_produk') }}">
                                    </div>
                                    <div class="invalid-feedback">
                                        Harga produk harus diisi
                                    </div>
                                    @error('harga_produk')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="keterangan_produk" class="form-label">Keterangan Produk</label>
                                    <textarea class="form-control form-control-lg @error('keterangan_produk') is-invalid @enderror" id="keterangan_produk" name="keterangan_produk" rows="6" placeholder="Masukkan keterangan produk" required>{{ old('keterangan_produk') }}</textarea>
                                    <div class="invalid-feedback">
                                        Keterangan produk harus diisi
                                    </div>
                                    @error('keterangan_produk')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="foto_produk" class="form-label">Foto Produk</label>
                                    <input type="file" class="form-control form-control-lg @error('foto_produk') is-invalid @enderror @error('foto_produk.*') is-invalid @enderror" id="foto_produk" name="foto_produk[]" multiple required>
                                    <div class="form-text">Anda bisa memilih lebih dari satu foto (Maks. 2MB per file)</div>
                                    {{-- Pesan error client-side akan ditampilkan di sini --}}
                                    <div id="file-size-error" class="text-danger-client" style="display: none;"></div>
                                    {{-- Tampilkan error spesifik untuk foto produk dari backend --}}
                                    @error('foto_produk')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                    @error('foto_produk.*')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                    <div class="invalid-feedback">
                                        Foto produk harus diisi
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 d-flex justify-content-between">
                                <a href="{{ route('produk.index') }}" class="btn btn-secondary btn-lg px-4">
                                    Kembali
                                </a>
                                <button type="submit" id="simpanProdukBtn" class="btn btn-gold btn-lg px-4">
                                    Simpan Produk
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
{{-- SweetAlert2 CDN (Pastikan ini sudah ada di layout utama Anda, atau tambahkan di sini) --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('foto_produk');
        const submitButton = document.getElementById('simpanProduukBtn'); // Perbaiki typo id
        const fileSizeErrorDiv = document.getElementById('file-size-error');
        const createProdukForm = document.getElementById('createProdukForm');
        const maxFileSize = 2 * 1024 * 1024; // 2MB dalam bytes

        // Fungsi untuk memeriksa validitas form secara keseluruhan dan memperbarui status tombol
        function updateButtonState() {
            let files = fileInput.files;
            let hasLargeFile = false;
            let filesTooLarge = [];

            if (files.length > 0) {
                for (let i = 0; i < files.length; i++) {
                    if (files[i].size > maxFileSize) {
                        hasLargeFile = true;
                        filesTooLarge.push(files[i].name);
                    }
                }
            }

            const formIsValid = createProdukForm.checkValidity(); // Memeriksa validasi HTML5 untuk semua field

            if (hasLargeFile) {
                fileSizeErrorDiv.textContent = 'Ukuran file terlalu besar: ' + filesTooLarge.join(', ') + '. Ukuran maksimal per file adalah 2MB.';
                fileSizeErrorDiv.style.display = 'block';
                submitButton.disabled = true; // Nonaktifkan tombol jika ada file besar
            } else {
                fileSizeErrorDiv.style.display = 'none';
                fileSizeErrorDiv.textContent = '';
                // Aktifkan tombol hanya jika form valid dan tidak ada file yang terlalu besar
                // Dan jika input file diperlukan, pastikan ada file yang dipilih
                submitButton.disabled = !formIsValid || (fileInput.hasAttribute('required') && files.length === 0);
            }
        }

        // Event listener untuk perubahan input file
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                updateButtonState(); // Perbarui status tombol

                // Tampilkan SweetAlert jika ada file yang melebihi batas
                let files = fileInput.files;
                let filesExceedingLimit = [];
                if (files.length > 0) {
                    for (let i = 0; i < files.length; i++) {
                        if (files[i].size > maxFileSize) {
                            filesExceedingLimit.push(files[i].name);
                        }
                    }
                }

                if (filesExceedingLimit.length > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Gagal!',
                        html: `Beberapa gambar melebihi batas ukuran 2MB:<br><strong>${filesExceedingLimit.join('<br>')}</strong>`,
                        confirmButtonText: 'Oke'
                    });
                }
            });
        }

        // Event listener untuk perubahan input form lainnya (selain file input)
        // Ini memastikan tombol aktif/nonaktif saat field lain diisi/dikosongkan
        if (createProdukForm) {
            createProdukForm.addEventListener('input', updateButtonState); // Gunakan 'input' event untuk semua elemen form
            createProdukForm.addEventListener('submit', function (event) {
                // Mencegah submit jika validasi HTML5 gagal atau tombol dinonaktifkan oleh script
                if (!createProdukForm.checkValidity() || submitButton.disabled) {
                    event.preventDefault();
                    event.stopPropagation();

                    // Tambahkan kelas 'was-validated' agar Bootstrap menampilkan feedback validasi
                    createProdukForm.classList.add('was-validated');

                    // Tampilkan SweetAlert spesifik jika form tidak valid karena file besar
                    if (submitButton.disabled && fileSizeErrorDiv.style.display === 'block') {
                        let files = fileInput.files;
                        let filesExceedingLimit = [];
                        if (files.length > 0) {
                            for (let i = 0; i < files.length; i++) {
                                if (files[i].size > maxFileSize) {
                                    filesExceedingLimit.push(files[i].name);
                                }
                            }
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Input Tidak Valid!',
                            html: `Mohon periksa kembali input Anda. Beberapa gambar melebihi batas ukuran 2MB:<br><strong>${filesExceedingLimit.join('<br>')}</strong>`,
                            confirmButtonText: 'Oke'
                        });
                    } else if (!createProdukForm.checkValidity()) {
                        // Jika form tidak valid karena alasan lain (field wajib kosong, dll.)
                        Swal.fire({
                            icon: 'warning',
                            title: 'Form Belum Lengkap!',
                            text: 'Mohon lengkapi semua kolom yang wajib diisi.',
                            confirmButtonText: 'Oke'
                        });
                    }
                } else {
                    // Jika form valid dan tombol tidak dinonaktifkan oleh script, biarkan submit berjalan
                    createProdukForm.classList.add('was-validated');
                }
            }, false);
        }

        // Panggil fungsi ini saat halaman pertama kali dimuat
        updateButtonState();
    });
</script>
@endpush
@endsection
