<?php

namespace App\Http\Controllers;

use App\Models\Meja;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MejaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $mejas = Meja::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nomor', 'like', "%{$search}%")
                      ->orWhere('nama', 'like', "%{$search}%");
                });
            })
            ->orderBy('nomor')
            ->paginate(15)
            ->withQueryString();

        return view('mejas.index', compact('mejas', 'search'));
    }

    public function create()
    {
        return view('mejas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor' => ['required', 'string', 'max:20', 'unique:mejas,nomor'],
            'nama' => ['nullable', 'string', 'max:100'],
            'kapasitas' => ['required', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ]);

        $meja = Meja::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Meja berhasil ditambahkan.',
            'redirect' => route('mejas.index'),
            'data' => $meja,
        ], 201);
    }

    public function edit(Meja $meja)
    {
        return view('mejas.edit', compact('meja'));
    }

    public function update(Request $request, Meja $meja)
    {
        $validated = $request->validate([
            'nomor' => [
                'required',
                'string',
                'max:20',
                Rule::unique('mejas', 'nomor')->ignore($meja->id),
            ],
            'nama' => ['nullable', 'string', 'max:100'],
            'kapasitas' => ['required', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ]);

        $meja->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Meja berhasil diperbarui.',
            'redirect' => route('mejas.index'),
            'data' => $meja,
        ]);
    }

    public function destroy(Meja $meja)
    {
        $meja->delete();

        return redirect()
            ->route('mejas.index')
            ->with('success', 'Meja berhasil dihapus.');
    }
}
