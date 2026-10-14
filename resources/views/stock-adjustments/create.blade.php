@extends('layouts.app')

@push('styles')
<style>

.product-search-results {

    position: absolute;

    top: 100%;

    left: 0;

    right: 0;

    z-index: 1050;

    background: #fff;

    border: 1px solid #d9dee4;

    border-radius: 0 0 6px 6px;

    box-shadow:
        0 6px 18px rgba(0,0,0,.12);

    max-height: 300px;

    overflow-y: auto;

}


.product-search-item {

    padding: 10px 12px;

    cursor: pointer;

    border-bottom: 1px solid #f0f0f0;

}


.product-search-item:last-child {

    border-bottom: none;

}


.product-search-item:hover,

.product-search-item.active {

    background: #f0f6ff;

}


.product-search-item .product-name {

    font-weight: 600;

}


.product-search-item .product-meta {

    font-size: 12px;

    color: #6c757d;

}


.product-search-empty {

    padding: 12px;

    text-align: center;

    color: #6c757d;

}

</style>
@endpush

@section('content')

<div class="page-wrapper">

    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">Stock Adjustment</div>
                    <h2 class="page-title">Sesuaikan stok berdasarkan kondisi stok fisik.</h2>
                </div>
                
            </div>
        </div>
    </div>

    {{-- ======================================================
        SUCCESS
    ======================================================= --}}
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-1"></i>
            {{ session('success') }}
        </div>
    @endif
    {{-- ======================================================
        ERROR
    ======================================================= --}}
    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-1"></i>
            {{ session('error') }}
        </div>
    @endif
    {{-- ======================================================
        VALIDATION
    ======================================================= --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">
                Periksa input berikut:
            </div>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- ======================================================
        FORM
    ======================================================= --}}
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Penyesuaian Stok</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('stock-adjustments.store') }}">
                            @csrf
                            <div class="row">
                                {{-- ======================================
                                    PRODUK
                                ======================================= --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Produk
                                        <span class="text-danger">*</span>
                                    </label>
                                    {{-- ==================================================
                                        PRODUCT SEARCH
                                    =================================================== --}}
                                    <div class="position-relative">
                                        <input type="text" id="product_search" class="form-control" autocomplete="off"
                                            placeholder="Cari nama produk / barcode...">
                                        {{-- SEARCH RESULT --}}
                                        <div id="product_results" class="product-search-results d-none" ></div>
                                    </div>
                                    {{-- ==================================================
                                        SELECTED PRODUCT
                                    =================================================== --}}
                                    <input type="hidden" name="product_id" id="product_id" value="{{ old('product_id') }}">

                                    <div id="selected_product" class="mt-2 d-none">
                                        <div class="alert alert-primary mb-0">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <div class="fw-semibold" id="selected_product_name"></div>
                                                    <small class="text-muted" id="selected_product_barcode"></small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger"id="clear_product">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <small class="text-muted">
                                        Ketik nama produk atau barcode, lalu gunakan ↑ ↓ dan Enter.
                                    </small>

                                </div>

                                {{-- ======================================
                                    TYPE
                                ======================================= --}}
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Tipe
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select name="type" class="form-select" required>
                                        <option value="">-- Pilih --</option>
                                        <option value="in" @selected(old('type') === 'in')>Stock Masuk (IN)</option>
                                        <option value="out" @selected(old('type') === 'out')>Stock Keluar (OUT)</option>
                                    </select>
                                </div>
                                {{-- ======================================
                                    QTY
                                ======================================= --}}
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Qty
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" name="qty" id="adjustment_qty" class="form-control" value="{{ old('qty') }}" min="1" required>
                                </div>

                                <div id="stock_preview" class="col-12 mb-3 d-none" >
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="row text-center">
                                                <div class="col-md-4">
                                                    <div class="text-muted small">
                                                        Stock Saat Ini
                                                    </div>
                                                    <div class="fs-3 fw-bold" id="stock_before" >0</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-muted small">Adjustment</div>
                                                    <div class="fs-3 fw-bold" id="stock_adjustment" >0</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-muted small">Stock Setelah</div>
                                                    <div class="fs-3 fw-bold text-primary" id="stock_after" >0</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- ======================================
                                    NOTE
                                ======================================= --}}

                                <div class="col-12 mb-3">
                                    <label class="form-label">Alasan / Keterangan
                                        <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="note" class="form-control" rows="3" maxlength="500" required
                                        placeholder="Contoh: Stock opname, barang rusak, barang hilang, selisih stok, dll.">
                                        {{ old('note') }}
                                    </textarea>
                                </div>
                            </div>
                            {{-- ==================================================
                                WARNING
                            =================================================== --}}
                            <div class="alert alert-warning">
                                <div class="d-flex">
                                    <div class="me-2">
                                        <i class="fas fa-triangle-exclamation"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">Perhatian</div>
                                        <div>
                                            Stock Adjustment akan langsung mengubah
                                            stok produk dan dicatat sebagai histori
                                            pergerakan stok.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- ==================================================
                                BUTTON
                            =================================================== --}}
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('stock-movements.index') }}" class="btn btn-outline-secondary">
                                    Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    Simpan Adjustment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>

$(function () {

    let searchTimer = null;

    let selectedIndex = -1;

    let products = [];


    const searchInput =
        $('#product_search');

    const resultsBox =
        $('#product_results');


    // ==========================================================
    // SEARCH PRODUCT
    // ==========================================================

    searchInput.on('input', function () {

        let keyword =
            $(this).val().trim();


        selectedIndex = -1;


        clearTimeout(
            searchTimer
        );


        if (keyword.length < 1) {

            resultsBox
                .addClass('d-none')
                .empty();

            return;

        }


        searchTimer = setTimeout(
            function () {

                $.ajax({

                    url:
                        "{{ route('stock-adjustments.search-products') }}",

                    type:
                        'GET',

                    data: {
                        search: keyword
                    },

                    dataType:
                        'json',

                    success:
                        function (response) {

                            products =
                                response.products || [];

                            renderProducts();

                        },

                    error:
                        function (xhr) {

                            console.error(
                                'Product search error:',
                                xhr
                            );

                            products = [];

                            renderProducts();

                        }

                });

            },
            250
        );

    });


    // ==========================================================
    // RENDER RESULT
    // ==========================================================

    function renderProducts()
    {
        resultsBox.empty();


        if (!products.length) {

            resultsBox
                .removeClass('d-none')
                .html(
                    '<div class="product-search-empty">' +
                    'Produk tidak ditemukan.' +
                    '</div>'
                );

            return;

        }


        products.forEach(
            function (product, index) {

                let item = $(
                    '<div>'
                );

                item.addClass(
                    'product-search-item'
                );

                item.attr(
                    'data-index',
                    index
                );


                item.html(

                    '<div class="product-name">' +
                        escapeHtml(
                            product.name
                        ) +
                    '</div>' +

                    '<div class="product-meta">' +

                        'Barcode: ' +
                        escapeHtml(
                            product.barcode || '-'
                        ) +

                        ' &nbsp; | &nbsp; ' +

                        'Stock: ' +
                        Number(
                            product.stock
                        ).toLocaleString('id-ID') +

                    '</div>'

                );


                item.on(
                    'click',
                    function () {

                        selectProduct(
                            index
                        );

                    }
                );


                resultsBox.append(
                    item
                );

            }
        );


        resultsBox.removeClass(
            'd-none'
        );
    }


    // ==========================================================
    // KEYBOARD NAVIGATION
    // ==========================================================

    searchInput.on(
        'keydown',
        function (e) {

            if (
                resultsBox.hasClass(
                    'd-none'
                )
            ) {

                return;

            }


            if (!products.length) {

                return;

            }


            // ==============================================
            // ARROW DOWN
            // ==============================================

            if (e.key === 'ArrowDown') {

                e.preventDefault();


                selectedIndex++;

                if (
                    selectedIndex >=
                    products.length
                ) {

                    selectedIndex = 0;

                }


                updateActiveItem();

            }


            // ==============================================
            // ARROW UP
            // ==============================================

            else if (
                e.key === 'ArrowUp'
            ) {

                e.preventDefault();


                selectedIndex--;

                if (
                    selectedIndex < 0
                ) {

                    selectedIndex =
                        products.length - 1;

                }


                updateActiveItem();

            }


            // ==============================================
            // ENTER
            // ==============================================

            else if (
                e.key === 'Enter'
            ) {

                e.preventDefault();


                if (
                    selectedIndex >= 0
                ) {

                    selectProduct(
                        selectedIndex
                    );

                }

            }


            // ==============================================
            // ESC
            // ==============================================

            else if (
                e.key === 'Escape'
            ) {

                resultsBox
                    .addClass('d-none')
                    .empty();

                selectedIndex = -1;

            }

        }
    );


    // ==========================================================
    // UPDATE ACTIVE ITEM
    // ==========================================================

    function updateActiveItem()
    {
        resultsBox
            .find('.product-search-item')
            .removeClass('active');


        let activeItem =
            resultsBox.find(
                '.product-search-item'
            ).eq(
                selectedIndex
            );


        activeItem.addClass(
            'active'
        );


        if (
            activeItem.length
        ) {

            activeItem[0]
                .scrollIntoView({
                    block: 'nearest'
                });

        }

    }


    // ==========================================================
    // SELECT PRODUCT
    // ==========================================================

    function selectProduct(
        index
    ) {

        let product =
            products[index];


        if (!product) {

            return;

        }


        // ==============================================
        // SET HIDDEN ID
        // ==============================================

        $('#product_id').val(
            product.id
        );


        // ==============================================
        // DISPLAY SELECTED PRODUCT
        // ==============================================

        $('#selected_product_name')
            .text(
                product.name
            );


        $('#selected_product_barcode')
            .text(
                'Barcode: ' +
                (product.barcode || '-')
            );


        $('#selected_product')
            .removeClass('d-none');


        // ==============================================
        // STOCK PREVIEW
        // ==============================================

        $('#stock_before')
            .text(
                Number(
                    product.stock
                ).toLocaleString('id-ID')
            );


        $('#stock_preview')
            .removeClass('d-none');


        // ==============================================
        // CLEAR SEARCH
        // ==============================================

        searchInput.val(
            product.name
        );


        resultsBox
            .addClass('d-none')
            .empty();


        selectedIndex = -1;


        // ==============================================
        // UPDATE PREVIEW
        // ==============================================

        updateStockPreview();


        // ==============================================
        // FOCUS QTY
        // ==============================================

        $('#adjustment_qty')
            .focus()
            .select();

    }


    // ==========================================================
    // CLEAR PRODUCT
    // ==========================================================

    $('#clear_product').on(
        'click',
        function () {

            $('#product_id').val('');

            $('#product_search').val('');

            $('#selected_product')
                .addClass('d-none');

            $('#stock_preview')
                .addClass('d-none');

            $('#stock_before')
                .text('0');

            $('#stock_after')
                .text('0');

            $('#stock_adjustment')
                .text('0');

            $('#adjustment_qty')
                .val('');

            products = [];

            selectedIndex = -1;

            searchInput.focus();

        }
    );


    // ==========================================================
    // TYPE / QTY CHANGE
    // ==========================================================

    $('select[name="type"], #adjustment_qty')
        .on(
            'input change',
            function () {

                updateStockPreview();

            }
        );


    // ==========================================================
    // STOCK PREVIEW CALCULATION
    // ==========================================================

    function updateStockPreview()
    {
        let productId =
            $('#product_id').val();


        if (!productId) {

            return;

        }


        let product =
            products.find(
                function (item) {

                    return String(
                        item.id
                    ) === String(
                        productId
                    );

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Jika product tidak ditemukan dalam array pencarian
        |--------------------------------------------------------------------------
        */

        if (!product) {

            return;

        }


        let stock =
            Number(
                product.stock
            ) || 0;


        let qty =
            Number(
                $('#adjustment_qty').val()
            ) || 0;


        let type =
            $('select[name="type"]').val();


        let after =
            stock;


        if (
            type === 'in'
        ) {

            after =
                stock + qty;

        }
        else if (
            type === 'out'
        ) {

            after =
                stock - qty;

        }


        $('#stock_before')
            .text(
                stock.toLocaleString(
                    'id-ID'
                )
            );


        $('#stock_adjustment')
            .text(

                type === 'out'
                    ? '-' +
                        qty.toLocaleString(
                            'id-ID'
                        )
                    : '+' +
                        qty.toLocaleString(
                            'id-ID'
                        )

            );


        $('#stock_after')
            .text(
                after.toLocaleString(
                    'id-ID'
                )
            );


        // ==============================================
        // WARNING JIKA STOCK AKAN NEGATIF
        // ==============================================

        if (
            after < 0
        ) {

            $('#stock_after')
                .removeClass(
                    'text-primary'
                )
                .addClass(
                    'text-danger'
                );

        }
        else {

            $('#stock_after')
                .removeClass(
                    'text-danger'
                )
                .addClass(
                    'text-primary'
                );

        }

    }


    // ==========================================================
    // CLICK OUTSIDE
    // ==========================================================

    $(document).on(
        'click',
        function (e) {

            if (
                !$(e.target).closest(
                    '#product_search, #product_results'
                ).length
            ) {

                resultsBox
                    .addClass('d-none');

            }

        }
    );


    // ==========================================================
    // ESCAPE HTML
    // ==========================================================

    function escapeHtml(
        value
    ) {

        return $('<div>')
            .text(
                value ?? ''
            )
            .html();

    }

});

$('form').on('submit', function (e) {

    let productId =
        $('#product_id').val();

    let type =
        $('select[name="type"]').val();

    let qty =
        Number(
            $('#adjustment_qty').val()
        ) || 0;


    if (!productId) {

        e.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Produk belum dipilih',
            text: 'Silakan pilih produk terlebih dahulu.'
        });

        return;

    }


    if (!type) {

        e.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Tipe belum dipilih',
            text: 'Silakan pilih Stock IN atau OUT.'
        });

        return;

    }


    if (qty <= 0) {

        e.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Qty tidak valid',
            text: 'Qty harus lebih dari 0.'
        });

        return;

    }

});

</script>

@endsection