<?php

namespace App\Http\Controllers;

use App\Models\M_produk;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class C_produk extends Controller
{
    public function index()
    {
        $daftarProduk = M_produk::all();
        return view('admin.v_KelolaProduk', compact('daftarProduk'));
    }

    public function create()
    {
        $daftarKategori = Kategori::orderBy('nama_kategori')->get();
        return view('admin.v_CreateProduk', compact('daftarKategori'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_produk' => 'required',
            'nama_produk' => 'required',
            'ukuran_produk' => 'required',
            'keterangan_produk' => 'required',
            'harga_produk' => 'required|numeric',
            'foto_produk' => 'required|array',
            'foto_produk.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $daftarFoto = [];
        if ($request->hasFile('foto_produk')) {
            foreach ($request->file('foto_produk') as $foto) {
                if ($foto->isValid()) {
                    try {
                        // Generate nama unik untuk file
                        $extension = $foto->getClientOriginalExtension();
                        $namaFile = uniqid() . '_' . time() . '.' . $extension;

                        // Konversi foto ke base64
                        $fotoBase64 = base64_encode(file_get_contents($foto->getRealPath()));
                        $daftarFoto[] = [
                            'nama_file' => $namaFile,
                            'foto_base64' => $fotoBase64,
                            'tipe' => $extension
                        ];
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }

        M_produk::create([
            'id_produk' => M_produk::generateIdProduk(),
            'kategori_produk' => $request->kategori_produk,
            'nama_produk' => $request->nama_produk,
            'ukuran_produk' => $request->ukuran_produk,
            'keterangan_produk' => $request->keterangan_produk,
            'harga_produk' => $request->harga_produk,
            'foto_produk' => json_encode($daftarFoto)
        ]);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan');
    }

    public function show($id_produk)
    {
        $produk = M_produk::where('id_produk', $id_produk)->firstOrFail();
        return view('admin.v_ShowProduk', compact('produk'));
    }

    public function edit($id_produk)
    {
        $produk = M_produk::where('id_produk', $id_produk)->firstOrFail();
        $daftarKategori = Kategori::orderBy('nama_kategori')->get();
        return view('admin.v_EditProduk', compact('produk', 'daftarKategori'));
    }

    public function update(Request $request, $id_produk)
    {
        $request->validate([
            'kategori_produk' => 'required',
            'nama_produk' => 'required',
            'ukuran_produk' => 'required',
            'keterangan_produk' => 'required',
            'harga_produk' => 'required|numeric',
            'foto_produk' => 'nullable|array',
            'foto_produk.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $produk = M_produk::where('id_produk', $id_produk)->firstOrFail();
        $daftarFoto = [];

        if ($request->hasFile('foto_produk')) {
            foreach ($request->file('foto_produk') as $foto) {
                if ($foto->isValid()) {
                    try {
                        // Generate nama unik untuk file
                        $extension = $foto->getClientOriginalExtension();
                        $namaFile = uniqid() . '_' . time() . '.' . $extension;

                        // Konversi foto ke base64
                        $fotoBase64 = base64_encode(file_get_contents($foto->getRealPath()));
                        $daftarFoto[] = [
                            'nama_file' => $namaFile,
                            'foto_base64' => $fotoBase64,
                            'tipe' => $extension
                        ];
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        } else {
            // Jika tidak ada foto baru diupload, gunakan foto lama
            $daftarFoto = json_decode($produk->foto_produk, true) ?? [];
        }

        $produk->update([
            'kategori_produk' => $request->kategori_produk,
            'nama_produk' => $request->nama_produk,
            'ukuran_produk' => $request->ukuran_produk,
            'keterangan_produk' => $request->keterangan_produk,
            'harga_produk' => $request->harga_produk,
            'foto_produk' => json_encode($daftarFoto)
        ]);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil diperbarui');
    }

    public function destroy($id_produk)
    {
        $produk = M_produk::where('id_produk', $id_produk)->firstOrFail();
        $produk->delete();
        return redirect()->route('produk.index')->with('success', 'Produk berhasil dihapus');
    }
}
