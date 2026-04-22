@extends('layout.v_template')

@section('page')
{{-- Bagian 'page' --}}
@endsection

@section('content')
{{-- Bagian 'content' --}}
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 shadow">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Pesanan</h5>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-3">
                        <table class="table table-hover table-bordered table-striped align-items-center mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center text-secondary text-xs font-weight-bold">No</th>
                                    <th class="text-secondary text-xs font-weight-bold">ID Pesanan</th>
                                    <th class="text-secondary text-xs font-weight-bold">Pengguna</th>
                                    <th class="text-secondary text-xs font-weight-bold">Produk</th>
                                    <th class="text-secondary text-xs font-weight-bold">Status</th>
                                    <th class="text-secondary text-xs font-weight-bold">Ubah Status</th>
                                    <th class="text-center text-secondary text-xs font-weight-bold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pesanan as $key => $p)
                                <tr>
                                    <td class="text-center align-middle">{{ $key + 1 }}</td>
                                    <td class="align-middle">{{ $p->id ?? 'N/A' }}</td>
                                    {{-- Error handling untuk relasi pengguna --}}
                                    <td class="align-middle">
                                        @if(isset($p->pengguna) && $p->pengguna)
                                            {{ $p->pengguna->nama ?? 'Nama tidak tersedia' }}
                                        @else
                                            Pengguna Dihapus
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if(isset($p->details) && $p->details->count() > 0)
                                            <ul class="mb-0 ps-3">
                                                @foreach ($p->details as $d)
                                                <li>
                                                    @if(isset($d->produk) && $d->produk)
                                                        {{ $d->produk->nama_produk ?? 'Nama produk tidak tersedia' }}
                                                    @else
                                                        Produk Dihapus
                                                    @endif
                                                    x {{ $d->jumlah ?? 0 }}
                                                </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-muted">Tidak ada detail produk</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge
                                            @switch($p->status ?? '')
                                                @case('dalam antrian') bg-warning @break
                                                @case('proses pembuatan') bg-info @break
                                                @case('siap diambil') bg-primary @break
                                                @case('sudah diambil') bg-success @break
                                                {{-- @case('selesai') bg-success @break --}}
                                                @default bg-secondary
                                            @endswitch
                                        ">
                                            {{ $p->status ?? 'Status tidak tersedia' }}
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        {{-- Form untuk update status dengan error handling --}}
                                        @if(isset($p->id))
                                            <form method="POST" action="{{ route('admin.pesanan.updateStatus', $p->id) }}" class="status-form">
                                                @csrf
                                                @method('PUT')
                                                <select name="status" class="form-select form-select-sm" onchange="submitForm(this)">
                                                    <option value="dalam antrian" {{ ($p->status ?? '') === 'dalam antrian' ? 'selected' : '' }}>Dalam Antrian</option>
                                                    <option value="proses pembuatan" {{ ($p->status ?? '') === 'proses pembuatan' ? 'selected' : '' }}>Proses Pembuatan</option>
                                                    <option value="siap diambil" {{ ($p->status ?? '') === 'siap diambil' ? 'selected' : '' }}>Siap Diambil</option>
                                                    <option value="sudah diambil" {{ ($p->status ?? '') === 'sudah diambil' ? 'selected' : '' }}>Sudah Diambil</option>
                                                    {{-- <option value="selesai" {{ ($p->status ?? '') === 'selesai' ? 'selected' : '' }}>Selesai</option> --}}
                                                </select>
                                            </form>
                                        @else
                                            <span class="text-muted">ID tidak tersedia</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @if(isset($p->id))
                                            <a href="{{ route('admin.pesanan.detail', $p->id) }}" class="btn btn-info btn-sm">Detail</a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada pesanan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- @if(config('app.debug'))
<div class="container-fluid">
    <div class="card mt-3">
        <div class="card-header">
            <h6>Debug Info</h6>
        </div>
        <div class="card-body">
            <p><strong>Total Pesanan:</strong> {{ isset($pesanan) ? $pesanan->count() : 'Variable $pesanan tidak ada' }}</p>
            <p><strong>Session Messages:</strong></p>
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="mb-3">
                <button onclick="testDebugData()" class="btn btn-warning btn-sm">Test Database</button>
                @if(isset($pesanan) && $pesanan->count() > 0)
                    <button onclick="testUpdate({{ $pesanan->first()->id }})" class="btn btn-info btn-sm">Test Update First Order</button>
                @endif
            </div>

            @if(isset($pesanan) && $pesanan->count() > 0)
                <p><strong>Sample Data:</strong></p>
                <pre>{{ json_encode($pesanan->first()->toArray(), JSON_PRETTY_PRINT) }}</pre>
                <p><strong>All Pesanan Status:</strong></p>
                @foreach($pesanan as $p)
                    <span class="badge bg-info me-1">ID:{{ $p->id }} - Status: {{ $p->status }}</span>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endif --}}

<script>
function submitForm(selectElement) {
    console.log('Form submission triggered');
    console.log('New status:', selectElement.value);
    console.log('Form action:', selectElement.form.action);

    // Konfirmasi sebelum submit
    if (confirm('Apakah Anda yakin ingin mengubah status pesanan ini?')) {
        selectElement.form.submit();
    } else {
        // Reset ke nilai sebelumnya jika dibatalkan
        selectElement.selectedIndex = 0;
    }
}

// Debug JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded');

    const forms = document.querySelectorAll('.status-form');
    console.log('Found', forms.length, 'status forms');

    forms.forEach(function(form, index) {
        console.log('Form', index, ':', form.action);
    });
});
</script>
@endsection
