@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
@php
    $isEdit = $product->exists;
@endphp

<div class="container-xl">
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">{{ $pageTitle }}</h2>
                <div class="text-muted mt-1">
                    Data produk dan harga kesepakatan dengan mitra.
                </div>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('products.consignment.index') }}"
                   class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-bold mb-1">Periksa kembali input Anda.</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ $isEdit
            ? route('products.consignment.update', $product)
            : route('products.consignment.store') }}"
    >
        @csrf

        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="row row-cards">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Produk</h3>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">
                                Nama Produk
                            </label>
                            <input
                                type="text"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $product->name) }}"
                                required
                                maxlength="150"
                                placeholder="Contoh: Brownies Cokelat"
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select
                                name="category_id"
                                class="form-select @error('category_id') is-invalid @enderror"
                            >
                                <option value="">-- Tanpa Kategori --</option>
                                @foreach ($categories as $category)
                                    <option
                                        value="{{ $category->id }}"
                                        @selected(
                                            (string) old('category_id', $product->category_id)
                                            === (string) $category->id
                                        )
                                    >
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">
                                Mitra / Pemilik Produk
                            </label>
                            <select
                                name="consignment_partner_id"
                                class="form-select @error('consignment_partner_id') is-invalid @enderror"
                                required
                            >
                                <option value="">-- Pilih Mitra --</option>
                                @foreach ($partners as $partner)
                                    <option
                                        value="{{ $partner->id }}"
                                        @selected(
                                            (string) old(
                                                'consignment_partner_id',
                                                $product->consignment_partner_id
                                            ) === (string) $partner->id
                                        )
                                    >
                                        {{ $partner->name }}
                                        {{ $partner->is_active ? '' : '(Nonaktif)' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('consignment_partner_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if ($partners->isEmpty())
                                <div class="text-danger small mt-1">
                                    Belum ada mitra aktif. Tambahkan mitra terlebih dahulu.
                                </div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">
                                    Harga Mitra per Unit (Rp)
                                </label>
                                <input
                                    type="number"
                                    name="partner_price"
                                    id="partner_price"
                                    class="form-control @error('partner_price') is-invalid @enderror"
                                    value="{{ old('partner_price', $product->partner_price) }}"
                                    min="0"
                                    step="0.01"
                                    required
                                >
                                @error('partner_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-hint">
                                    Nominal yang menjadi hak mitra untuk setiap unit terjual.
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">
                                    Harga Jual per Unit (Rp)
                                </label>
                                <input
                                    type="number"
                                    name="price"
                                    id="price"
                                    class="form-control @error('price') is-invalid @enderror"
                                    value="{{ old('price', $product->price) }}"
                                    min="0"
                                    step="0.01"
                                    required
                                >
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-hint">
                                    Harga yang dibayar pelanggan.
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mb-0">
                            <div class="fw-bold">Simulasi keuntungan per unit</div>
                            <div id="profit-preview" class="mt-1">
                                Masukkan harga mitra dan harga jual.
                            </div>
                            <div id="profit-warning" class="text-danger small mt-1 d-none">
                                Harga jual tidak boleh lebih rendah dari harga mitra.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Stok Produk</h3>
                    </div>

                    <div class="card-body">
                        @if (!$isEdit)
                            <label class="form-label required">
                                Stok Awal
                            </label>
                            <input
                                type="number"
                                name="stock"
                                class="form-control @error('stock') is-invalid @enderror"
                                value="{{ old('stock', 0) }}"
                                min="0"
                                step="1"
                                required
                            >
                            @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-hint mt-2">
                                Stok awal akan dicatat sebagai stok masuk.
                            </div>
                        @else
                            <label class="form-label">Stok Saat Ini</label>
                            <input
                                type="number"
                                class="form-control"
                                value="{{ $product->stock }}"
                                readonly
                            >
                            <div class="form-hint mt-2">
                                Stok tidak dapat diubah dari halaman edit.
                                Gunakan proses stok masuk untuk menambah persediaan.
                            </div>
                        @endif
                    </div>

                    @if ($isEdit)
                        <div class="card-body border-top">
                            <label class="form-label">Status Produk</label>
                            <select name="is_active" class="form-select" required>
                                <option
                                    value="1"
                                    @selected((string) old('is_active', (int) $product->is_active) === '1')
                                >
                                    Aktif
                                </option>
                                <option
                                    value="0"
                                    @selected((string) old('is_active', (int) $product->is_active) === '0')
                                >
                                    Nonaktif
                                </option>
                            </select>
                            @error('is_active')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-device-floppy me-1"></i>
                            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Produk Titipan' }}
                        </button>

                        <a href="{{ route('products.consignment.index') }}"
                           class="btn btn-outline-secondary w-100 mt-2">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const partnerPrice = document.getElementById('partner_price');
    const sellingPrice = document.getElementById('price');
    const preview = document.getElementById('profit-preview');
    const warning = document.getElementById('profit-warning');

    function rupiah(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(value);
    }

    function updateProfit() {
        const partner = parseFloat(partnerPrice.value);
        const selling = parseFloat(sellingPrice.value);

        if (
            partnerPrice.value === '' ||
            sellingPrice.value === '' ||
            !Number.isFinite(partner) ||
            !Number.isFinite(selling)
        ) {
            preview.textContent = 'Masukkan harga mitra dan harga jual.';
            warning.classList.add('d-none');
            return;
        }

        const profit = selling - partner;
        preview.textContent = 'Keuntungan kotor per unit: ' + rupiah(profit);

        warning.classList.toggle('d-none', profit >= 0);
    }

    partnerPrice.addEventListener('input', updateProfit);
    sellingPrice.addEventListener('input', updateProfit);

    updateProfit();
});
</script>
@endsection
