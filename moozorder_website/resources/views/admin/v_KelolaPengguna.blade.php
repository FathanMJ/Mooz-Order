@extends('layout.v_template')
@section('content')

<style>
     .table-bordered th, .table-bordered td {
        border: 1px solid #dee2e6 !important;
    }

    .btn-orange {
        background-color: #ff8000;
        color: #fff;
        border: none;
        font-weight: 600;
        transition: 0.3s ease;
    }

    .btn-orange:hover {
        background-color: #e67300;
        color: #fff;
    }
</style>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 shadow">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Pengguna</h5>
                    <a href="{{ route('pengguna.create') }}" class="btn btn-orange btn-sm">
                        + Tambah Pengguna
                    </a>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-3">
                        <table class="table table-hover table-bordered table-striped align-items-center mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center text-secondary text-xs font-weight-bold">No</th>
                                    <th class="text-secondary text-xs font-weight-bold">Nama</th>
                                    <th class="text-secondary text-xs font-weight-bold">Email</th>
                                    <th class="text-secondary text-xs font-weight-bold">No HP</th>
                                    <th class="text-secondary text-xs font-weight-bold">Alamat</th>
                                    <th class="text-secondary text-xs font-weight-bold">Role</th>
                                    <th class="text-center text-secondary text-xs font-weight-bold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($daftarPengguna as $key => $pengguna)
                                <tr>
                                    <td class="text-center align-middle">{{ $key + 1 }}</td>
                                    <td class="align-middle">{{ $pengguna->nama }}</td>
                                    <td class="align-middle">{{ $pengguna->email }}</td>
                                    <td class="align-middle">{{ $pengguna->no_hp }}</td>
                                    <td class="align-middle">{{ $pengguna->alamat }}</td>
                                    <td class="align-middle">{{ $pengguna->role }}</td>
                                    <td class="text-center align-middle">
                                        <a href="{{ route('pengguna.show', $pengguna->id) }}" class="btn btn-info btn-sm me-1">Detail</a>
                                        <a href="{{ route('pengguna.edit', $pengguna->id) }}" class="btn btn-warning btn-sm me-1">Edit</a>
                                        <form action="{{ route('pengguna.destroy', $pengguna->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                                @if($daftarPengguna->isEmpty())
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada pengguna.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
