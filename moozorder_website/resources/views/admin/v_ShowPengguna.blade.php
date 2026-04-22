@extends('layout.v_template')
@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-gold text-white text-center py-3">
                    <h4 class="mb-0">Detail Pengguna</h4>
                </div>
                <div class="card-body px-4 py-4">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nama</dt>
                        <dd class="col-sm-8">{{ $pengguna->nama }}</dd>
                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">{{ $pengguna->email }}</dd>
                        <dt class="col-sm-4">No HP</dt>
                        <dd class="col-sm-8">{{ $pengguna->no_hp }}</dd>
                        <dt class="col-sm-4">Alamat</dt>
                        <dd class="col-sm-8">{{ $pengguna->alamat }}</dd>
                        <dt class="col-sm-4">Role</dt>
                        <dd class="col-sm-8">{{ $pengguna->role }}</dd>
                    </dl>
                    <div class="mt-4 text-end">
                        <a href="{{ route('pengguna.index') }}" class="btn btn-secondary">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
