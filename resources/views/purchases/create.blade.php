@extends('layouts.app')

@push('styles')
<style>

    .supplier-search-wrapper {
        position: relative;
    }

    .supplier-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;

        margin-top: 4px;

        background: #fff;

        border: 1px solid #dee2e6;
        border-radius: 8px;

        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.10);

        max-height: 280px;
        overflow-y: auto;

        z-index: 1050;

        display: none;
    }

    .supplier-dropdown.show {
        display: block;
    }

    .supplier-item {
        display: block;

        width: 100%;

        padding: 10px 14px;

        border: 0;
        border-bottom: 1px solid #f1f1f1;

        background: #fff;

        text-align: left;

        cursor: pointer;

        transition: background-color .15s ease;
    }

    .supplier-item:last-child {
        border-bottom: 0;
    }

    .supplier-item:hover,
    .supplier-item.active {
        background-color: #f1f5f9;
    }

    .supplier-item .supplier-name {
        font-weight: 600;
        color: #212529;
    }

    .supplier-item .supplier-phone {
        display: block;

        margin-top: 2px;

        font-size: 12px;

        color: #6c757d;
    }

    .supplier-empty {
        padding: 12px 14px;

        color: #6c757d;

        font-size: 14px;

        text-align: center;
    }

.product-search-wrapper {
        position: relative;
    }

    .product-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;

        margin-top: 4px;

        background: #fff;

        border: 1px solid #dee2e6;
        border-radius: 8px;

        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.10);

        max-height: 320px;
        overflow-y: auto;

        z-index: 1050;

        display: none;
    }

    .product-dropdown.show {
        display: block;
    }

    .product-item {
        display: block;

        width: 100%;

        padding: 11px 14px;

        border: 0;
        border-bottom: 1px solid #f1f1f1;

        background: #fff;

        text-align: left;

        cursor: pointer;

        transition: background-color .15s ease;
    }

    .product-item:last-child {
        border-bottom: 0;
    }

    .product-item:hover,
    .product-item.active {
        background-color: #f1f5f9;
    }

    .product-name {
        font-weight: 600;
        color: #212529;
    }

    .product-info {
        display: flex;

        justify-content: space-between;

        margin-top: 3px;

        font-size: 12px;

        color: #6c757d;
    }

    .product-empty {
        padding: 18px;

        text-align: center;

        background: #fff8e1;

        border: 1px solid #ffe082;

        border-radius: 8px;

        color: #495057;

        font-size: 14px;
    }

    .product-empty .product-not-found-title {
        color: #b45309;

        font-weight: 600;

        font-size: 15px;

        margin-bottom: 4px;
    }

    .product-empty .product-not-found-description {
        color: #6c757d;

        font-size: 13px;

        margin-bottom: 12px;
    }

    .product-empty .btn-add-product {
        background-color: #0d6efd;

        border-color: #0d6efd;

        color: #fff;

        font-weight: 500;

        padding: 7px 14px;

        border-radius: 6px;

        transition: all .15s ease;
    }

    .product-empty .btn-add-product:hover {
        background-color: #0b5ed7;

        border-color: #0a58ca;

        color: #fff;
    }

    #newProductModal .modal-header {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    #newProductModal .modal-title {
        font-weight: 600;
    }

    #newProductModal .form-label {
        font-weight: 500;
    }

    #newProductModal .alert-info {
        font-size: 13px;
    }
        
</style>
@endpush

@section('content')

<div class="page-body">
    <div class="container-xl">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Pembelian Barang</h4>
                <p class="text-muted mb-0">
                    Input pembelian barang dari supplier
                </p>
            </div>

            <a href="{{ route('purchases.index') }}"
            class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Kembali
            </a>
        </div>


        <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">

            @csrf

            {{-- DATA PEMBELIAN --}}
            <div class="card shadow-sm mb-4">

                <div class="card-header">
                    <strong>Informasi Pembelian</strong>
                </div>

                <div class="card-body">

                    <div class="row">

                        {{-- SUPPLIER --}}
                    <div class="col-md-6 mb-3">

                            <label class="form-label fw-semibold">
                                Supplier <span class="text-danger">*</span>
                            </label>

                            <div class="supplier-search-wrapper position-relative">

                                <input type="text"
                                    class="form-control"
                                    id="supplier_search"
                                    placeholder="Cari nama supplier..."
                                    autocomplete="off">

                                <input type="hidden"
                                    name="supplier_id"
                                    id="supplier_id">

                                <div id="supplier_results"
                                    class="supplier-dropdown">
                                </div>

                            </div>

                        </div>


                        {{-- TANGGAL --}}
                        <div class="col-md-3 mb-3">

                            <label class="form-label">
                                Tanggal <span class="text-danger">*</span>
                            </label>

                            <input type="date"
                                name="date"
                                class="form-control"
                                value="{{ date('Y-m-d') }}"
                                required>

                        </div>


                        {{-- NOMOR --}}
                        <div class="col-md-3 mb-3">

                            <label class="form-label">
                                Nomor Pembelian
                            </label>

                            <input type="text"
                                name="invoice"
                                class="form-control"
                                value="PB-{{ date('YmdHis') }}"
                                readonly>

                        </div>

                    </div>

                </div>

            </div>


            {{-- PRODUK --}}
            <div class="card shadow-sm mb-4">

                <div class="card-header">

                    <div class="d-flex justify-content-between align-items-center">

                        <strong>Barang Pembelian</strong>

                        <span class="text-muted small">
                            Cari produk untuk menambahkan barang
                        </span>

                    </div>

                </div>

                <div class="card-body">

                    <div class="mb-4 position-relative">

                        <label class="form-label">
                            Cari Produk
                        </label>

                        <input type="text"
                            class="form-control"
                            id="product_search"
                            placeholder="Ketik nama produk..."
                            autocomplete="off">

                        <div id="product_results"
                            class="list-group position-absolute w-100 shadow-sm"
                            style="z-index: 999;">
                        </div>

                    </div>


                    {{-- TABLE --}}
                    <div class="table-responsive">

                        <table class="table table-bordered align-middle">

                            <thead class="table-light">

                                <tr>

                                    <th width="35%">
                                        Produk
                                    </th>

                                    <th width="15%"
                                        class="text-center">
                                        Qty
                                    </th>

                                    <th width="20%"
                                        class="text-end">
                                        Harga Beli
                                    </th>

                                    <th width="20%"
                                        class="text-end">
                                        Subtotal
                                    </th>

                                    <th width="10%"
                                        class="text-center">
                                    </th>

                                </tr>

                            </thead>

                            <tbody id="purchase_items">

                                <tr id="empty_row">

                                    <td colspan="5"
                                        class="text-center text-muted py-4">

                                        Belum ada barang.

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


            {{-- TOTAL --}}
        <div class="row justify-content-end">

                <div class="col-md-5">

                    <div class="card shadow-sm">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <strong>
                                    Grand Total
                                </strong>

                                <strong class="text-primary fs-4"
                                        id="grand_total_display">
                                    Rp 0
                                </strong>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            {{-- CATATAN --}}
            <div class="card shadow-sm mt-4 mb-4">

                <div class="card-body">

                    <label class="form-label">
                        Catatan
                    </label>

                    <textarea name="catatan"
                            class="form-control"
                            rows="3"
                            placeholder="Catatan pembelian (opsional)"></textarea>

                </div>

            </div>


            {{-- BUTTON --}}
            <div class="d-flex justify-content-end gap-2">

                <a href="{{ route('purchases.index') }}"
                class="btn btn-secondary">

                    Batal

                </a>

                <button type="submit"
                        class="btn btn-primary">

                    <i class="bi bi-save"></i>

                    Simpan Pembelian

                </button>

            </div>

        </form>

    </div>
</div>

</form>


{{-- ==========================================================
     MODAL TAMBAH PRODUK BARU
     ========================================================== --}}

<div class="modal fade" id="newProductModal" tabindex="-1" aria-labelledby="newProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newProductModalLabel">
                    <i class="bi bi-box-seam me-2"></i>
                    Tambah Produk Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- ERROR --}}
                <div id="new_product_errors"
                     class="alert alert-danger d-none">
                </div>
                <div class="row">
                    {{-- BARCODE --}}
                   <div class="col-md-6 mb-3">
                        <label class="form-label">Barcode</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="new_product_barcode" readonly>
                            <span class="input-group-text">
                                <i class="bi bi-upc-scan"></i>
                            </span>
                        </div>
                        <div class="form-text">
                            Barcode dibuat otomatis oleh sistem.
                        </div>
                    </div>
                    {{-- NAMA PRODUK --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Produk
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="new_product_name" placeholder="Masukkan nama produk">
                    </div>
                    {{-- KATEGORI --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Kategori
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="new_product_category_id">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    {{-- HPP --}}
                    <div class="col-md-3 mb-3">
                        <label class="form-label">HPP
                            <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control text-end" id="new_product_hpp" min="0" step="0.01" placeholder="0">
                    </div>
                    {{-- HARGA JUAL --}}
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Harga Jual
                            <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control text-end" id="new_product_price" min="0" step="0.01" placeholder="0">
                    </div>
                </div>
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Stok produk akan bertambah setelah transaksi pembelian
                    disimpan.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="btn btn-primary" id="btn_save_new_product">
                    <i class="bi bi-save me-1"></i>
                    Simpan Produk
                </button>
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')

<script>

$(document).ready(function () {

    // ==========================================================
    // DATA PURCHASE ITEMS
    // ==========================================================

    let purchaseItems = [];


    // ==========================================================
    // SUPPLIER
    // ==========================================================

    let supplierIndex = -1;


    // ==========================================================
    // SEARCH SUPPLIER
    // ==========================================================

    $('#supplier_search').on('input', function () {

        let keyword = $(this).val().trim();

        supplierIndex = -1;

        // Karena user mulai mengetik ulang,
        // supplier sebelumnya dianggap belum dipilih.
        $('#supplier_id').val('');


        if (keyword.length < 2) {

            $('#supplier_results')
                .removeClass('show')
                .empty();

            return;
        }


        $.ajax({

            url: "{{ route('purchases.searchSuppliers') }}",

            type: "GET",

            data: {
                q: keyword
            },

            dataType: "json",

            success: function (data) {

                let html = '';


                if (data.length === 0) {

                    html = `
                        <div class="supplier-empty">
                            Supplier tidak ditemukan
                        </div>
                    `;

                } else {

                    data.forEach(function (supplier, index) {

                        html += `
                            <button type="button"
                                    class="supplier-item"
                                    data-index="${index}"
                                    data-id="${supplier.id}"
                                    data-name="${supplier.name}">

                                <span class="supplier-name">
                                    ${supplier.name}
                                </span>

                                <span class="supplier-phone">
                                    ${supplier.phone ?? '-'}
                                </span>

                            </button>
                        `;

                    });

                }


                $('#supplier_results')
                    .html(html)
                    .addClass('show');

            },


            error: function (xhr) {

                console.error(
                    'Supplier search error:',
                    xhr.responseText
                );

                $('#supplier_results')
                    .removeClass('show')
                    .empty();

            }

        });

    });


    // ==========================================================
    // SUPPLIER KEYBOARD NAVIGATION
    // ==========================================================

    $('#supplier_search').on('keydown', function (e) {

        let items =
            $('#supplier_results .supplier-item');


        if (!items.length) {
            return;
        }


        // ------------------------------------------
        // ARROW DOWN
        // ------------------------------------------

        if (e.key === 'ArrowDown') {

            e.preventDefault();

            supplierIndex++;


            if (supplierIndex >= items.length) {

                supplierIndex = 0;

            }


            setSupplierActive(items);

        }


        // ------------------------------------------
        // ARROW UP
        // ------------------------------------------

        else if (e.key === 'ArrowUp') {

            e.preventDefault();

            supplierIndex--;


            if (supplierIndex < 0) {

                supplierIndex = items.length - 1;

            }


            setSupplierActive(items);

        }


        // ------------------------------------------
        // ENTER
        // ------------------------------------------

        else if (e.key === 'Enter') {

            e.preventDefault();


            if (supplierIndex >= 0) {

                $(items[supplierIndex]).trigger('click');

            }

        }


        // ------------------------------------------
        // ESC
        // ------------------------------------------

        else if (e.key === 'Escape') {

            e.preventDefault();

            supplierIndex = -1;

            $('#supplier_results')
                .removeClass('show');

        }

    });


    // ==========================================================
    // SET ACTIVE SUPPLIER
    // ==========================================================

    function setSupplierActive(items)
    {
        items.removeClass('active');


        let activeItem =
            $(items[supplierIndex]);


        activeItem.addClass('active');


        // Scroll otomatis
        let container =
            $('#supplier_results');


        let itemTop =
            activeItem.position().top;


        let itemBottom =
            itemTop + activeItem.outerHeight();


        let containerHeight =
            container.innerHeight();


        if (itemBottom > containerHeight) {

            container.scrollTop(
                container.scrollTop() +
                (itemBottom - containerHeight)
            );

        }


        if (itemTop < 0) {

            container.scrollTop(
                container.scrollTop() + itemTop
            );

        }

    }


    // ==========================================================
    // SELECT SUPPLIER
    // ==========================================================

    $(document).on(
        'click',
        '.supplier-item',
        function () {

            let id =
                $(this).data('id');


            let name =
                $(this).data('name');


            $('#supplier_id')
                .val(id);


            $('#supplier_search')
                .val(name);


            supplierIndex = -1;


            $('#supplier_results')
                .removeClass('show')
                .empty();

        }
    );


    // ==========================================================
    // PRODUCT
    // ==========================================================

    let productIndex = -1;


    // ==========================================================
    // SEARCH PRODUCT
    // ==========================================================

    $('#product_search').on('input', function () {

        let keyword =
            $(this).val().trim();


        productIndex = -1;


        if (keyword.length < 2) {

            $('#product_results')
                .removeClass('show')
                .empty();

            return;
        }


        $.ajax({

            url: "{{ route('purchases.searchProducts') }}",

            type: "GET",

            data: {
                q: keyword
            },

            dataType: "json",

            success: function (data) {

                let html = '';


                // ==================================================
                // PRODUCT NOT FOUND
                // ==================================================

                if (data.length === 0) {

                   html = `
                        <div class="product-empty">

                            <div class="product-not-found-title">

                                <i class="bi bi-exclamation-circle me-1"></i>

                                Produk tidak ditemukan

                            </div>

                            <div class="product-not-found-description">

                                Produk belum tersedia di database.

                            </div>

                            <button type="button"
                                    class="btn btn-sm btn-add-product"
                                    id="btn_add_new_product">

                                <i class="bi bi-plus-circle me-1"></i>

                                Tambah Produk Baru

                            </button>

                        </div>
                    `;

                }


                // ==================================================
                // PRODUCT FOUND
                // ==================================================

                else {

                    data.forEach(function (product, index) {

                        html += `

                            <button type="button"
                                    class="product-item"
                                    data-index="${index}"
                                    data-id="${product.id}"
                                    data-name="${product.name}"
                                    data-barcode="${product.barcode ?? ''}"
                                    data-hpp="${product.hpp}"
                                    data-stock="${product.stock}">

                                <div class="product-name">

                                    ${product.name}

                                </div>


                                <div class="product-info">

                                    <span>
                                        Barcode:
                                        ${product.barcode ?? '-'}
                                    </span>


                                    <span>
                                        Stok:
                                        ${product.stock}
                                    </span>


                                    <span>
                                        HPP:
                                        Rp ${formatRupiah(product.hpp)}
                                    </span>

                                </div>

                            </button>

                        `;

                    });

                }


                $('#product_results')
                    .html(html)
                    .addClass('show');

            },


            error: function (xhr) {

                console.error(
                    'Product search error:',
                    xhr.responseText
                );

                $('#product_results')
                    .removeClass('show')
                    .empty();

            }

        });

    });


    // ==========================================================
    // PRODUCT KEYBOARD NAVIGATION
    // ==========================================================

    $('#product_search').on('keydown', function (e) {

        let items =
            $('#product_results .product-item');


        if (!items.length) {
            return;
        }


        // ------------------------------------------
        // ARROW DOWN
        // ------------------------------------------

        if (e.key === 'ArrowDown') {

            e.preventDefault();

            productIndex++;


            if (productIndex >= items.length) {

                productIndex = 0;

            }


            setProductActive(items);

        }


        // ------------------------------------------
        // ARROW UP
        // ------------------------------------------

        else if (e.key === 'ArrowUp') {

            e.preventDefault();

            productIndex--;


            if (productIndex < 0) {

                productIndex = items.length - 1;

            }


            setProductActive(items);

        }


        // ------------------------------------------
        // ENTER
        // ------------------------------------------

        else if (e.key === 'Enter') {

            e.preventDefault();


            if (productIndex >= 0) {

                $(items[productIndex]).trigger('click');

            }

        }


        // ------------------------------------------
        // ESC
        // ------------------------------------------

        else if (e.key === 'Escape') {

            e.preventDefault();

            productIndex = -1;

            $('#product_results')
                .removeClass('show');

        }

    });


    // ==========================================================
    // SET ACTIVE PRODUCT
    // ==========================================================

    function setProductActive(items)
    {
        items.removeClass('active');


        let activeItem =
            $(items[productIndex]);


        activeItem.addClass('active');


        // Scroll otomatis
        let container =
            $('#product_results');


        let itemTop =
            activeItem.position().top;


        let itemBottom =
            itemTop + activeItem.outerHeight();


        let containerHeight =
            container.innerHeight();


        // Scroll ke bawah jika item keluar dari area
        if (itemBottom > containerHeight) {

            container.scrollTop(
                container.scrollTop() +
                (itemBottom - containerHeight)
            );

        }


        // Scroll ke atas jika item keluar dari area
        if (itemTop < 0) {

            container.scrollTop(
                container.scrollTop() + itemTop
            );

        }

    }


    // ==========================================================
    // SELECT PRODUCT
    // ==========================================================

    $(document).on(
        'click',
        '.product-item',
        function () {

            let product = {

                id:
                    $(this).data('id'),

                name:
                    $(this).data('name'),

                barcode:
                    $(this).data('barcode'),

                hpp:
                    parseFloat(
                        $(this).data('hpp')
                    ) || 0,

                stock:
                    parseFloat(
                        $(this).data('stock')
                    ) || 0

            };


            addProductToPurchase(product);


            productIndex = -1;


            $('#product_results')
                .removeClass('show')
                .empty();


            $('#product_search')
                .val('')
                .focus();

        }
    );


    // ==========================================================
    // ADD PRODUCT TO PURCHASE
    // ==========================================================

    function addProductToPurchase(product)
    {

        let existingIndex =
            purchaseItems.findIndex(function (item) {

                return item.product_id == product.id;

            });


        // ======================================================
        // PRODUCT ALREADY EXISTS
        // ======================================================

        if (existingIndex !== -1) {

            purchaseItems[existingIndex].qty++;


            renderPurchaseItems();

            return;
        }


        // ======================================================
        // NEW PRODUCT
        // ======================================================

        purchaseItems.push({

            product_id:
                product.id,

            name:
                product.name,

            barcode:
                product.barcode,

            qty:
                1,

            price:
                product.hpp,

            total:
                product.hpp

        });


        renderPurchaseItems();

    }


    // ==========================================================
    // RENDER PURCHASE ITEMS
    // ==========================================================

    function renderPurchaseItems()
    {
        let tbody =$('#purchase_items');
        tbody.empty();
        // EMPTY
        if (purchaseItems.length === 0) {
            tbody.html(`
                <tr id="empty_row">
                    <td colspan="5" class="text-center text-muted py-4">
                        Belum ada barang.
                    </td>
                </tr>
            `);
            updatePurchaseTotal();
            return;
        }

        // ITEMS
        purchaseItems.forEach(
            function (item, index) {
                let subtotal =item.qty * item.price;
                item.total =subtotal;
                tbody.append(`
                    <tr>
                        <td>
                            <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                            <div class="fw-semibold">
                                ${item.name}
                            </div>
                            <small class="text-muted">
                                ${item.barcode ?? '-'}
                            </small>
                        </td>
                        <td>
                           <input type="number" name="items[${index}][qty]" class="form-control form-control-sm text-center item-qty"
                            data-index="${index}" value="${item.qty}" min="1" step="1">
                        </td>
                        <td>
                         <input type="number" name="items[${index}][price]" class="form-control form-control-sm text-end item-price"
                            data-index="${index}" value="${item.price}" min="0" step="0.01">
                        </td>
                        <td class="text-end">
                           <strong class="item-subtotal">
                                Rp ${formatRupiah(item.total)}
                            </strong>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-product" data-index="${index}">
                                <i class="far fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                `);
            }
        );
        updatePurchaseTotal();
    }


    // ==========================================================
    // CHANGE QTY
    // ==========================================================

  $(document).on('input', '.item-qty', function () {

    let input = $(this);

    let index = input.data('index');

    let qty = parseInt(input.val()) || 0;

    if (qty < 1) {
        qty = 1;
    }

    purchaseItems[index].qty = qty;

    let subtotal =
        purchaseItems[index].qty *
        purchaseItems[index].price;

    purchaseItems[index].total = subtotal;

    // Update subtotal baris saja
    input
        .closest('tr')
        .find('.item-subtotal')
        .text('Rp ' + formatRupiah(subtotal));

    // Update grand total
    updatePurchaseTotal();

});


    // ==========================================================
    // CHANGE PURCHASE PRICE
    // ==========================================================

 $(document).on('input', '.item-price', function () {

    let input = $(this);

    let index = input.data('index');

    let price = parseFloat(input.val()) || 0;

    if (price < 0) {
        price = 0;
    }

    purchaseItems[index].price = price;

    let subtotal =
        purchaseItems[index].qty *
        purchaseItems[index].price;

    purchaseItems[index].total = subtotal;

    // Update subtotal baris saja
    input
        .closest('tr')
        .find('.item-subtotal')
        .text('Rp ' + formatRupiah(subtotal));

    // Update grand total
    updatePurchaseTotal();

});


    // ==========================================================
    // REMOVE PRODUCT
    // ==========================================================

    $(document).on(
        'click',
        '.btn-remove-product',
        function () {

            let index =
                $(this).data('index');


            purchaseItems.splice(
                index,
                1
            );


            renderPurchaseItems();

        }
    );


    // ==========================================================
    // UPDATE GRAND TOTAL
    // ==========================================================

    function updatePurchaseTotal()
    {

        let grandTotal = 0;


        purchaseItems.forEach(
            function (item) {

                grandTotal +=
                    item.qty * item.price;

            }
        );


        $('#grand_total_display')
            .text(
                'Rp ' +
                formatRupiah(grandTotal)
            );

    }


    // ==========================================================
    // FORMAT RUPIAH
    // ==========================================================

    function formatRupiah(value)
    {

        return new Intl.NumberFormat(
            'id-ID'
        ).format(value);

    }


    // ==========================================================
    // CLICK OUTSIDE
    // ==========================================================

    $(document).on('click', function (e) {

        // Supplier
        if (
            !$(e.target)
                .closest('.supplier-search-wrapper')
                .length
        ) {

            supplierIndex = -1;

            $('#supplier_results')
                .removeClass('show');

        }


        // Product
        if (
            !$(e.target)
                .closest('.product-search-wrapper')
                .length
        ) {

            productIndex = -1;

            $('#product_results')
                .removeClass('show');

        }

    });

   $(document).on('click', '#btn_add_new_product', function () {

    $('#product_results')
        .removeClass('show')
        .empty();


    resetNewProductForm();


    let modalElement =
        document.getElementById('newProductModal');


    let modal =
        bootstrap.Modal.getOrCreateInstance(
            modalElement
        );


    modal.show();


    // Generate barcode otomatis
    generateNewProductBarcode();


    setTimeout(function () {

        $('#new_product_name').focus();

    }, 300);

});

    $(document).on('click', '#btn_save_new_product', function () {

        let button = $(this);


        let barcode =
            $('#new_product_barcode')
                .val()
                .trim();


        let name =
            $('#new_product_name')
                .val()
                .trim();


        let categoryId =
            $('#new_product_category_id')
                .val();


        let hpp =
            $('#new_product_hpp')
                .val();


        let price =
            $('#new_product_price')
                .val();


        // ==================================================
        // VALIDASI
        // ==================================================

        if (
            barcode === '' ||
            barcode === 'Generating...'
        ) {

            showNewProductError(
                'Barcode belum tersedia. Silakan tunggu sebentar.'
            );

            return;
        }


        if (name === '') {

            showNewProductError(
                'Nama produk wajib diisi.'
            );

            $('#new_product_name').focus();

            return;
        }


        if (categoryId === '') {

            showNewProductError(
                'Kategori produk wajib dipilih.'
            );

            $('#new_product_category_id').focus();

            return;
        }


        if (
            hpp === '' ||
            parseFloat(hpp) < 0
        ) {

            showNewProductError(
                'HPP wajib diisi.'
            );

            $('#new_product_hpp').focus();

            return;
        }


        if (
            price === '' ||
            parseFloat(price) < 0
        ) {

            showNewProductError(
                'Harga jual wajib diisi.'
            );

            $('#new_product_price').focus();

            return;
        }


        // ==================================================
        // CSRF
        // ==================================================

        let csrfToken =
            $('meta[name="csrf-token"]').attr('content');


        if (!csrfToken) {

            showNewProductError(
                'CSRF token tidak ditemukan.'
            );

            return;
        }


        // ==================================================
        // BUTTON LOADING
        // ==================================================

        button
            .prop('disabled', true)
            .html(`
                <span class="spinner-border spinner-border-sm me-1"></span>
                Menyimpan...
            `);


        $('#new_product_errors')
            .addClass('d-none')
            .empty();


        // ==================================================
        // AJAX
        // ==================================================

        $.ajax({

            url: "{{ route('purchases.storeProduct') }}",

            type: "POST",

            headers: {

                'X-CSRF-TOKEN': csrfToken,

                'Accept': 'application/json'

            },

            data: {

                barcode: barcode,

                name: name,

                category_id: categoryId,

                hpp: hpp,

                price: price

            },

            dataType: "json",


            success: function (response) {

                console.log(
                    'Product created:',
                    response
                );


                if (!response.success) {

                    showNewProductError(
                        response.message ||
                        'Produk gagal disimpan.'
                    );

                    resetSaveProductButton();

                    return;
                }


                let product =
                    response.product;


                // ==================================================
                // TAMBAHKAN KE PURCHASE
                // ==================================================

                addProductToPurchase({

                    id:
                        product.id,

                    name:
                        product.name,

                    barcode:
                        product.barcode,

                    hpp:
                        parseFloat(product.hpp) || 0,

                    stock:
                        parseFloat(product.stock) || 0

                });


                // ==================================================
                // TUTUP MODAL
                // ==================================================

                let modalElement =
                    document.getElementById(
                        'newProductModal'
                    );


                let modal =
                    bootstrap.Modal.getOrCreateInstance(
                        modalElement
                    );


                modal.hide();


                // ==================================================
                // RESET SEARCH
                // ==================================================

                $('#product_search')
                    .val('')
                    .focus();


                // Reset form setelah modal tertutup
                setTimeout(function () {

                    resetNewProductForm();

                }, 300);

            },


            error: function (xhr) {

                console.error(
                    'Product error:',
                    xhr.status,
                    xhr.responseText
                );


                let message =
                    'Terjadi kesalahan saat menyimpan produk.';


                if (xhr.status === 422) {

                    let response =
                        xhr.responseJSON;


                    if (
                        response &&
                        response.errors
                    ) {

                        let errors = [];


                        Object.values(
                            response.errors
                        ).forEach(function (messages) {

                            messages.forEach(function (msg) {

                                errors.push(msg);

                            });

                        });


                        if (errors.length > 0) {

                            message =
                                errors.join('<br>');

                        }

                    }

                }


                else if (xhr.status === 419) {

                    message =
                        'Session atau CSRF token tidak valid. Silakan refresh halaman.';

                }


                else if (xhr.status === 403) {

                    message =
                        'Anda tidak memiliki izin menambahkan produk.';

                }


                else if (xhr.status === 500) {

                    message =
                        'Terjadi kesalahan pada server. Periksa Console Laravel.';

                }


                showNewProductError(message);


                resetSaveProductButton();

            }

        });

    });

    function showNewProductError(message)
    {
        $('#new_product_errors')
            .removeClass('d-none')
            .html(`
                <i class="bi bi-exclamation-triangle me-1"></i>
                ${message}
            `);
    }

    function resetSaveProductButton()
    {
        $('#btn_save_new_product')
            .prop('disabled', false)
            .html(`
                <i class="bi bi-save me-1"></i>
                Simpan Produk
            `);
    }

    function generateNewProductBarcode()
    {
        $('#new_product_barcode')
            .val('Generating...');


        $.ajax({

            url: "{{ route('purchases.generateProductBarcode') }}",

            type: "GET",

            dataType: "json",

            success: function (response) {

                if (response.success) {

                    $('#new_product_barcode')
                        .val(response.barcode);

                } else {

                    $('#new_product_barcode')
                        .val('');

                    showNewProductError(
                        'Barcode gagal dibuat.'
                    );

                }

            },

            error: function (xhr) {

                console.error(
                    'Generate barcode error:',
                    xhr.responseText
                );


                $('#new_product_barcode')
                    .val('');


                showNewProductError(
                    'Gagal membuat barcode otomatis.'
                );

            }

        });
    }

    function resetNewProductForm()
    {
        $('#new_product_barcode').val('');

        $('#new_product_name').val('');

        $('#new_product_category_id').val('');

        $('#new_product_hpp').val('');

        $('#new_product_price').val('');

        $('#new_product_errors')
            .addClass('d-none')
            .empty();

        resetSaveProductButton();
    }
});


</script>

@endsection