@extends('layouts.app')

@push('styles')

<style>

.autocomplete-wrapper {
    position: relative;
}

.autocomplete-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1050;

    background: #fff;

    border: 1px solid #d9dfe7;

    border-radius: 0 0 4px 4px;

    max-height: 300px;

    overflow-y: auto;

    display: none;

    box-shadow: 0 4px 12px rgba(0,0,0,.08);
}

.autocomplete-item {
    padding: 10px 14px;

    border-bottom: 1px solid #eee;

    cursor: pointer;

    background: #fff;
}

.autocomplete-item:last-child {
    border-bottom: none;
}

.autocomplete-item:hover,
.autocomplete-item.active {
    background: #f1f5f9;
}

.autocomplete-item strong {
    display: block;
}

.autocomplete-item small {
    display: block;

    margin-top: 2px;

    color: #667382;
}

</style>

@endpush


@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Sales</h2>
            </div>
        </div>
    </div>
</div>


<div class="page-body">
    <div class="container-xl">
        {{-- CUSTOMER + DATE --}}
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <label class="form-label">Nama Customer</label>
                        <input type="hidden" id="customer_id" name="customer_id" value="{{ $sale?->customer_id ?? '' }}">
                        <div class="autocomplete-wrapper">
                            <input type="text" id="customer_search" class="form-control"
                                autocomplete="off" placeholder="Ketik nama customer..." value="{{ $sale?->customer?->name ?? '' }}">
                            <div id="customer_results"
                                class="autocomplete-results">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <label class="form-label">Tanggal</label>
                        <input type="date" id="sale_date" class="form-control" value="{{ $sale?->date?->format('Y-m-d') ?? now()->format('Y-m-d') }}">

                    </div>

                </div>

            </div>

        </div>
        {{-- PRODUCT SEARCH --}}

        <div class="card mb-3">
            <div class="card-body">
                 <div class="autocomplete-wrapper">
                    <div class="input-group">
                        <input type="text" id="product_search" class="form-control"
                            autocomplete="off" placeholder="Cari produk berdasarkan nama atau barcode...">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div id="product_results"
                        class="autocomplete-results">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th width="35%">Produk</th>
                            <th width="15%">Harga</th>
                            <th width="10%">Stok</th>
                            <th width="10%">Qty</th>
                            <th width="10%">Diskon (%)</th>
                            <th width="15%">Total</th>
                            <th width="5%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="cart-body">
                        <tr id="empty-cart">
                            <td colspan="7" class="text-center text-muted py-4">
                                Belum ada produk.
                            </td>
                        </tr>
                    </tbody>

                    <tfoot>

                        <tr>

                            <th colspan="5"
                                class="text-end">

                                Grand Total

                            </th>

                            <th id="grand-total">

                                Rp 0

                            </th>

                            <th></th>

                        </tr>

                    </tfoot>

                </table>

            </div>

        </div>


        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <label class="form-label">Status Bayar</label>
                        <select id="payment_status" class="form-select">
                            <option value="belum_lunas" @selected(($sale?->payment_status ?? 'belum_lunas') === 'belum_lunas')>
                                Belum Lunas
                            </option>
                            <option value="lunas" @selected(($sale?->payment_status ?? '') === 'lunas')>
                                Lunas
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <label class="form-label">Catatan</label>
                        <textarea id="sale_note" class="form-control" rows="3">{{ $sale?->note ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-end">
                        <h2>Total:<span id="grand-total-bottom">Rp 0</span></h2>
                        <div class="btn-group w-100">
                            <button type="button" id="btnSubmitSale" class="btn btn-primary">
                                @if ($sale)
                                    <i class="ti ti-device-floppy"></i>
                                    Update
                                @else
                                    <i class="ti ti-check"></i>
                                    Submit
                                @endif
                            </button>
                            <button type="button" id="btnResetSale" class="btn btn-secondary">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>

$(document).ready(function () {
    
    let customerActiveIndex = -1;
    let customerTimer = null;

    let productActiveIndex = -1;
    let productTimer = null;

    const editMode = @json((bool) $sale);

    const saleId = @json($sale?->id);

    const initialCart = @json($initialCart ?? []);

    let cart = initialCart.map(function (item) {

        return {
            id: item.id,
            barcode: item.barcode,
            name: item.name,
            price: parseFloat(item.price) || 0,
            stock: parseInt(item.stock) || 0,
            qty: parseInt(item.qty) || 1,
            discount: parseFloat(item.discount) || 0
        };

    });

    


    // =================================================
    // FORMAT RUPIAH
    // =================================================

    function formatRupiah(value)
    {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }).format(value || 0);
    }


    // =================================================
    // ESCAPE HTML
    // =================================================

    function escapeHtml(text)
    {
        return $('<div>')
            .text(text ?? '')
            .html();
    }


    // =================================================
    // CUSTOMER AUTOCOMPLETE
    // =================================================

    $('#customer_search').on('input', function () {

        let keyword = $(this).val().trim();

        // Customer sebelumnya dibatalkan
        $('#customer_id').val('');

        clearTimeout(customerTimer);

        customerActiveIndex = -1;

        // Jika kosong
        if (keyword.length === 0) {

            $('#customer_results')
                .hide()
                .html('');

            return;
        }


        customerTimer = setTimeout(function () {

            $.ajax({

                url: "{{ route('sales.search-customers') }}",

                type: "GET",

                data: {
                    q: keyword
                },

                dataType: "json",

                success: function (customers) {

                    customerActiveIndex = -1;

                    let html = '';


                    // Tidak ada hasil
                    if (!customers.length) {

                        html = `
                            <div class="p-3 text-muted">
                                Customer tidak ditemukan.
                            </div>
                        `;

                    } else {

                        customers.forEach(function (customer) {

                            html += `

                                <div class="autocomplete-item customer-item"
                                     data-id="${customer.id}"
                                     data-name="${escapeHtml(customer.name)}">

                                    <strong>
                                        ${escapeHtml(customer.name)}
                                    </strong>

                                    <small>
                                        ${escapeHtml(customer.phone || '')}
                                    </small>

                                </div>

                            `;

                        });

                    }


                    $('#customer_results')
                        .html(html)
                        .show();

                },

                error: function (xhr) {

                    console.error(
                        'Customer search error:',
                        xhr
                    );

                    $('#customer_results')
                        .hide()
                        .html('');

                }

            });

        }, 250);

    });


    // =================================================
    // CUSTOMER KEYBOARD
    // =================================================

    $('#customer_search').on('keydown', function (e) {

        let items =
            $('#customer_results .customer-item');


        if (!items.length) {
            return;
        }


        // ---------------------------------------------
        // ARROW DOWN
        // ---------------------------------------------

        if (e.key === 'ArrowDown') {

            e.preventDefault();

            customerActiveIndex++;


            if (customerActiveIndex >= items.length) {

                customerActiveIndex = 0;

            }


            items.removeClass('active');


            let activeItem =
                items.eq(customerActiveIndex);


            activeItem.addClass('active');


            activeItem[0].scrollIntoView({
                block: 'nearest'
            });


            return;
        }


        // ---------------------------------------------
        // ARROW UP
        // ---------------------------------------------

        if (e.key === 'ArrowUp') {

            e.preventDefault();

            customerActiveIndex--;


            if (customerActiveIndex < 0) {

                customerActiveIndex =
                    items.length - 1;

            }


            items.removeClass('active');


            let activeItem =
                items.eq(customerActiveIndex);


            activeItem.addClass('active');


            activeItem[0].scrollIntoView({
                block: 'nearest'
            });


            return;
        }


        // ---------------------------------------------
        // ENTER
        // ---------------------------------------------

        if (e.key === 'Enter') {

            e.preventDefault();


            if (customerActiveIndex >= 0) {

                items
                    .eq(customerActiveIndex)
                    .trigger('click');

            }


            return;
        }


        // ---------------------------------------------
        // ESCAPE
        // ---------------------------------------------

        if (e.key === 'Escape') {

            $('#customer_results')
                .hide();

            customerActiveIndex = -1;

        }

    });


    // =================================================
    // CUSTOMER CLICK
    // =================================================

    $(document).on(
        'click',
        '.customer-item',
        function () {

            let id =
                $(this).data('id');

            let name =
                $(this).data('name');


            $('#customer_id')
                .val(id);


            $('#customer_search')
                .val(name);


            $('#customer_results')
                .hide()
                .html('');


            customerActiveIndex = -1;

        }
    );


    // =================================================
    // PRODUCT SEARCH
    // =================================================

    $('#product_search').on('input', function () {

        let keyword =
            $(this).val().trim();


        clearTimeout(productTimer);

        productActiveIndex = -1;


        if (keyword.length === 0) {

            $('#product_results')
                .hide()
                .html('');

            return;
        }


        productTimer = setTimeout(function () {

            $.ajax({

                url: "{{ route('sales.search-products') }}",

                type: "GET",

                data: {
                    q: keyword
                },

                dataType: "json",

                success: function (products) {

                    productActiveIndex = -1;

                    let html = '';


                    // Tidak ada produk
                    if (!products.length) {

                        html = `
                            <div class="p-3 text-muted">
                                Produk tidak ditemukan / stok habis.
                            </div>
                        `;

                    } else {

                        products.forEach(function (product) {

                            html += `

                                <div class="autocomplete-item product-item"
                                     data-id="${product.id}">

                                    <strong>
                                        ${escapeHtml(product.name)}
                                    </strong>

                                    <small>
                                        Stock: ${product.stock}
                                        |
                                        Rp ${formatRupiah(product.price)}
                                    </small>

                                </div>

                            `;

                        });

                    }


                    $('#product_results')
                        .html(html)
                        .show();

                },

                error: function (xhr) {

                    console.error(
                        'Product search error:',
                        xhr
                    );

                    $('#product_results')
                        .hide()
                        .html('');

                }

            });

        }, 250);

    });


    // =================================================
    // PRODUCT KEYBOARD
    // =================================================

    $('#product_search').on('keydown', function (e) {

        let items =
            $('#product_results .product-item');


        if (!items.length) {
            return;
        }


        // ---------------------------------------------
        // ARROW DOWN
        // ---------------------------------------------

        if (e.key === 'ArrowDown') {

            e.preventDefault();

            productActiveIndex++;


            if (productActiveIndex >= items.length) {

                productActiveIndex = 0;

            }


            items.removeClass('active');


            let activeItem =
                items.eq(productActiveIndex);


            activeItem.addClass('active');


            activeItem[0].scrollIntoView({
                block: 'nearest'
            });


            return;
        }


        // ---------------------------------------------
        // ARROW UP
        // ---------------------------------------------

        if (e.key === 'ArrowUp') {

            e.preventDefault();

            productActiveIndex--;


            if (productActiveIndex < 0) {

                productActiveIndex =
                    items.length - 1;

            }


            items.removeClass('active');


            let activeItem =
                items.eq(productActiveIndex);


            activeItem.addClass('active');


            activeItem[0].scrollIntoView({
                block: 'nearest'
            });


            return;
        }


        // ---------------------------------------------
        // ENTER
        // ---------------------------------------------

        if (e.key === 'Enter') {

            e.preventDefault();


            if (productActiveIndex >= 0) {

                items
                    .eq(productActiveIndex)
                    .trigger('click');

            }


            return;
        }


        // ---------------------------------------------
        // ESCAPE
        // ---------------------------------------------

        if (e.key === 'Escape') {

            $('#product_results')
                .hide();

            productActiveIndex = -1;

        }

    });


    // =================================================
    // SELECT PRODUCT
    // =================================================

    $(document).on(
        'click',
        '.product-item',
        function () {

            let productId =
                $(this).data('id');


            $.ajax({

                url:
                    "{{ url('/products') }}/" +
                    productId +
                    "/edit",

                type: "GET",

                dataType: "json",

                success: function (response) {

                    addProductToCart(
                        response.data
                    );

                },

                error: function (xhr) {

                    console.error(
                        'Get product error:',
                        xhr
                    );

                    Swal.fire({

                        icon: 'error',

                        title: 'Gagal',

                        text:
                            'Data produk tidak dapat diambil.'

                    });

                }

            });

        }
    );


    // =================================================
    // ADD PRODUCT
    // =================================================

    function addProductToCart(product)
    {

        let existing =
            cart.find(
                item => item.id == product.id
            );


        if (existing) {

            if (existing.qty >= product.stock) {

                Swal.fire({

                    icon: 'warning',

                    title: 'Stock tidak mencukupi',

                    text:
                        'Qty sudah mencapai stock tersedia.'

                });

                return;
            }


            existing.qty++;

        } else {

            cart.push({

                id:
                    product.id,

                barcode:
                    product.barcode,

                name:
                    product.name,

                price:
                    parseFloat(product.price),

                stock:
                    parseInt(product.stock),

                qty:
                    1,

                discount:
                    0

            });

        }


        $('#product_search')
            .val('');


        $('#product_results')
            .hide()
            .html('');


        productActiveIndex = -1;


        renderCart();

    }


    // =================================================
    // RENDER CART
    // =================================================

    function renderCart()
    {
        let tbody =$('#cart-body');
        tbody.html('');
        if (cart.length === 0) {
            tbody.html(`
                <tr id="empty-cart">
                    <td colspan="7" class="text-center text-muted py-4">
                        Belum ada produk.
                    </td>
                </tr>
            `);
            updateGrandTotal();
            return;
        }
        cart.forEach(function (item, index) {
            let subtotal = item.price * item.qty;
            let discountValue =subtotal *(item.discount / 100);
            let total =subtotal - discountValue;
            tbody.append(`
                <tr data-index="${index}">
                    <td>${escapeHtml(item.name)}</td>
                    <td>
                        <input type="text" class="form-control cart-price" value="${item.price}" inputmode="decimal" data-index="${index}">
                    </td>
                    <td>${item.stock}</td>
                    <td>
                        <input type="number" class="form-control cart-qty" value="${item.qty}" min="1" max="${item.stock}"data-index="${index}">
                    </td>
                    <td>
                        <input type="text" class="form-control cart-discount" value="${item.discount}" inputmode="decimal" data-index="${index}">
                    </td>
                    <td class="cart-row-total">Rp ${formatRupiah(total)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger btn-remove-cart" data-index="${index}">
                            <i class="far fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `);
        });
        updateGrandTotal();
    }


    // =================================================
    // QTY
    // =================================================

    $(document).on(
        'input',
        '.cart-qty',
        function () {

            let index =
                $(this).data('index');


            let qty =
                parseInt($(this).val());


            if (isNaN(qty) || qty < 1) {

                qty = 1;

            }


            if (qty > cart[index].stock) {

                qty =
                    cart[index].stock;


                $(this).val(qty);

            }


            cart[index].qty =
                qty;


            updateCartRow(index);

            updateGrandTotal();

        }
    );


    // =================================================
    // DISCOUNT
    // =================================================

    $(document).on(
        'input',
        '.cart-discount',
        function () {

            let index =
                $(this).data('index');


            let value =
                $(this).val();


            // Support 1,5 dan 1.5
            value =
                value.replace(',', '.');


            // Hanya angka dan titik
            value =
                value.replace(/[^0-9.]/g, '');


            // Maksimal satu titik
            let parts =
                value.split('.');


            if (parts.length > 2) {

                value =
                    parts[0] +
                    '.' +
                    parts.slice(1).join('');

            }


            let discount =
                parseFloat(value);


            if (isNaN(discount)) {

                discount = 0;

            }


            if (discount > 100) {

                discount = 100;

            }


            if (discount < 0) {

                discount = 0;

            }


            cart[index].discount =
                discount;


            updateCartRow(index);

            updateGrandTotal();

        }
    );


    // =================================================
    // PRICE
    // =================================================

    $(document).on(
        'input',
        '.cart-price',
        function () {

            let index =
                $(this).data('index');


            let value =
                $(this)
                    .val()
                    .replace(',', '.');


            let price =
                parseFloat(value);


            if (isNaN(price) || price < 0) {

                price = 0;

            }


            cart[index].price =
                price;


            updateCartRow(index);

            updateGrandTotal();

        }
    );


    // =================================================
    // REMOVE CART
    // =================================================

    $(document).on(
        'click',
        '.btn-remove-cart',
        function () {

            let index =
                $(this).data('index');


            cart.splice(index, 1);


            renderCart();

        }
    );


    // =================================================
    // UPDATE CART ROW
    // =================================================

    function updateCartRow(index)
    {

        let item =
            cart[index];


        let subtotal =
            item.price *
            item.qty;


        let discountValue =
            subtotal *
            (item.discount / 100);


        let total =
            subtotal -
            discountValue;


        let row =
            $('#cart-body tr[data-index="' + index + '"]');


        row.find('.cart-row-total')
            .text(
                'Rp ' +
                formatRupiah(total)
            );

    }


    // =================================================
    // GRAND TOTAL
    // =================================================

    function updateGrandTotal()
    {

        let total = 0;


        cart.forEach(function (item) {

            let subtotal =
                item.price *
                item.qty;


            let discountValue =
                subtotal *
                (item.discount / 100);


            total +=
                subtotal -
                discountValue;

        });


        $('#grand-total')
            .text(
                'Rp ' +
                formatRupiah(total)
            );


        $('#grand-total-bottom')
            .text(
                'Rp ' +
                formatRupiah(total)
            );

    }


    // =================================================
    // RESET
    // =================================================

    $('#btnResetSale').on(
        'click',
        function () {

            Swal.fire({

                title:
                    'Reset transaksi?',

                text:
                    'Semua item dalam cart akan dihapus.',

                icon:
                    'warning',

                showCancelButton:
                    true,

                confirmButtonText:
                    'Ya, Reset',

                cancelButtonText:
                    'Batal',

                reverseButtons:
                    true

            }).then(function (result) {

                if (!result.isConfirmed) {
                    return;
                }


                cart = [];


                $('#customer_id')
                    .val('');


                $('#customer_search')
                    .val('');


                $('#customer_results')
                    .hide()
                    .html('');


                $('#product_search')
                    .val('');


                $('#product_results')
                    .hide()
                    .html('');


                $('#sale_note')
                    .val('');


                $('#payment_status')
                    .val('belum_lunas');


                customerActiveIndex = -1;

                productActiveIndex = -1;


                renderCart();

            });

        }
    );


    // =================================================
    // INITIAL
    // =================================================

    renderCart();

    // =====================================================
    // SUBMIT SALE
    // CREATE + EDIT
    // =====================================================

    $('#btnSubmitSale').on(
        'click',
        function () {

            // =================================================
            // VALIDASI CART
            // =================================================

            if (cart.length === 0) {

                Swal.fire({

                    icon: 'warning',

                    title: 'Cart kosong',

                    text:
                        'Silakan pilih produk terlebih dahulu.'

                });

                return;
            }


            // =================================================
            // AMBIL BUTTON
            // =================================================

            let button =
                $(this);


            // =================================================
            // CART DATA
            // =================================================

            let cartData =
                cart.map(function (item) {

                    return {

                        id:
                            item.id,

                        price:
                            item.price,

                        qty:
                            item.qty,

                        discount:
                            item.discount

                    };

                });


            // =================================================
            // DATA FORM
            // =================================================

            let data = {

                _token:
                    "{{ csrf_token() }}",

                customer_id:
                    $('#customer_id').val() || null,

                date:
                    $('#sale_date').val(),

                payment_status:
                    $('#payment_status').val(),

                note:
                    $('#sale_note').val(),

                cart:
                    cartData

            };


            // =================================================
            // TENTUKAN URL
            // =================================================

            let ajaxUrl;


            if (editMode) {

                // ---------------------------------------------
                // EDIT
                // ---------------------------------------------

                ajaxUrl =
                    "{{ url('/sales') }}/" +
                    saleId;

                // Laravel PUT melalui POST
                data._method = 'PUT';

            } else {

                // ---------------------------------------------
                // CREATE
                // ---------------------------------------------

                ajaxUrl =
                    "{{ route('sales.store') }}";

            }


            // =================================================
            // DISABLE BUTTON
            // =================================================

            button.prop(
                'disabled',
                true
            );


            // Ubah teks button

            button.html(

                '<span class="spinner-border spinner-border-sm me-1"></span>' +

                (
                    editMode
                        ? 'Menyimpan...'
                        : 'Memproses...'
                )

            );


            // =================================================
            // AJAX
            // =================================================

            $.ajax({

                url:
                    ajaxUrl,

                type:
                    'POST',

                data:
                    data,

                dataType:
                    'json',


                // =================================================
                // SUCCESS
                // =================================================

                success:
                    function (response) {

                        Swal.fire({

                            icon:
                                'success',

                            title:
                                editMode
                                    ? 'Transaksi Berhasil Diperbarui'
                                    : 'Transaksi Berhasil',

                            html:

                                'Invoice: <strong>' +

                                escapeHtml(
                                    response.invoice
                                ) +

                                '</strong>',

                            confirmButtonText:
                                editMode
                                    ? 'Lihat Detail'
                                    : 'Cetak',

                            allowOutsideClick:
                                false,

                            allowEscapeKey:
                                false

                        }).then(function (result) {


                            // =================================
                            // EDIT
                            // =================================

                            if (editMode) {

                                if (
                                    result.isConfirmed
                                ) {

                                    window.location.href =
                                        response.redirect;

                                }

                                return;

                            }


                            // =================================
                            // CREATE
                            // =================================

                            if (
                                result.isConfirmed
                            ) {

                                let printUrl =
                                    "{{ url('/sales') }}/" +
                                    response.sale_id +
                                    "/print";


                                // Buka halaman print
                                window.open(
                                    printUrl,
                                    '_blank'
                                );


                                // Kembali ke form transaksi baru
                                setTimeout(
                                    function () {

                                        window.location.href =
                                            "{{ route('sales.create') }}";

                                    },
                                    500
                                );

                            }

                        });

                    },


                // =================================================
                // ERROR
                // =================================================

                error:
                    function (xhr) {

                        console.error(
                            'Submit sale error:',
                            xhr
                        );


                        let message =
                            'Transaksi gagal disimpan.';


                        // -----------------------------------------
                        // Validation / Server message
                        // -----------------------------------------

                        if (
                            xhr.responseJSON &&
                            xhr.responseJSON.message
                        ) {

                            message =
                                xhr.responseJSON.message;

                        }


                        // -----------------------------------------
                        // Validation errors Laravel
                        // -----------------------------------------

                        if (
                            xhr.responseJSON &&
                            xhr.responseJSON.errors
                        ) {

                            let errors =
                                xhr.responseJSON.errors;


                            let errorMessages = [];


                            Object.keys(errors).forEach(
                                function (key) {

                                    errors[key].forEach(
                                        function (error) {

                                            errorMessages.push(
                                                error
                                            );

                                        }
                                    );

                                }
                            );


                            if (
                                errorMessages.length
                            ) {

                                message =
                                    errorMessages.join(
                                        '<br>'
                                    );

                            }

                        }


                        Swal.fire({

                            icon:
                                'error',

                            title:
                                'Gagal',

                            html:
                                message

                        });

                    },


                // =================================================
                // COMPLETE
                // =================================================

                complete:
                    function () {

                        button.prop(
                            'disabled',
                            false
                        );


                        button.html(

                            editMode

                                ? '<i class="ti ti-device-floppy"></i> Update'

                                : '<i class="ti ti-check"></i> Submit'

                        );

                    }

            });

        }
    );

});

</script>

@endsection