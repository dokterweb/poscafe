@extends('layouts.app')

@section('content')

<div class="page-wrapper">
  <div class="page-header d-print-none">
      <div class="container-xl">
          <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Produk List
                    </h2>
                </div>
            <div class="col-auto ms-auto">
                <button type="button"
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#modalCreateProduct">

                    <i class="ti ti-plus"></i>
                    Tambah Produk

                </button>
            </div>
          </div>
      </div>
  </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('products.index') }}">
                        <div class="row g-2">
                            {{-- SEARCH --}}
                            <div class="col-md-6">
                                <label class="form-label">Cari Produk</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-search"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control" placeholder="Cari barcode atau nama produk..." value="{{ $search }}">
                                </div>
                            </div>

                            {{-- CATEGORY --}}
                            <div class="col-md-4">
                                <label class="form-label">
                                    Kategori
                                </label>
                                <select name="category_id" class="form-select">
                                    <option value="">Semua Kategori</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected($categoryId == $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- BUTTON --}}
                            <div class="col-md-2">
                                <label class="form-label">
                                    &nbsp;
                                </label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-100" >
                                        <i class="ti ti-search me-1"></i>
                                        Cari
                                    </button>
                                    @if ($search || $categoryId)
                                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary" title="Reset">
                                            <i class="fas fa-redo"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Barcode</th>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>HPP</th>
                                <th>Harga</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="product-table">
                        @forelse ($products as $product)
                            <tr id="product-row-{{ $product->id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="font-monospace">
                                        {{ $product->barcode }}
                                    </span>
                                </td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category?->name ?? '-' }}</td>
                                <td>Rp {{ number_format($product->hpp, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                <td>{{ number_format($product->stock, 0, ',', '.') }}</td>
                                <td>
                                    @if ($product->is_active)
                                        <span class="badge bg-green-lt">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-red-lt">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-list justify-content-center">
                                        <button type="button" class="btn btn-sm btn-warning btn-edit-product" data-id="{{ $product->id }}">
                                            <i class="far fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger btn-delete-product" data-id="{{ $product->id }}">
                                            <i class="far fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="empty-product">
                                <td colspan="9" class="text-center text-muted py-4">
                                    Belum ada produk.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($products->hasPages())
                    <ul class="pagination mb-0">
                        {{-- Previous --}}
                        @if ($products->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="ti ti-chevron-left"></i>
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $products->previousPageUrl() }}">
                                    <i class="ti ti-chevron-left"></i>
                                </a>
                            </li>
                        @endif
                        {{-- Page Numbers --}}
                        @foreach ($products->getUrlRange(
                            max(1, $products->currentPage() - 2),
                            min($products->lastPage(), $products->currentPage() + 2)
                        ) as $page => $url)
                            <li class="page-item {{ $page == $products->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $url }}">
                                    {{ $page }}
                                </a>
                            </li>
                        @endforeach
                        {{-- Next --}}
                        @if ($products->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $products->nextPageUrl() }}">
                                    <i class="ti ti-chevron-right"></i>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="ti ti-chevron-right"></i>
                                </span>
                            </li>
                        @endif
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>


{{-- MODAL CREATE PRODUCT --}}
<div class="modal modal-blur fade"
     id="modalCreateProduct"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Tambah Produk</h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Tutup"></button>
            </div>

            <form id="formCreateProduct">
                @csrf

                <div class="modal-body">
                    <div id="create-product-error"></div>

                    {{-- INFORMASI PRODUK --}}
                    <div class="mb-3">
                        <label class="form-label">
                            Nama Produk <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               maxlength="150"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select">
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">HPP</label>
                            <input type="number"
                                   name="hpp"
                                   class="form-control"
                                   min="0"
                                   step="0.01"
                                   value="0"
                                   required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Harga Jual</label>
                            <input type="number"
                                   name="price"
                                   class="form-control"
                                   min="0"
                                   step="0.01"
                                   value="0"
                                   required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stock Awal</label>
                            <input type="number"
                                   name="stock"
                                   class="form-control"
                                   min="0"
                                   step="1"
                                   value="0">
                        </div>
                    </div>

                    <hr>

                    {{-- VARIAN MENU --}}
                    <div class="mb-3">
                        <h3 class="h3 mb-1">Varian Menu</h3>
                        <p class="text-muted small mb-3">
                            Contoh: Regular dan Large, dengan HPP
                            serta harga jual masing-masing.
                        </p>

                        <div id="create-variants-container"></div>

                        <button type="button"
                                class="btn btn-outline-primary btn-sm w-100"
                                id="btn-add-create-variant">
                            <i class="ti ti-plus"></i>
                            Tambah Varian
                        </button>
                    </div>

                    <hr>

                    {{-- PILIHAN TAMBAHAN --}}
                    <div class="mb-3">
                        <h3 class="h3 mb-1">Pilihan Tambahan</h3>
                        <p class="text-muted small">
                            Pilih kelompok pilihan yang tersedia untuk
                            produk ini, misalnya Suhu.
                        </p>

                        <div id="create-option-groups-container">
                            @forelse ($optionGroups as $group)
                                @if ($group->options->where('is_active', true)->isNotEmpty())
                                    <label class="form-check mb-2">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="option_group_ids[]"
                                               value="{{ $group->id }}">

                                        <span class="form-check-label">
                                            <strong>{{ $group->name }}</strong>
                                            <small class="d-block text-muted">
                                                {{ $group->options
                                                    ->where('is_active', true)
                                                    ->pluck('name')
                                                    ->join(', ') }}
                                            </small>
                                        </span>
                                    </label>
                                @endif
                            @empty
                                <div class="alert alert-warning mb-0">
                                    Belum ada kelompok pilihan.
                                    Tambahkan kelompok seperti Suhu dan
                                    pilihan Hot/Iced terlebih dahulu.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <hr>

                    {{-- PELACAKAN STOK --}}
                    <div class="form-check form-switch">
                        <input type="hidden"
                               name="track_stock"
                               value="0">

                        <input class="form-check-input"
                               type="checkbox"
                               name="track_stock"
                               value="1"
                               id="create_track_stock"
                               checked>

                        <label class="form-check-label"
                               for="create_track_stock">
                            Lacak stok produk
                        </label>
                    </div>
                    <small class="text-muted">
                        Aktifkan untuk produk yang stoknya dikelola,
                        misalnya air mineral kemasan.
                    </small>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit"
                            class="btn btn-primary"
                            id="btnSaveProduct">
                        <i class="ti ti-device-floppy"></i>
                        Simpan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


{{-- =====================================================
     MODAL EDIT PRODUCT
===================================================== --}}
<div class="modal modal-blur fade"
     id="modalEditProduct"
     tabindex="-1"
     aria-hidden="true">

    {{-- <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"> --}}
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Edit Produk</h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Tutup"></button>
            </div>

            <form id="formEditProduct">
                @csrf
                @method('PUT')

                <input type="hidden"
                       id="edit_product_id"
                       name="product_id">

                <div class="modal-body">

                    <div id="edit-product-error"></div>

                    {{-- NAMA PRODUK --}}
                    <div class="mb-3">
                        <label class="form-label">
                            Nama Produk <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="name"
                               id="edit_name"
                               class="form-control"
                               maxlength="150"
                               required>
                    </div>

                    {{-- KATEGORI --}}
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>

                        <select name="category_id"
                                id="edit_category_id"
                                class="form-select">
                            <option value="">Pilih Kategori</option>

                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- HPP DAN HARGA UTAMA --}}
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">HPP Utama</label>

                            <input type="number"
                                   name="hpp"
                                   id="edit_hpp"
                                   class="form-control"
                                   min="0"
                                   step="0.01"
                                   required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Harga Jual Utama</label>

                            <input type="number"
                                   name="price"
                                   id="edit_price"
                                   class="form-control"
                                   min="0"
                                   step="0.01"
                                   required>
                        </div>

                        {{-- STOK --}}
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stok Saat Ini</label>

                            <input type="text"
                                id="edit_stock"
                                class="form-control"
                                readonly>

                        </div>
                    </div>

                    {{-- VARIAN MENU --}}
                    <div class="mb-3">
                        <h3 class="h3 mb-1">Varian Menu</h3>

                        <p class="text-muted small">
                            Jika produk tidak memiliki varian, kosongkan bagian ini.
                            Jika memiliki varian, isi HPP dan harga jual masing-masing.
                        </p>

                        <div id="edit-variants-container"></div>

                        <button type="button"
                                class="btn btn-outline-primary btn-sm w-100"
                                id="btn-add-edit-variant">
                            <i class="ti ti-plus"></i>
                            Tambah Varian
                        </button>
                    </div>

                    <hr>

                    <div class="row">
                        {{-- PILIHAN TAMBAHAN --}}
                        <div class="col-md-6 mb-3">
                            <h3 class="h3 mb-1">Pilihan Tambahan</h3>

                            <p class="text-muted small">
                                Centang kelompok pilihan yang tersedia untuk produk ini.
                                Misalnya Suhu, Level Gula, atau Topping.
                            </p>

                            <div id="edit-option-groups-container">
                                @forelse ($optionGroups as $group)
                                    @if ($group->options->isNotEmpty())
                                        <label class="form-check mb-3">
                                            <input class="form-check-input edit-option-group"
                                                type="checkbox"
                                                name="option_group_ids[]"
                                                value="{{ $group->id }}">

                                            <span class="form-check-label">
                                                <strong>{{ $group->name }}</strong>

                                                <small class="d-block text-muted">
                                                    {{ $group->options->pluck('name')->join(', ') }}
                                                </small>
                                            </span>
                                        </label>
                                    @endif
                                @empty
                                    <div class="alert alert-warning">
                                        Belum ada kelompok pilihan.
                                        Tambahkan melalui menu Master Pilihan Tambahan.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            {{-- LACAK STOK --}}
                            <div class="form-check form-switch">
                                <input type="hidden"
                                    name="track_stock"
                                    value="0">

                                <input class="form-check-input"
                                    type="checkbox"
                                    name="track_stock"
                                    id="edit_track_stock"
                                    value="1">

                                <label class="form-check-label"
                                    for="edit_track_stock">
                                    Lacak stok produk
                                </label>
                            </div>

                            <div class="form-hint">
                                Nonaktifkan jika stok produk jadi tidak dilacak.
                            </div>
                        </div>

                        <hr>

                        {{-- STATUS PRODUK --}}
                        <div class="mb-3">
                            <label class="form-label">Status Produk</label>

                            <select name="is_active"
                                    id="edit_is_active"
                                    class="form-select"
                                    required>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit"
                            class="btn btn-primary"
                            id="btnUpdateProduct">
                        <i class="ti ti-device-floppy"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@endsection

@section('scripts')

<script>

$(document).ready(function () {

    // =====================================================
    // VARIAN MENU
    // =====================================================

    let createVariantIndex = 0;

    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    function variantRowHtml(index, variant = {}) {
        const name = escapeHtml(variant.name ?? '');
        const hpp = variant.hpp ?? 0;
        const price = variant.price ?? 0;
        const isActive = variant.is_active === false ||
                         variant.is_active === 0 ||
                         variant.is_active === '0'
            ? ''
            : 'checked';

        return `
            <div class="card card-sm mb-2 variant-row">
                <div class="card-body">
                    <div class="row g-2 align-items-end">

                        <div class="col-md-3">
                            <label class="form-label">Nama Varian</label>
                            <input type="text"
                                   name="variants[${index}][name]"
                                   class="form-control"
                                   value="${name}"
                                   placeholder="Regular / Large"
                                   maxlength="100"
                                   required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">HPP</label>
                            <input type="number"
                                   name="variants[${index}][hpp]"
                                   class="form-control"
                                   min="0"
                                   step="0.01"
                                   value="${hpp}"
                                   required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Harga Jual</label>
                            <input type="number"
                                   name="variants[${index}][price]"
                                   class="form-control"
                                   min="0"
                                   step="0.01"
                                   value="${price}"
                                   required>
                        </div>

                        <div class="col-md-2">
                            <input type="hidden"
                                   name="variants[${index}][is_active]"
                                   value="0">

                            <label class="form-check mb-2">
                                <input type="checkbox"
                                       class="form-check-input"
                                       name="variants[${index}][is_active]"
                                       value="1"
                                       ${isActive}>
                                <span class="form-check-label">Aktif</span>
                            </label>
                        </div>

                        <div class="col-md-1">
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm btn-remove-create-variant"
                                    title="Hapus varian">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        `;
    }

    function addCreateVariant(variant = {}) {
        const index = createVariantIndex++;

        $('#create-variants-container').append(
            variantRowHtml(index, variant)
        );
    }

    $('#btn-add-create-variant').on('click', function () {
        addCreateVariant();
    });

    $(document).on(
        'click',
        '.btn-remove-create-variant',
        function () {
            $(this).closest('.variant-row').remove();
        }
    );


    // =====================================================
    // RESET MODAL CREATE
    // =====================================================

    $('#modalCreateProduct').on('hidden.bs.modal', function () {
        const form = $('#formCreateProduct')[0];

        form.reset();

        $('#create-product-error').empty();
        $('#create-variants-container').empty();

        createVariantIndex = 0;

        $('#create_track_stock').prop('checked', true);

        // Reset hanya input produk/varian.
        // Kelompok pilihan yang dicentang dikosongkan.
        $('#create-option-groups-container')
            .find('input[name="option_group_ids[]"]')
            .prop('checked', false);

        $('#btnSaveProduct')
            .prop('disabled', false)
            .html('<i class="ti ti-device-floppy"></i> Simpan');
    });


    // =====================================================
    // CREATE PRODUCT - AJAX
    // =====================================================

    $('#formCreateProduct').on('submit', function (e) {
        e.preventDefault();

        const form = $(this);
        const button = $('#btnSaveProduct');

        $('#create-product-error').empty();

        button.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

        $.ajax({
            url: "{{ route('products.store') }}",
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',

            success: function (response) {
                if (response.status !== 'success') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message || 'Produk gagal disimpan.'
                    });
                    return;
                }

                const product = response.data;

                // Muat ulang daftar agar pagination, urutan,
                // dan tampilan tabel tetap konsisten.
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message || 'Produk berhasil ditambahkan.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(function () {
                    window.location.reload();
                });

                bootstrap.Modal
                    .getOrCreateInstance(
                        document.getElementById('modalCreateProduct')
                    )
                    .hide();
            },

            error: function (xhr) {
                console.error('Create product error:', xhr.status);
                console.error(xhr.responseText);

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const errors = xhr.responseJSON.errors;
                    let html = '<div class="alert alert-danger"><ul class="mb-0">';

                    Object.values(errors).flat().forEach(function (message) {
                        html += `<li>${escapeHtml(message)}</li>`;
                    });

                    html += '</ul></div>';

                    $('#create-product-error').html(html);

                    return;
                }

                const message = xhr.responseJSON?.message
                    || 'Produk gagal disimpan. Periksa laravel.log untuk detailnya.';

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: message
                });
            },

            complete: function () {
                button.prop('disabled', false)
                    .html('<i class="ti ti-device-floppy"></i> Simpan');
            }
        });
    });
    

    
$(document).ready(function () {

    // =====================================================
    // EDIT PRODUCT - VARIANT ROW
    // =====================================================

    let editVariantIndex = 0;

    function escapeEditHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    function editVariantRowHtml(index, variant = {}) {
        const idField = variant.id
            ? `<input type="hidden"
                      name="variants[${index}][id]"
                      value="${variant.id}">`
            : '';

        const active = variant.is_active === false ||
                       variant.is_active === 0 ||
                       variant.is_active === '0'
            ? ''
            : 'checked';

        return `
            <div class="card card-sm mb-2 edit-variant-row">
                <div class="card-body">
                    ${idField}

                    <div class="row g-2 align-items-end">

                        <div class="col-md-3">
                            <label class="form-label">Nama Varian</label>

                            <input type="text"
                                   name="variants[${index}][name]"
                                   class="form-control"
                                   maxlength="100"
                                   value="${escapeEditHtml(variant.name ?? '')}"
                                   placeholder="Regular / Large"
                                   required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">HPP</label>

                            <input type="number"
                                   name="variants[${index}][hpp]"
                                   class="form-control"
                                   min="0"
                                   step="0.01"
                                   value="${variant.hpp ?? 0}"
                                   required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Harga Jual</label>

                            <input type="number"
                                   name="variants[${index}][price]"
                                   class="form-control"
                                   min="0"
                                   step="0.01"
                                   value="${variant.price ?? 0}"
                                   required>
                        </div>

                        <div class="col-md-2">
                            <input type="hidden"
                                   name="variants[${index}][is_active]"
                                   value="0">

                            <label class="form-check mb-2">
                                <input type="checkbox"
                                       name="variants[${index}][is_active]"
                                       value="1"
                                       class="form-check-input"
                                       ${active}>

                                <span class="form-check-label">Aktif</span>
                            </label>
                        </div>

                        <div class="col-md-1">
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm btn-remove-edit-variant"
                                    title="Hapus varian dari form">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        `;
    }

    function addEditVariant(variant = {}) {
        const index = editVariantIndex++;

        $('#edit-variants-container').append(
            editVariantRowHtml(index, variant)
        );
    }

    $('#btn-add-edit-variant').on('click', function () {
        addEditVariant();
    });

    $(document).on(
        'click',
        '.btn-remove-edit-variant',
        function () {
            $(this).closest('.edit-variant-row').remove();
        }
    );


    // =====================================================
    // EDIT PRODUCT - OPEN MODAL
    // =====================================================

    $(document).on('click', '.btn-edit-product', function () {

        const productId = $(this).data('id');

        $('#edit-product-error').empty();
        $('#formEditProduct')[0].reset();

        $('#edit_product_id').val(productId);
        $('#edit-variants-container').empty();
        $('#edit-option-groups-container')
            .find('.edit-option-group')
            .prop('checked', false);

        editVariantIndex = 0;

        $('#btnUpdateProduct')
            .prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2"></span>Memuat...');

        $.ajax({
            url: "{{ url('/products') }}/" + productId + "/edit",
            type: 'GET',
            dataType: 'json',

            success: function (response) {

                if (response.status !== 'success') {
                    Swal.fire(
                        'Gagal',
                        'Data produk tidak dapat diambil.',
                        'error'
                    );
                    return;
                }

                const product = response.data;

                // Data utama
                $('#edit_product_id').val(product.id);
                $('#edit_barcode').val(product.barcode);
                $('#edit_name').val(product.name);
                $('#edit_category_id').val(product.category_id ?? '');
                $('#edit_hpp').val(product.hpp ?? 0);
                $('#edit_price').val(product.price ?? 0);

                $('#edit_stock').val(
                    new Intl.NumberFormat('id-ID')
                        .format(product.stock ?? 0)
                );

                $('#edit_track_stock')
                    .prop('checked', Boolean(product.track_stock));

                $('#edit_is_active')
                    .val(product.is_active ? '1' : '0');

                // Varian produk
                $('#edit-variants-container').empty();
                editVariantIndex = 0;

                (product.variants || []).forEach(function (variant) {
                    addEditVariant(variant);
                });

                // Jika produk tidak memiliki varian,
                // container dibiarkan kosong.

                // Kelompok pilihan
                $('#edit-option-groups-container')
                    .find('.edit-option-group')
                    .prop('checked', false);

                const groups = product.option_groups
                    || product.optionGroups
                    || [];

                groups.forEach(function (group) {
                    $('#edit-option-groups-container')
                        .find('.edit-option-group')
                        .filter(function () {
                            return String(this.value) === String(group.id);
                        })
                        .prop('checked', true);
                });

                bootstrap.Modal
                    .getOrCreateInstance(
                        document.getElementById('modalEditProduct')
                    )
                    .show();
            },

            error: function (xhr) {
                console.error('Edit product error:', xhr.responseText);

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: xhr.responseJSON?.message
                        || 'Data produk tidak dapat diambil.'
                });
            },

            complete: function () {
                $('#btnUpdateProduct')
                    .prop('disabled', false)
                    .html('<i class="ti ti-device-floppy"></i> Simpan Perubahan');
            }
        });
    });


    // =====================================================
    // UPDATE PRODUCT - AJAX
    // =====================================================

    $('#formEditProduct').on('submit', function (e) {

        e.preventDefault();

        const form = $(this);
        const productId = $('#edit_product_id').val();
        const button = $('#btnUpdateProduct');

        if (!productId) {
            Swal.fire('Gagal', 'ID produk tidak ditemukan.', 'error');
            return;
        }

        $('#edit-product-error').empty();

        button.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

        $.ajax({
            url: "{{ url('/products') }}/" + productId,
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',

            success: function (response) {

                if (response.status !== 'success') {
                    Swal.fire(
                        'Gagal',
                        response.message || 'Produk gagal diperbarui.',
                        'error'
                    );
                    return;
                }

                const product = response.data;

                // Perbarui baris tabel.
                // Urutan kolom mengikuti tabel Produk yang ada.
                const row = $('#product-row-' + product.id);

                row.find('td:eq(2)').text(product.name);

                row.find('td:eq(3)').text(
                    product.category
                        ? product.category.name
                        : '-'
                );

                row.find('td:eq(4)').text(
                    'Rp ' + new Intl.NumberFormat('id-ID')
                        .format(Number(product.hpp || 0))
                );

                row.find('td:eq(5)').text(
                    'Rp ' + new Intl.NumberFormat('id-ID')
                        .format(Number(product.price || 0))
                );

                row.find('td:eq(6)').text(
                    new Intl.NumberFormat('id-ID')
                        .format(Number(product.stock || 0))
                );

                row.find('td:eq(7)').html(
                    product.is_active
                        ? '<span class="badge bg-green-lt">Aktif</span>'
                        : '<span class="badge bg-red-lt">Nonaktif</span>'
                );

                bootstrap.Modal
                    .getOrCreateInstance(
                        document.getElementById('modalEditProduct')
                    )
                    .hide();

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message || 'Produk berhasil diperbarui.',
                    timer: 1500,
                    showConfirmButton: false
                });
            },

            error: function (xhr) {
                console.error('Update product error:', xhr.responseText);

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    let html = '<div class="alert alert-danger"><ul class="mb-0">';

                    Object.values(xhr.responseJSON.errors)
                        .flat()
                        .forEach(function (message) {
                            html += `<li>${escapeEditHtml(message)}</li>`;
                        });

                    html += '</ul></div>';

                    $('#edit-product-error').html(html);
                    return;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: xhr.responseJSON?.message
                        || 'Produk gagal diperbarui. Periksa laravel.log.'
                });
            },

            complete: function () {
                button.prop('disabled', false)
                    .html('<i class="ti ti-device-floppy"></i> Simpan Perubahan');
            }
        });
    });

});


    // =====================================================
    // DELETE PRODUCT
    // =====================================================

    $(document).on('click', '.btn-delete-product', function () {

        let button = $(this);

        let productId = button.data('id');

        Swal.fire({

            title: 'Hapus Produk?',

            text: 'Data produk yang dihapus tidak dapat dikembalikan.',

            icon: 'warning',

            showCancelButton: true,

            confirmButtonText: 'Ya, Hapus',

            cancelButtonText: 'Batal',

            reverseButtons: true,

            focusCancel: true

        }).then((result) => {

            if (!result.isConfirmed) {
                return;
            }

            // ---------------------------------------------
            // DISABLE BUTTON
            // ---------------------------------------------

            button.prop('disabled', true);

            // ---------------------------------------------
            // AJAX DELETE
            // ---------------------------------------------

            $.ajax({

                url: "{{ url('/products') }}/" + productId,

                type: "POST",

                data: {
                    _token: "{{ csrf_token() }}",
                    _method: "DELETE"
                },

                dataType: "json",

                success: function (response) {

                    if (response.status === 'success') {

                        // ---------------------------------
                        // HAPUS ROW
                        // ---------------------------------

                        $('#product-row-' + productId)
                            .fadeOut(300, function () {

                                $(this).remove();

                                // ---------------------------------
                                // JIKA TABEL KOSONG
                                // ---------------------------------

                                if (
                                    $('#product-table tr').length === 0
                                ) {

                                    $('#product-table').html(`
                                        <tr id="empty-product">

                                            <td colspan="9"
                                                class="text-center text-muted py-4">

                                                Belum ada produk.

                                            </td>

                                        </tr>
                                    `);

                                }

                            });


                        // ---------------------------------
                        // SUCCESS ALERT
                        // ---------------------------------

                        Swal.fire({

                            icon: 'success',

                            title: 'Berhasil',

                            text: response.message,

                            timer: 1500,

                            showConfirmButton: false

                        });

                    }

                },

                error: function (xhr) {

                    button.prop('disabled', false);

                    let message = 'Produk gagal dihapus.';

                    if (xhr.status === 409) {

                        message =
                            'Produk tidak dapat dihapus karena sudah digunakan dalam transaksi.';

                    } else if (
                        xhr.responseJSON &&
                        xhr.responseJSON.message
                    ) {

                        message =
                            xhr.responseJSON.message;
                    }

                    Swal.fire({

                        icon: 'error',

                        title: 'Tidak dapat dihapus',

                        text: message

                    });

                }

            });

        });

    });
});


</script>

@endsection