
@extends('layouts.app')

@section('title', 'Data Produk')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Data Produk</h2>
                <div class="text-secondary mt-1">
                    Kelola produk dan menu kafe.
                </div>
            </div>

            <div class="col-auto ms-auto">
                <a href="{{ route('products.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Produk
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Notifikasi --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                <div class="d-flex">
                    <div>
                        <i class="ti ti-check me-2"></i>
                        {{ session('success') }}
                    </div>
                </div>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        @endif

        {{-- Pencarian dan filter --}}
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('products.index') }}" method="GET">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Cari Produk</label>
                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                value="{{ $search ?? '' }}"
                                placeholder="Nama produk atau barcode..."
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Kategori</label>
                            <select name="category_id" class="form-select">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                    <option
                                        value="{{ $category->id }}"
                                        @selected((string) ($categoryId ?? '') === (string) $category->id)
                                    >
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="ti ti-search me-1"></i>
                                Cari
                            </button>

                            <a
                                href="{{ route('products.index') }}"
                                class="btn btn-outline-secondary"
                                title="Reset filter"
                            >
                                <i class="ti ti-refresh"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Daftar produk --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Produk</h3>

                <div class="card-actions">
                    <span class="text-secondary">
                        Total {{ $products->total() }} produk
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter table-hover card-table">
                    <thead>
                        <tr>
                            <th width="55">No.</th>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Varian</th>
                            <th>Kelompok Pilihan</th>
                            <th class="text-end">HPP Dasar</th>
                            <th class="text-end">Harga Jual Dasar</th>
                            <th class="text-end">Stok</th>
                            <th>Status</th>
                            <th width="100" class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>
                                    {{ $products->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <div class="fw-bold">
                                        {{ $product->name }}
                                    </div>

                                    <div class="text-secondary small">
                                        {{ $product->barcode }}
                                    </div>

                                    @if(!$product->track_stock)
                                        <span class="badge bg-azure-lt mt-1">
                                            Stok tidak dilacak
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $product->category->name ?? '-' }}
                                </td>

                                {{-- Varian produk --}}
                                <td>
                                    @php
                                        $activeVariants = $product->variants
                                            ->where('is_active', true);
                                    @endphp

                                    @forelse($activeVariants as $variant)
                                        <div class="mb-2">
                                            <span class="badge bg-blue-lt">
                                                {{ $variant->name }}
                                            </span>

                                            <div class="small text-secondary mt-1">
                                                HPP:
                                                Rp {{ number_format((float) $variant->hpp, 0, ',', '.') }}
                                            </div>

                                            <div class="small fw-bold">
                                                Rp {{ number_format((float) $variant->price, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    @empty
                                        <span class="text-secondary">
                                            Tanpa varian
                                        </span>
                                    @endforelse
                                </td>

                                {{-- Kelompok pilihan --}}
                                <td>
                                    @forelse($product->optionGroups as $group)
                                        <div class="mb-1">
                                            <span class="badge bg-purple-lt">
                                                {{ $group->name }}
                                            </span>
                                        </div>
                                    @empty
                                        <span class="text-secondary">-</span>
                                    @endforelse
                                </td>

                                <td class="text-end">
                                    Rp {{ number_format((float) $product->hpp, 0, ',', '.') }}
                                </td>

                                <td class="text-end">
                                    Rp {{ number_format((float) $product->price, 0, ',', '.') }}
                                </td>

                                <td class="text-end">
                                    @if($product->track_stock)
                                        {{ number_format((int) $product->stock, 0, ',', '.') }}
                                    @else
                                        <span class="text-secondary">-</span>
                                    @endif
                                </td>

                                <td>
                                    @if($product->is_active)
                                        <span class="badge bg-success-lt">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-danger-lt">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a
                                            href="{{ route('products.edit', $product->id) }}"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Edit produk"
                                        >
                                            <i class="far fa-edit"></i>
                                        </a>

                                        <form
                                            action="{{ route('products.destroy', $product->id) }}"
                                            method="POST"
                                            class="form-delete-product"
                                                data-name="{{ $product->name }}"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Hapus produk"
                                            >
                                                 <i class="far fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <i class="ti ti-package-off fs-1 text-secondary"></i>

                                    <div class="fw-bold mt-2">
                                        Produk belum ditemukan
                                    </div>

                                    <div class="text-secondary small mt-1">
                                        Silakan ubah pencarian atau tambahkan produk baru.
                                    </div>

                                    <a
                                        href="{{ route('products.create') }}"
                                        class="btn btn-primary mt-3"
                                    >
                                        <i class="ti ti-plus me-1"></i>
                                        Tambah Produk
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($products->hasPages())
                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-secondary">
                        Menampilkan
                        <strong>{{ $products->firstItem() ?? 0 }}</strong>
                        sampai
                        <strong>{{ $products->lastItem() ?? 0 }}</strong>
                        dari
                        <strong>{{ $products->total() }}</strong>
                        produk
                    </p>

                    <div class="ms-auto">
                        {{ $products->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).on('submit', '.form-delete-product', function (e) {
    e.preventDefault();

    const form = this;
    const productName = $(form).data('name');

    if (typeof Swal === 'undefined') {
        if (confirm('Yakin ingin menghapus produk "' + productName + '"?')) {
            form.submit();
        }

        return;
    }

    Swal.fire({
        title: 'Hapus produk?',
        html: 'Produk <strong>' + $('<div>').text(productName).html() +
              '</strong> akan dihapus.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d63939',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then(function (result) {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});
</script>
@endsection