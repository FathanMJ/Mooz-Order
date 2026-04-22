<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\M_laporanpenjualan;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanPenjualanController extends Controller
{
    public function index()
    {
        $laporan = M_laporanpenjualan::with(['produk', 'pesanan.pengguna'])
            ->orderBy('waktu_diambil', 'desc')
            ->get();

        return view('admin.v_LaporanPenjualan', compact('laporan'));
    }

    public function filter(Request $request)
    {
        $start_date = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $end_date = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        $query = M_laporanpenjualan::with(['produk', 'pesanan.pengguna']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('waktu_diambil', [$start_date, $end_date]);
        }

        $laporan = $query->orderBy('waktu_diambil', 'desc')->get();

        return view('admin.v_LaporanPenjualan', compact('laporan', 'start_date', 'end_date'));
    }

    public function exportPDF(Request $request)
    {
        $start_date = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $end_date = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        $query = M_laporanpenjualan::with(['produk', 'pesanan.pengguna']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('waktu_diambil', [$start_date, $end_date]);
        }

        $laporan = $query->orderBy('waktu_diambil', 'desc')->get();

        $pdf = PDF::loadView('admin.v_LaporanPenjualanPDF', compact('laporan', 'start_date', 'end_date'));

        return $pdf->download('laporan-penjualan.pdf');
    }
}
