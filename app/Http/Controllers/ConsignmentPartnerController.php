<?php

namespace App\Http\Controllers;

use App\Models\ConsignmentPartner;
use Illuminate\Http\Request;

class ConsignmentPartnerController extends Controller
{
    public function index(Request $request)
    {
        $query = ConsignmentPartner::query();

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('contact_person', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) {
            $query->where(
                'is_active',
                $request->status === 'aktif'
            );
        }

        $partners = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('consignment-partners.index', compact('partners'));
    }

    public function create()
    {
        return view('consignment-partners.form', [
            'partner' => new ConsignmentPartner(),
            'pageTitle' => 'Tambah Mitra Titipan',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        ConsignmentPartner::create($validated);

        return redirect()
            ->route('consignment-partners')
            ->with('success', 'Mitra titipan berhasil ditambahkan.');
    }

    public function edit(ConsignmentPartner $consignment_partner)
    {
        return view('consignment-partners.form', [
            'partner' => $consignment_partner,
            'pageTitle' => 'Edit Mitra Titipan',
        ]);
    }

    public function update(
        Request $request,
        ConsignmentPartner $consignment_partner
    ) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $consignment_partner->update($validated);

        return redirect()
            ->route('consignment-partners')
            ->with('success', 'Data mitra titipan berhasil diperbarui.');
    }
   
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

   

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
