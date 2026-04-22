@extends('layout.v_template')

@section('content')
<style>
    .bg-gold {
        background: linear-gradient(135deg, #ff8000, #ff8000);
        color: white;
    }

    .btn-gold {
        background: linear-gradient(135deg, #ff8000, #ff8000);
        border: none;
        color: #fff;
        font-weight: 600;
        transition: background 0.3s ease;
    }

    .btn-gold:hover {
        background: linear-gradient(135deg, #ff8000, #ff8000);
        color: #fff;
    }

    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 6px;
    }

    .form-control,
    .form-select {
        border-radius: 10px;
        padding: 12px 14px;
        font-size: 16px;
        border: 1px solid #ced4da;
        box-shadow: inset 0 1px 1px rgba(0,0,0,0.05);
        transition: all 0.2s ease-in-out;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #ff8000;
        box-shadow: 0 0 0 0.2rem rgba(244, 193, 15, 0.25);
    }

    .form-section {
        margin-bottom: 1.5rem;
    }

    .card {
        border-radius: 12px;
    }

    .btn-secondary {
        background-color: #f8f9fa;
        border: 1px solid #ccc;
        color: #333;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background-color: #e2e6ea;
        border-color: #bbb;
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
                <div class="card-header bg-gold text-center py-3">
                    <h4 class="mb-0">Tambah Pengguna</h4>
                </div>
                <div class="card-body px-4 py-4">
                    <form action="{{ route('pengguna.store') }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        <div class="form-grid">
                            <div class="form-section">
                                <label for="nama" class="form-label">Nama</label>
                                <input type="text" class="form-control" id="nama" name="nama" required>
                                <div class="invalid-feedback">Nama harus diisi</div>
                            </div>

                            <div class="form-section">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Email harus diisi</div>
                            </div>

                            <div class="form-section">
                                <label for="no_hp" class="form-label">No HP</label>
                                <input type="text" class="form-control" id="no_hp" name="no_hp" required>
                                <div class="invalid-feedback">No HP harus diisi</div>
                            </div>

                            <div class="form-section">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="admin">Admin</option>
                                    <option value="user" selected>User</option>
                                </select>
                                <div class="invalid-feedback">Role harus dipilih</div>
                            </div>
                        </div>

                        <div class="form-section">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                            <div class="invalid-feedback">Alamat harus diisi</div>
                        </div>

                        <div class="form-grid">
                            <div class="form-section">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="invalid-feedback">Password harus diisi</div>
                            </div>

                            <div class="form-section">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                <div class="invalid-feedback">Konfirmasi password harus diisi</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('pengguna.index') }}" class="btn btn-secondary px-4">Kembali</a>
                            <button type="submit" class="btn btn-gold px-4">Tambah Pengguna</button>
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
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
    })()
</script>
@endpush
@endsection
