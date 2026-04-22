<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProdukRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Sesuaikan ini dengan logika otorisasi Anda
        // Misalnya, hanya admin yang boleh membuat/mengupdate produk
        return true; // Ganti ini dengan logika otorisasi yang sebenarnya
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kategori_produk' => 'required|string|max:255',
            'nama_produk' => 'required|string|max:255',
            'ukuran_produk' => 'nullable|string|max:255', // Ubah menjadi nullable jika opsional
            'keterangan_produk' => 'nullable|string',
            'harga_produk' => 'required|numeric|min:0',
            'foto_produk' => 'array', // Foto produk diharapkan berupa array (untuk multiple files)
            'foto_produk.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi setiap file: harus gambar, tipe tertentu, max 2048 KB (2MB)
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kategori_produk.required' => 'Kategori produk wajib diisi.',
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'harga_produk.required' => 'Harga produk wajib diisi.',
            'harga_produk.numeric' => 'Harga produk harus berupa angka.',
            'harga_produk.min' => 'Harga produk tidak boleh negatif.',
            'foto_produk.array' => 'Foto produk harus berupa kumpulan file.',
            'foto_produk.*.image' => 'File harus berupa gambar.',
            'foto_produk.*.mimes' => 'Format gambar tidak didukung. Gunakan JPEG, PNG, JPG, atau GIF.',
            'foto_produk.*.max' => 'Ukuran file gambar tidak boleh lebih dari 2MB.',
        ];
    }
}
