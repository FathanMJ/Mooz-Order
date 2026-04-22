@extends('layout.v_template')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="text-primary">Detail Produk</h6>
                </div>
                <div class="card-body px-4 pt-4">
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Carousel Foto Produk -->
                            <div id="carouselFotoProduk" class="carousel slide mb-4" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    @if($produk->foto_produk)
                                        @php
                                            $daftarFoto = json_decode($produk->foto_produk, true);
                                        @endphp
                                        @if($daftarFoto)
                                            @foreach($daftarFoto as $index => $foto)
                                                <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                                    <img src="data:image/{{ $foto['tipe'] }};base64,{{ $foto['foto_base64'] }}"
                                                         class="d-block w-100 rounded"
                                                         style="height: 400px; object-fit: contain;"
                                                         alt="Foto {{ $produk->nama_produk }}">
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="carousel-item active">
                                                <img src="{{ asset('img/no-image.jpg') }}"
                                                     class="d-block w-100 rounded"
                                                     style="height: 400px; object-fit: contain;"
                                                     alt="No Image">
                                            </div>
                                        @endif
                                    @else
                                        <div class="carousel-item active">
                                            <img src="{{ asset('img/no-image.jpg') }}"
                                                 class="d-block w-100 rounded"
                                                 style="height: 400px; object-fit: contain;"
                                                 alt="No Image">
                                        </div>
                                    @endif
                                </div>
                                @if($produk->foto_produk && $daftarFoto && count($daftarFoto) > 1)
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselFotoProduk" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselFotoProduk" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                @endif
                            </div>

                            <!-- Thumbnail Foto -->
                            @if($produk->foto_produk && $daftarFoto && count($daftarFoto) > 1)
                                <div class="row g-2 mb-4">
                                    @foreach($daftarFoto as $index => $foto)
                                        <div class="col-3">
                                            <img src="data:image/{{ $foto['tipe'] }};base64,{{ $foto['foto_base64'] }}"
                                                 class="img-thumbnail cursor-pointer"
                                                 style="height: 80px; object-fit: cover;"
                                                 onclick="$('#carouselFotoProduk').carousel({{ $index }})"
                                                 alt="Thumbnail {{ $produk->nama_produk }}">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <div class="product-details">
                                <h2 class="mb-3">{{ $produk->nama_produk }}</h2>

                                <div class="mb-3">
                                    <span class="badge bg-primary">{{ $produk->kategori_produk }}</span>
                                    <span class="badge bg-secondary">ID: {{ $produk->id_produk }}</span>
                                </div>

                                <h3 class="text-primary mb-4">
                                    Rp {{ number_format($produk->harga_produk, 0, ',', '.') }}
                                </h3>

                                <div class="mb-3">
                                    <h6 class="text-uppercase">Ukuran:</h6>
                                    <p class="text-muted">{{ $produk->ukuran_produk }}</p>
                                </div>

                                <div class="mb-4">
                                    <h6 class="text-uppercase">Keterangan:</h6>
                                    <p class="text-muted">{{ $produk->keterangan_produk }}</p>
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="{{ route('produk.edit', $produk->id_produk) }}" class="btn btn-warning">
                                        <i class="fas fa-edit me-2"></i>Edit
                                    </a>
                                    <form action="{{ route('produk.destroy', $produk->id_produk) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                            <i class="fas fa-trash me-2"></i>Hapus
                                        </button>
                                    </form>
                                    <a href="{{ route('produk.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Kembali
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>
    // Inisialisasi carousel
    var carousel = new bootstrap.Carousel(document.getElementById('carouselFotoProduk'), {
        interval: false // Nonaktifkan auto-slide
    });
</script>
@endpush
@endsection
