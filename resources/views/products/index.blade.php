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

{{-- MODAL CREATE --}}
<div class="modal modal-blur fade"
     id="modalCreateProduct"
     tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Tambah Produk
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <form id="formCreateProduct">

                @csrf

                <div class="modal-body">

                    <div id="create-product-error"></div>

                    <div class="mb-3">

                        <label class="form-label">
                            Nama Produk
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="name"
                               class="form-control"
                               required>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Kategori
                        </label>

                        <select name="category_id"
                                class="form-select">

                            <option value="">
                                -- Tanpa Kategori --
                            </option>

                            @foreach ($categories as $category)

                                <option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="row">

                        <div class="col-md-4">

                            <div class="mb-3">

                                <label class="form-label">
                                    HPP
                                </label>

                                <input type="number"
                                       name="hpp"
                                       class="form-control"
                                       min="0"
                                       step="0.01"
                                       value="0">

                            </div>

                        </div>


                        <div class="col-md-4">

                            <div class="mb-3">

                                <label class="form-label">
                                    Harga Jual
                                </label>

                                <input type="number"
                                       name="price"
                                       class="form-control"
                                       min="0"
                                       step="0.01"
                                       value="0">

                            </div>

                        </div>


                        <div class="col-md-4">

                            <div class="mb-3">

                                <label class="form-label">
                                    Stock Awal
                                </label>

                                <input type="number"
                                       name="stock"
                                       class="form-control"
                                       min="0"
                                       value="0">

                            </div>

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
                            id="btnSaveProduct">

                        <i class="ti ti-device-floppy"></i>

                        Simpan

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

{{-- ===================================================== --}}
{{-- MODAL EDIT PRODUCT --}}
{{-- ===================================================== --}}

<div class="modal modal-blur fade"
     id="modalEditProduct"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Edit Produk
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>

            </div>

            <form id="formEditProduct">

                @csrf

                <input type="hidden"
                       name="product_id"
                       id="edit_product_id">

                <div class="modal-body">

                    <div id="edit-product-error"></div>


                    {{-- BARCODE --}}

                    <div class="mb-3">

                        <label class="form-label">
                            Barcode
                        </label>

                        <input type="text"
                               id="edit_barcode"
                               class="form-control"
                               readonly>

                        <div class="form-hint">
                            Barcode dibuat otomatis dan tidak dapat diubah.
                        </div>

                    </div>


                    {{-- NAMA --}}

                    <div class="mb-3">

                        <label class="form-label">

                            Nama Produk

                            <span class="text-danger">
                                *
                            </span>

                        </label>

                        <input type="text"
                               name="name"
                               id="edit_name"
                               class="form-control"
                               placeholder="Nama produk">

                    </div>


                    {{-- KATEGORI --}}

                    <div class="mb-3">

                        <label class="form-label">
                            Kategori
                        </label>

                        <select name="category_id"
                                id="edit_category_id"
                                class="form-select">

                            <option value="">
                                -- Tanpa Kategori --
                            </option>

                            @foreach ($categories as $category)

                                <option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- HPP + PRICE --}}

                    <div class="row">

                        <div class="col-md-6">

                            <div class="mb-3">

                                <label class="form-label">
                                    HPP
                                </label>

                                <input type="number"
                                       name="hpp"
                                       id="edit_hpp"
                                       class="form-control"
                                       min="0"
                                       step="0.01">

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="mb-3">

                                <label class="form-label">
                                    Harga Jual
                                </label>

                                <input type="number"
                                       name="price"
                                       id="edit_price"
                                       class="form-control"
                                       min="0"
                                       step="0.01">

                            </div>

                        </div>

                    </div>


                    {{-- STOCK --}}

                    <div class="mb-3">

                        <label class="form-label">
                            Stock Saat Ini
                        </label>

                        <input type="text"
                               id="edit_stock"
                               class="form-control"
                               readonly>

                        <div class="form-hint">
                            Stock dikelola melalui transaksi stok dan penjualan.
                        </div>

                    </div>


                    {{-- STATUS --}}

                    <div class="mb-3">

                        <label class="form-label">
                            Status
                        </label>

                        <select name="is_active"
                                id="edit_is_active"
                                class="form-select">

                            <option value="1">
                                Aktif
                            </option>

                            <option value="0">
                                Nonaktif
                            </option>

                        </select>

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

    $('#formCreateProduct').on('submit', function (e) {

        e.preventDefault();

        let form = $(this);

        let button = $('#btnSaveProduct');

        $('#create-product-error').html('');

        button.prop('disabled', true);

        $.ajax({

            url: "{{ route('products.store') }}",

            type: "POST",

            data: form.serialize(),

            dataType: "json",

            success: function (response) {

                let product = response.data;

                $('#empty-product').remove();

                let categoryName =
                    product.category
                        ? product.category.name
                        : '-';

                let row = `
                    <tr id="product-row-${product.id}">

                        <td>New</td>

                        <td>
                            <span class="font-monospace">
                                ${escapeHtml(product.barcode)}
                            </span>
                        </td>

                        <td>
                            ${escapeHtml(product.name)}
                        </td>

                        <td>
                            ${escapeHtml(categoryName)}
                        </td>

                        <td>
                            Rp ${formatNumber(product.hpp)}
                        </td>

                        <td>
                            Rp ${formatNumber(product.price)}
                        </td>

                        <td>
                            ${formatNumber(product.stock)}
                        </td>

                        <td>
                            <span class="badge bg-green-lt">
                                Aktif
                            </span>
                        </td>

                        <td>

                            <button type="button"
                                    class="btn btn-sm btn-warning btn-edit-product"
                                    data-id="${product.id}">

                                <i class="ti ti-edit"></i>

                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-danger btn-delete-product"
                                    data-id="${product.id}">

                                <i class="ti ti-trash"></i>

                            </button>

                        </td>

                    </tr>
                `;

                $('#product-table').prepend(row);

                bootstrap.Modal
                    .getOrCreateInstance(
                        document.getElementById('modalCreateProduct')
                    )
                    .hide();

                form[0].reset();

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                });

            },

            error: function (xhr) {

                if (xhr.status === 422) {

                    showProductValidationError(
                        '#create-product-error',
                        xhr.responseJSON.errors
                    );

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Produk gagal disimpan.'
                    });

                }

            },

            complete: function () {

                button.prop('disabled', false);

            }

        });

    });


    function formatNumber(value)
    {
        return new Intl.NumberFormat('id-ID')
            .format(value || 0);
    }


    function escapeHtml(text)
    {
        return $('<div>')
            .text(text ?? '')
            .html();
    }


    function showProductValidationError(
        container,
        errors
    )
    {
        let html = `
            <div class="alert alert-danger">
                <ul class="mb-0">
        `;

        $.each(errors, function (field, messages) {

            $.each(messages, function (index, message) {

                html += `
                    <li>
                        ${escapeHtml(message)}
                    </li>
                `;

            });

        });

        html += `
                </ul>
            </div>
        `;

        $(container).html(html);
    }

    
    // =====================================================
    // EDIT PRODUCT - OPEN MODAL
    // =====================================================

    $(document).on('click', '.btn-edit-product', function () {

        let productId = $(this).data('id');

        $('#edit-product-error').html('');

        $('#formEditProduct')[0].reset();

        $('#edit_product_id').val(productId);

        $.ajax({

            url: "{{ url('/products') }}/" + productId + "/edit",

            type: "GET",

            dataType: "json",

            beforeSend: function () {

                $('#btnUpdateProduct')
                    .prop('disabled', true);

            },

            success: function (response) {

                if (response.status === 'success') {

                    let product = response.data;

                    // ID
                    $('#edit_product_id')
                        .val(product.id);

                    // BARCODE
                    $('#edit_barcode')
                        .val(product.barcode);

                    // NAME
                    $('#edit_name')
                        .val(product.name);

                    // CATEGORY
                    $('#edit_category_id')
                        .val(product.category_id ?? '');

                    // HPP
                    $('#edit_hpp')
                        .val(product.hpp);

                    // PRICE
                    $('#edit_price')
                        .val(product.price);

                    // STOCK
                    $('#edit_stock')
                        .val(
                            new Intl.NumberFormat('id-ID')
                                .format(product.stock)
                        );

                    // STATUS
                    $('#edit_is_active')
                        .val(product.is_active ? '1' : '0');

                    // OPEN MODAL
                    let modalElement =
                        document.getElementById(
                            'modalEditProduct'
                        );

                    let modal =
                        bootstrap.Modal
                            .getOrCreateInstance(modalElement);

                    modal.show();
                }

            },

            error: function () {

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Data produk tidak dapat diambil.'
                });

            },

            complete: function () {

                $('#btnUpdateProduct')
                    .prop('disabled', false);

            }

        });

    });

    // =====================================================
    // UPDATE PRODUCT
    // =====================================================

    $('#formEditProduct').on('submit', function (e) {

        e.preventDefault();

        let form = $(this);

        let productId =
            $('#edit_product_id').val();

        let button =
            $('#btnUpdateProduct');

        $('#edit-product-error').html('');

        button.prop('disabled', true);

        $.ajax({

            url: "{{ url('/products') }}/" + productId,

            type: "POST",

            data: form.serialize() + '&_method=PUT',

            dataType: "json",

            success: function (response) {

                if (response.status === 'success') {

                    let product = response.data;

                    let row =
                        $('#product-row-' + product.id);

                    // -----------------------------------------
                    // NAMA
                    // -----------------------------------------

                    row.find('td:eq(2)')
                        .text(product.name);

                    // -----------------------------------------
                    // KATEGORI
                    // -----------------------------------------

                    row.find('td:eq(3)')
                        .text(
                            product.category
                                ? product.category.name
                                : '-'
                        );

                    // -----------------------------------------
                    // HPP
                    // -----------------------------------------

                    row.find('td:eq(4)')
                        .text(
                            'Rp ' +
                            formatNumber(product.hpp)
                        );

                    // -----------------------------------------
                    // HARGA
                    // -----------------------------------------

                    row.find('td:eq(5)')
                        .text(
                            'Rp ' +
                            formatNumber(product.price)
                        );

                    // -----------------------------------------
                    // STOCK
                    // -----------------------------------------

                    row.find('td:eq(6)')
                        .text(
                            formatNumber(product.stock)
                        );

                    // -----------------------------------------
                    // STATUS
                    // -----------------------------------------

                    if (product.is_active) {

                        row.find('td:eq(7)').html(`
                            <span class="badge bg-green-lt">
                                Aktif
                            </span>
                        `);

                    } else {

                        row.find('td:eq(7)').html(`
                            <span class="badge bg-red-lt">
                                Nonaktif
                            </span>
                        `);

                    }

                    // -----------------------------------------
                    // CLOSE MODAL
                    // -----------------------------------------

                    let modalElement =
                        document.getElementById(
                            'modalEditProduct'
                        );

                    let modal =
                        bootstrap.Modal
                            .getOrCreateInstance(modalElement);

                    modal.hide();

                    // -----------------------------------------
                    // RESET FORM
                    // -----------------------------------------

                    form[0].reset();

                    // -----------------------------------------
                    // SUCCESS
                    // -----------------------------------------

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

                if (xhr.status === 422) {

                    showProductValidationError(
                        '#edit-product-error',
                        xhr.responseJSON.errors
                    );

                } else {

                    Swal.fire({

                        icon: 'error',

                        title: 'Gagal',

                        text: 'Produk gagal diperbarui.'

                    });

                }

            },

            complete: function () {

                button.prop('disabled', false);

            }

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