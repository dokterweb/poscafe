<?php

namespace App\Http\Controllers;

use App\Models\OptionGroup;
use App\Models\ProductOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OptionGroupController extends Controller
{
    public function index()
    {
        $optionGroups = OptionGroup::with([
            'options' => fn ($query) => $query->orderBy('name'),
            'products',
        ])
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return view('option-groups.index', compact('optionGroups'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateGroup($request);

        $group = DB::transaction(function () use ($validated) {
            $group = OptionGroup::create([
                'name' => trim($validated['name']),
            ]);

            foreach ($validated['options'] as $optionData) {
                $group->options()->create([
                    'name' => trim($optionData['name']),
                    'price_adjustment' => $optionData['price_adjustment'] ?? 0,
                    'is_active' => (bool) ($optionData['is_active'] ?? false),
                ]);
            }

            return $group;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Kelompok pilihan berhasil ditambahkan.',
            'data' => $group->load('options'),
        ]);
    }

    public function update(Request $request, OptionGroup $option_group)
    {
        $validated = $this->validateGroup(
            $request,
            $option_group
        );

        DB::transaction(function () use ($validated, $option_group) {
            $option_group->update([
                'name' => trim($validated['name']),
            ]);

            $keptIds = [];

            foreach ($validated['options'] as $optionData) {
                $data = [
                    'name' => trim($optionData['name']),
                    'price_adjustment' => $optionData['price_adjustment'] ?? 0,
                    'is_active' => (bool) ($optionData['is_active'] ?? false),
                ];

                if (!empty($optionData['id'])) {
                    $option = $option_group->options()
                        ->whereKey($optionData['id'])
                        ->firstOrFail();

                    $option->update($data);
                } else {
                    $option = $option_group->options()->create($data);
                }

                $keptIds[] = $option->id;
            }

            // Hapus opsi yang dihilangkan dari form.
            // Jangan lakukan ini jika opsi sudah direferensikan
            // oleh tabel transaksi penjualan.
            $option_group->options()
                ->whereNotIn('id', $keptIds)
                ->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Kelompok pilihan berhasil diperbarui.',
            'data' => $option_group->fresh()->load('options'),
        ]);
    }

    public function destroy(OptionGroup $option_group)
    {
        if ($option_group->products()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kelompok pilihan tidak dapat dihapus karena masih digunakan oleh produk.',
            ], 409);
        }

        DB::transaction(function () use ($option_group) {
            $option_group->options()->delete();
            $option_group->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Kelompok pilihan berhasil dihapus.',
        ]);
    }

    private function validateGroup(
        Request $request,
        ?OptionGroup $optionGroup = null
    ): array {
        $groupId = $optionGroup?->id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('option_groups', 'name')->ignore($groupId),
            ],

            'options' => ['required', 'array', 'min:1'],
            'options.*.id' => ['nullable', 'integer'],
            'options.*.name' => ['required', 'string', 'max:100'],
            'options.*.price_adjustment' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'options.*.is_active' => ['nullable', 'boolean'],
        ]);

        $names = collect($validated['options'])
            ->map(fn ($option) => mb_strtolower(trim($option['name'])))
            ->all();

        if (count($names) !== count(array_unique($names))) {
            throw ValidationException::withMessages([
                'options' => 'Nama pilihan tidak boleh duplikat dalam satu kelompok.',
            ]);
        }

        // Pastikan ID opsi yang dikirim benar-benar milik kelompok ini.
        foreach ($validated['options'] as $option) {
            if (!empty($option['id'])) {
                if (!$optionGroup) {
                    throw ValidationException::withMessages([
                        'options' => 'ID pilihan tidak valid.',
                    ]);
                }

                $belongsToGroup = $optionGroup->options()
                    ->whereKey($option['id'])
                    ->exists();

                if (!$belongsToGroup) {
                    throw ValidationException::withMessages([
                        'options' => 'Ditemukan pilihan yang bukan milik kelompok ini.',
                    ]);
                }
            }
        }

        return $validated;
    }

    public function editData(OptionGroup $option_group)
    {
        return response()->json([
            'status' => 'success',
            'data' => $option_group->load('options'),
        ]);
    }
}
