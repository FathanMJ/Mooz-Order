<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\M_produk;
use Illuminate\Http\Request;
use App\Http\Requests\ProdukRequest;

class ProdukController extends Controller
{
    public function index()
    {
        $produk = M_produk::all();
        return response()->json([
            'status' => 'success',
            'data' => $produk
        ]);
    }

    public function show($id_produk)
    {
        $produk = M_produk::where('id_produk', $id_produk)->first();

        if (!$produk) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $produk
        ]);
    }

    public function store(ProdukRequest $request)
    {
        $daftarFoto = [];
        if ($request->hasFile('foto_produk')) {
            foreach ($request->file('foto_produk') as $foto) {
                if ($foto->isValid()) {
                    try {
                        $extension = $foto->getClientOriginalExtension();
                        $namaFile = uniqid() . '_' . time() . '.' . $extension;
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

        $produk = M_produk::create([
            'id_produk' => M_produk::generateIdProduk(),
            'kategori_produk' => $request->kategori_produk,
            'nama_produk' => $request->nama_produk,
            'ukuran_produk' => $request->ukuran_produk,
            'keterangan_produk' => $request->keterangan_produk,
            'harga_produk' => $request->harga_produk,
            'foto_produk' => json_encode($daftarFoto)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil ditambahkan',
            'data' => $produk
        ], 201);
    }

    public function update(ProdukRequest $request, $id_produk)
    {
        $produk = M_produk::where('id_produk', $id_produk)->first();

        if (!$produk) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        $daftarFoto = [];
        if ($request->hasFile('foto_produk')) {
            foreach ($request->file('foto_produk') as $foto) {
                if ($foto->isValid()) {
                    try {
                        $extension = $foto->getClientOriginalExtension();
                        $namaFile = uniqid() . '_' . time() . '.' . $extension;
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

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil diperbarui',
            'data' => $produk
        ]);
    }

    public function destroy($id_produk)
    {
        $produk = M_produk::where('id_produk', $id_produk)->first();

        if (!$produk) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        $produk->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil dihapus'
        ]);
    }
}
