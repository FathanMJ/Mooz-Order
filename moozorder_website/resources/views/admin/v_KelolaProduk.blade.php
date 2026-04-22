@extends('layout.v_template')
@section('page')
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
                    <h5 class="mb-0">Daftar Produk</h5>
                    <a href="{{ route('produk.create') }}" class="btn btn-orange btn-sm">
                        Tambah Produk
                    </a>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-3">
                        <table class="table table-hover table-bordered table-striped align-items-center mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center text-secondary text-xs font-weight-bold">No</th>
                                    <th class="text-secondary text-xs font-weight-bold">ID Produk</th>
                                    <th class="text-secondary text-xs font-weight-bold">Kategori</th>
                                    <th class="text-secondary text-xs font-weight-bold">Nama Produk</th>
                                    <th class="text-secondary text-xs font-weight-bold">Ukuran Produk</th>
                                    <th class="text-secondary text-xs font-weight-bold">Harga</th>
                                    <th class="text-center text-secondary text-xs font-weight-bold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($daftarProduk as $key => $produk)
                                <tr>
                                    <td class="text-center align-middle">{{ $key + 1 }}</td>
                                    <td class="align-middle">{{ $produk->id_produk }}</td>
                                    <td class="align-middle">{{ $produk->kategori_produk }}</td>
                                    <td class="align-middle">{{ $produk->nama_produk }}</td>
                                    <td class="align-middle">{{ $produk->ukuran_produk }}</td>
                                    <td class="align-middle">Rp {{ number_format($produk->harga_produk, 0, ',', '.') }}</td>
                                    <td class="text-center align-middle">
                                        <a href="{{ route('produk.show', $produk->id_produk) }}" class="btn btn-info btn-sm me-1">
                                            Detail
                                        </a>
                                        <a href="{{ route('produk.edit', $produk->id_produk) }}" class="btn btn-warning btn-sm me-1">
                                            Edit
                                        </a>
                                        <form action="{{ route('produk.destroy', $produk->id_produk) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                                @if($daftarProduk->isEmpty())
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada produk.</td>
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
