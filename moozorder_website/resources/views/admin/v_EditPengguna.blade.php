@extends('layout.v_template')

@section('content')

<style>
    .bg-orange {
        background: linear-gradient(135deg, #ff8000, #e67300);
        color: white;
    }

    .btn-orange {
        background: #ff8000;
        border: none;
        color: #fff;
        font-weight: 600;
        transition: all 0.3s ease-in-out;
    }

    .btn-orange:hover {
        background: #e67300;
        color: #fff;
    }

    .form-label {
        font-weight: 600;
        color: #212529;
        margin-bottom: 6px;
    }

    .form-control,
    .form-select {
        border-radius: 10px;
        padding: 12px 14px;
        font-size: 16px;
        border: 1px solid #ced4da;
        transition: all 0.2s ease-in-out;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #ff8000;
        box-shadow: 0 0 0 0.2rem rgba(255, 128, 0, 0.25);
    }

    .card {
        border-radius: 12px;
        background-color: #ffffff;
    }

    .form-section {
        margin-bottom: 1.5rem;
    }

    .btn-secondary {
        background-color: #f1f1f1;
        border: 1px solid #ccc;
        color: #333;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background-color: #e2e2e2;
    }

    @media (min-width: 768px) {
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow border-0">
                <div class="card-header bg-orange text-center py-3">
                    <h4 class="mb-0">Edit Pengguna</h4>
                </div>
                <div class="card-body px-4 py-4">
                    <form action="{{ route('pengguna.update', $pengguna->id) }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        {{-- Sesuaikan dengan route web.php, method POST --}}
                        {{-- Kalau controller menangani PUT, tambahkan method spoofing --}}
                        @method('POST')

                        <div class="form-grid">
                            <div class="form-section">
                                <label for="nama" class="form-label">Nama</label>
                                <input type="text" class="form-control" id="nama" name="nama" value="{{ old('nama', $pengguna->nama) }}" required>
                                <div class="invalid-feedback">Nama harus diisi</div>
                            </div>

                            <div class="form-section">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $pengguna->email) }}" required>
                                <div class="invalid-feedback">Email harus diisi</div>
                            </div>

                            <div class="form-section">
                                <label for="no_hp" class="form-label">No HP</label>
                                <input type="text" class="form-control" id="no_hp" name="no_hp" value="{{ old('no_hp', $pengguna->no_hp) }}" required>
                                <div class="invalid-feedback">No HP harus diisi</div>
                            </div>

                            <div class="form-section">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="admin" {{ $pengguna->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="user" {{ $pengguna->role == 'user' ? 'selected' : '' }}>User</option>
                                </select>
                                <div class="invalid-feedback">Role harus dipilih</div>
                            </div>
                        </div>

                        <div class="form-section">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" required>{{ old('alamat', $pengguna->alamat) }}</textarea>
                            <div class="invalid-feedback">Alamat harus diisi</div>
                        </div>

                        <div class="form-section">
                            <label for="password" class="form-label">Password (kosongkan jika tidak ingin mengubah)</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password baru (opsional)">
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('pengguna.index') }}" class="btn btn-secondary px-4">Kembali</a>
                            <button type="submit" class="btn btn-orange px-4">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (() => {
        'use strict';
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>
@endpush

@endsection
