<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('categories.index',compact('categories'));
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        try {
            // Simpan data ke database
            Category::create([
                'name' => $request->name, // Sesuaikan dengan nama kolom di database
            ]);
            // Notifikasi sukses
            return redirect()->route('categories')
                             ->with('success', 'Data Kategori berhasil ditambahkan.');
        } catch (\Exception $e) {
            // Notifikasi error jika terjadi kesalahan
            return redirect()->route('categories')
                             ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

  public function edit(Category $category)
    {
        $categoryview = Category::all();
        // dd($category);
        return view('categories.edit',compact('category','categoryview'));
    }

    public function update(Request $request, Category $category)
    {
        // Validasi input
        $request->validate(['name' => 'required|string|max:255']);

        try {
            $category->update([
                'name' => $request->name, 
            ]);

            // Notifikasi sukses
            return redirect()->route('categories')
                            ->with('success', 'Data Category berhasil diperbarui.');
        } catch (\Exception $e) {
            // Notifikasi error jika terjadi kesalahan
            return redirect()->route('categories')
                            ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

      public function destroy($id)
    {
        try {
            // Temukan kelas berdasarkan ID
            $category = Category::findOrFail($id);
            
            // Hapus data
            $category->delete();

            // Redirect dengan notifikasi sukses
            return redirect()->route('categories')
                            ->with('success', 'Data Category berhasil dihapus.');
        } catch (\Exception $e) {
            // Jika terjadi kesalahan
            return redirect()->route('categories')
                            ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
