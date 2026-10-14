<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::all();
        return view('suppliers.index',compact('suppliers'));
    }

     public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $supplier = Supplier::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Supplier berhasil ditambahkan.',
            'data' => $supplier,
        ]);
    }

    public function edit(Supplier $supplier)
    {
        return response()->json([
            'status' => 'success',
            'data' => $supplier,
        ]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $supplier->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Supplier berhasil diperbarui.',
            'data' => $supplier->fresh(),
        ]);
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Supplier berhasil dihapus.',
        ]);
    }
}
