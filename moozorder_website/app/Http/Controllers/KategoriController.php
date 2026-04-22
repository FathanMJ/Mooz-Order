<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index()
    {
        $daftarKategori = Kategori::orderBy('created_at', 'desc')->get();
        return view('admin.v_KelolaKategori', compact('daftarKategori'));
    }

    public function create()
    {
        return view('admin.v_CreateKategori');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);
        // Cek duplikat nama kategori
        if (Kategori::where('nama_kategori', $request->nama_kategori)->exists()) {
            return redirect()->back()->withInput()->with('error', 'Nama kategori sudah tersedia!');
        }
        Kategori::create(['nama_kategori' => $request->nama_kategori]);
        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('admin.v_EditKategori', compact('kategori'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);
        $kategori = Kategori::findOrFail($id);
        // Cek duplikat nama kategori (kecuali dirinya sendiri)
        if (Kategori::where('nama_kategori', $request->nama_kategori)->where('id', '!=', $id)->exists()) {
            return redirect()->back()->withInput()->with('error', 'Nama kategori sudah tersedia!');
        }
        $kategori->update(['nama_kategori' => $request->nama_kategori]);
        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil diupdate!');
    }

    public function destroy($id)
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->delete();
        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus!');
    }
}
