<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            padding: 0;
        }
        .date-range {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .total {
            text-align: right;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Penjualan</h2>
        <div class="date-range">
            Periode: {{ $start_date ? $start_date->format('d/m/Y') : '-' }} - {{ $end_date ? $end_date->format('d/m/Y') : '-' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Pesanan</th>
                <th>Produk</th>
                <th>Nama Pemesan</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Total Harga</th>
                <th>Waktu Diambil</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_penjualan = 0;
            @endphp
            @foreach($laporan as $l)
            <tr>
                <td>{{ $l->id_pesanan }}</td>
                <td>{{ $l->produk->nama_produk ?? 'Produk Dihapus' }}</td>
                <td>{{ $l->nama_pemesan ?? 'Pemesan Dihapus' }}</td>
                <td>{{ $l->jumlah }}</td>
                <td>Rp {{ number_format($l->harga_satuan, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($l->total_harga, 0, ',', '.') }}</td>
                <td>{{ $l->waktu_diambil }}</td>
            </tr>
            @php
                $total_penjualan += $l->total_harga;
            @endphp
            @endforeach
        </tbody>
    </table>

    <div class="total">
        Total Penjualan: Rp {{ number_format($total_penjualan, 0, ',', '.') }}
    </div>
</body>
</html>
