<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
   public function index(Request $request)
    {
        $search = $request->input('search');

        $customers = Customer::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact(
            'customers',
            'search'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:100',],
            'phone' => ['nullable','string','max:20',],
            'address' => ['nullable','string','max:255',],
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Customer berhasil ditambahkan.',
            'data' => $customer,
        ]);
    }

    public function edit(Customer $customer)
    {
        return response()->json([
            'status' => 'success',
            'data' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:100',],
            'phone' => ['nullable','string','max:20',],
            'address' => ['nullable','string','max:255',],
        ]);

        $customer->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Customer berhasil diperbarui.',
            'data' => $customer->fresh(),
        ]);
    }

    public function destroy(Customer $customer)
    {
        try {

            $customer->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Customer berhasil dihapus.',
            ]);

        } catch (\Illuminate\Database\QueryException $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Customer tidak dapat dihapus karena sudah digunakan dalam transaksi.',
            ], 409);
        }
    }
}
