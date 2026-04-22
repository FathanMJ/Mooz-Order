<?php

namespace App\Http\Controllers;

use App\Models\M_pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class C_pengguna extends Controller
{
    public function index()
    {
        $daftarPengguna = M_pengguna::all();
        return view('admin.v_KelolaPengguna', compact('daftarPengguna'));
    }

    public function create()
    {
        return view('admin.v_CreatePengguna');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'email' => 'required|email|unique:pengguna,email',
            'no_hp' => 'required',
            'alamat' => 'required',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,user'
        ]);

        M_pengguna::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil ditambahkan');
    }

    public function show($id)
    {
        $pengguna = M_pengguna::findOrFail($id);
        return view('admin.v_ShowPengguna', compact('pengguna'));
    }

    public function edit($id)
    {
        $pengguna = M_pengguna::findOrFail($id);
        return view('admin.v_EditPengguna', compact('pengguna'));
    }

    public function update(Request $request, $id)
    {
        $pengguna = M_pengguna::findOrFail($id);

        $request->validate([
            'nama' => 'required',
            'email' => 'required|email|unique:pengguna,email,'.$id,
            'no_hp' => 'required',
            'alamat' => 'required',
            'role' => 'required|in:admin,user'
        ]);

        $data = $request->only(['nama', 'email', 'no_hp', 'alamat', 'role']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $pengguna->update($data);

        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil diperbarui');
    }

    public function destroy($id)
    {
        $pengguna = M_pengguna::findOrFail($id);
        $pengguna->delete();
        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil dihapus');
    }
}