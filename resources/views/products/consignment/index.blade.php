@extends('layouts.app')

@section('title', 'Produk Titipan')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Produk Titipan</h2>
                <div class="text-muted mt-1">
                    Kelola produk konsinyasi, mitra, harga, dan stok.
                </div>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('products.consignment.create') }}"
                   class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Produk Titipan
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body border-bottom">
            <form method="GET"
                  action="{{ route('products.consignment.index') }}">
                <div class="row g-2">
                    <div class="col-md-6">
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Cari nama produk atau barcode..."
                            value="{{ $search ?? '' }}"
                        >
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-search me-1"></i>
                            Cari
                        </button>
                    </div>
                    @if (!empty($search))
                        <div class="col-auto">
                            <a href="{{ route('products.consignment.index') }}"
                               class="btn btn-outline-secondary">
                                Reset
                            </a>
                        </div>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Mitra</th>
                        <th class="text-end">Harga Mitra</th>
                        <th class="text-end">Harga Jual</th>
                        <th class="text-end">Untung/Unit</th>
                        <th class="text-end">Stok</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        @php
                            $partnerPrice = (float) $product->partner_price;
                            $sellingPrice = (float) $product->price;
                            $profit = $sellingPrice - $partnerPrice;
                        @endphp

                        <tr>
                            <td>
                                <div class="fw-bold">
                                    {{ $product->name }}
                                </div>
                                <div class="text-muted small">
                                    Barcode: {{ $product->barcode }}
                                </div>
                                <div class="text-muted small">
                                    {{ $product->category->name ?? 'Tanpa kategori' }}
                                </div>
                            </td>

                            <td>
                                {{ $product->consignmentPartner->name ?? '-' }}
                            </td>

                            <td class="text-end text-nowrap">
                                Rp {{ number_format($partnerPrice, 0, ',', '.') }}
                            </td>

                            <td class="text-end text-nowrap">
                                Rp {{ number_format($sellingPrice, 0, ',', '.') }}
                            </td>

                            <td class="text-end text-nowrap">
                                <span class="{{ $profit < 0 ? 'text-danger' : 'text-success' }}">
                                    Rp {{ number_format($profit, 0, ',', '.') }}
                                </span>
                            </td>

                            <td class="text-end">
                                {{ number_format($product->stock, 0, ',', '.') }}
                            </td>

                            <td>
                                @if ($product->is_active)
                                    <span class="badge bg-success-lt">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-lt">Nonaktif</span>
                                @endif
                            </td>

                            <td class="text-end">
                                <a href="{{ route('products.consignment.edit', $product) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-edit me-1"></i>
                                    Edit
                                </a>
                                <a href="{{ route('products.consignment.stock-in.create', $product) }}"
                                    class="btn btn-sm btn-outline-success">
                                    <i class="ti ti-package-import me-1"></i>
                                    Stok Masuk
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Belum ada produk titipan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->hasPages())
            <div class="card-footer">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
