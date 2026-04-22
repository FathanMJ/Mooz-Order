@extends('layout.v_template')

@section('page', 'Detail Pesanan')
@section('content')

<style>
.detail-pesanan {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.detail-pesanan .card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.detail-pesanan .card-header {
    background: #f8f9fa;
    color: #343a40;
    font-weight: bold;
    font-size: 1.2rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #dee2e6;
}

.detail-pesanan .btn-secondary {
    background-color: #6c757d;
    font-weight: 600;
    border-radius: 6px;
    transition: all 0.3s;
}

.detail-pesanan .btn-secondary:hover {
    background-color: #5a6268;
}

.detail-pesanan .table-responsive {
    background-color: #fff;
    border-top: 1px solid #dee2e6;
}

.detail-pesanan .table {
    min-width: 900px;
    margin-bottom: 0;
    font-size: 0.95rem;
}

.detail-pesanan .table thead th {
    background-color: #f1f3f5;
    font-weight: bold;
    text-align: center;
    padding: 0.75rem;
    border: 1px solid #dee2e6;
}

.detail-pesanan .table tbody td {
    text-align: center;
    padding: 0.75rem;
    border: 1px solid #dee2e6;
    vertical-align: middle;
}

.detail-pesanan .table tbody td:first-child {
    font-weight: 500;
}

.detail-pesanan .table tbody tr:hover {
    background-color: #f8f9fa;
}

.detail-pesanan .table tfoot td {
    background-color: #f1f3f5;
    font-weight: bold;
    font-size: 1rem;
    padding: 1rem;
    border: 1px solid #dee2e6;
    text-align: center;
}

.detail-pesanan .status-row {
    background: #f8f9fa;
    padding: 1rem 1.5rem;
    border-top: 1px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 600;
    border-bottom-left-radius: 12px;
    border-bottom-right-radius: 12px;
}

.detail-pesanan .status-badge {
    background-color: #198754;
    color: #fff;
    padding: 0.5rem 1.2rem;
    border-radius: 20px;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}
</style>

<div class="container-fluid py-4 detail-pesanan">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Pesanan #{{ $pesanan->id }}</h5>
                    <a href="{{ route('admin.pesanan.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Produk</th>
                                    <th>Jumlah</th>
                                    <th>Harga Satuan</th>
                                    <th>Total</th>
                                    <th>ID Pesanan</th>
                                    <th>Pengguna</th>
                                    <th>Tanggal</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $total = 0; @endphp
                                @foreach ($pesanan->details as $index => $d)
                                    @php
                                        $subtotal = $d->jumlah * $d->harga_satuan;
                                        $total += $subtotal;
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="text-start">{{ $d->produk->nama_produk ?? 'Produk Dihapus' }}</td>
                                        <td>{{ $d->jumlah }}</td>
                                        <td class="text-end">Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                        <td>{{ $pesanan->id }}</td>
                                        <td>{{ $pesanan->id_pengguna }}</td>
                                        <td>{{ $pesanan->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-start">{{ $pesanan->catatan ?: 'Tidak ada catatan' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end">Total Pembayaran</td>
                                    <td colspan="5" class="text-center text-success">Rp {{ number_format($total, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="status-row">
                        <span>Status Pesanan:</span>
                        <span class="status-badge">{{ strtoupper($pesanan->status) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
