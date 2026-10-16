@extends('layouts.app')

@section('title', 'Kasir POS')

@section('content')
<div class="page-header d-print-none">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Kasir POS</h2>
                <div class="text-secondary mt-1">
                    Pilih produk untuk memulai pesanan.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-fluid">
        <div class="row g-3">

            {{-- KATALOG PRODUK --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Produk</h3>
                    </div>

                    <div class="card-body border-bottom">
                        <input
                            type="text"
                            id="search-product"
                            class="form-control"
                            placeholder="Cari nama atau barcode produk..."
                        >
                    </div>

                    <div class="card-body">
                        <div class="row g-3" id="product-list">
                            @forelse($products as $product)
                                <div
                                    class="col-6 col-md-4 col-xl-3 product-card"
                                    data-name="{{ strtolower($product->name) }}"
                                    data-barcode="{{ strtolower($product->barcode) }}"
                                >
                                    <button
                                        type="button"
                                        class="card card-link w-100 text-start h-100 btn-add-product"
                                        data-id="{{ $product->id }}"
                                        data-name="{{ $product->name }}"
                                        data-price="{{ $product->price }}"
                                        data-has-variants="{{ $product->variants->isNotEmpty() ? 1 : 0 }}"
                                        data-has-options="{{ $product->optionGroups->isNotEmpty() ? 1 : 0 }}"
                                    >
                                        <div class="card-body">
                                            <div class="fw-bold mb-2">
                                                {{ $product->name }}
                                            </div>

                                            <div class="text-secondary small mb-2">
                                                {{ $product->category->name ?? 'Tanpa kategori' }}
                                            </div>

                                            <div class="text-primary fw-bold">
                                                Rp {{ number_format($product->price, 0, ',', '.') }}
                                            </div>

                                            @if($product->variants->isNotEmpty())
                                                <span class="badge bg-blue-lt mt-2">Ada varian</span>
                                            @endif

                                            @if($product->optionGroups->isNotEmpty())
                                                <span class="badge bg-purple-lt mt-2">Ada opsi</span>
                                            @endif
                                        </div>
                                    </button>
                                </div>
                            @empty
                                <div class="col-12 text-center text-secondary py-5">
                                    Belum ada produk aktif.
                                </div>
                            @endforelse
                        </div>

                        <div id="empty-search" class="text-center text-secondary py-4 d-none">
                            Produk tidak ditemukan.
                        </div>
                    </div>
                </div>
            </div>

            {{-- KERANJANG --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Pesanan Baru</h3>
                        <div class="card-actions">
                            <button type="button" id="clear-cart"
                                    class="btn btn-sm btn-outline-danger">
                                Kosongkan
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Tipe Pesanan</label>
                            <select id="order-type" class="form-select">
                                <option value="dine_in">Dine-in</option>
                                <option value="takeaway">Takeaway</option>
                            </select>
                        </div>

                        <div class="mb-3" id="table-wrapper">
                            <label class="form-label">Pilih Meja</label>
                            <select id="table-id" class="form-select">
                                <option value="">-- Pilih meja --</option>
                                    @foreach($mejas as $meja)
                                        <option
                                            value="{{ $meja->id }}"
                                            @selected((string) request('meja_id') === (string) $meja->id)
                                        >
                                            {{ $meja->nomor }}
                                            @if($meja->nama)
                                                - {{ $meja->nama }}
                                            @endif
                                            ({{ $meja->kapasitas }} kursi)
                                        </option>
                                    @endforeach
                            </select>
                        </div>

                        <div id="cart-items">
                            <div class="text-center text-secondary py-4" id="cart-empty">
                                <i class="ti ti-shopping-cart fs-1"></i>
                                <div class="mt-2">Keranjang masih kosong.</div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Jumlah item</span>
                            <strong id="cart-count">0</strong>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total sementara</span>
                            <strong class="text-primary fs-2" id="cart-total">
                                Rp 0
                            </strong>
                        </div>

                        <button type="button" id="btn-save-order"
                                class="btn btn-primary w-100 mt-3" disabled>
                            <i class="ti ti-device-floppy me-1"></i>
                            Simpan Pesanan
                        </button>

                        <div class="form-hint mt-2">
                            Penyimpanan pesanan akan diaktifkan pada tahap berikutnya.
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


<div class="modal fade" id="product-config-modal" tabindex="-1"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="config-product-name">
                    Konfigurasi Produk
                </h5>
                <button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="config-product-id">

                <div id="config-variant-wrapper" class="mb-3 d-none">
                    <label class="form-label">Pilih Varian</label>
                    <div id="config-variants"></div>
                </div>

                <div id="config-options-wrapper"></div>

                <div class="mb-3">
                    <label for="config-quantity" class="form-label">
                        Jumlah
                    </label>
                    <input type="number" id="config-quantity" class="form-control" min="1" value="1">
                    <div id="config-stock-warning" class="alert alert-danger py-2 d-none" role="alert"></div>
                </div>

                <div class="mb-3">
                    <label for="config-notes" class="form-label">
                        Catatan
                    </label>
                    <textarea id="config-notes" class="form-control"
                              rows="2"
                              placeholder="Contoh: gula sedikit"></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-secondary">Harga per item</span>
                    <strong id="config-unit-price">Rp 0</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <span class="fw-bold">Subtotal</span>
                    <strong class="text-primary" id="config-subtotal">Rp 0</strong>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary"
                        id="btn-add-configured-product">
                    Tambahkan ke Keranjang
                </button>
            </div>
        </div>
    </div>
</div>

@endsection


@section('scripts')
<script>
$(function () {
    const products = {{ Illuminate\Support\Js::from(
    $products->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'stock' => (int) $product->stock,
            'track_stock' => (bool) $product->track_stock,
            'variants' => $product->variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'price' => (float) $variant->price,
                ];
            })->values(),
            'optionGroups' => $product->optionGroups->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'options' => $group->options->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'name' => $option->name,
                            'price_adjustment' => (float) $option->price_adjustment,
                        ];
                    })->values(),
                ];
            })->values(),
        ];
    })->values()
) }};

    const productMap = {};
    products.forEach(function (product) {
        productMap[String(product.id)] = product;
    });

    const cart = {};
    let currentProduct = null;
    let configModal = null;

    const modalElement = document.getElementById('product-config-modal');

    if (modalElement && window.bootstrap) {
        configModal = bootstrap.Modal.getOrCreateInstance(modalElement);
    }

    function rupiah(value) {
        return 'Rp ' + Number(value).toLocaleString('id-ID');
    }

    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    function getSelectedOptions() {
        const selected = [];

        $('#config-options-wrapper input[type="checkbox"]:checked').each(function () {
            const option = $(this).data('option');
            selected.push(option);
        });

        return selected;
    }

    function getSelectedVariant() {
        const variantId = $('#config-variants input[name="config-variant"]:checked').val();

        if (!variantId || !currentProduct) {
            return null;
        }

        return currentProduct.variants.find(function (variant) {
            return String(variant.id) === String(variantId);
        }) || null;
    }


    function calculateConfig() {
        if (!currentProduct) return;

        const variant = getSelectedVariant();
        const options = getSelectedOptions();

        const basePrice = variant
            ? Number(variant.price)
            : Number(currentProduct.price);

        const optionsPrice = options.reduce(function (sum, option) {
            return sum + Number(option.price_adjustment);
        }, 0);

        const unitPrice = basePrice + optionsPrice;

        const rawQuantity = Number($('#config-quantity').val());
        const quantity = Number.isInteger(rawQuantity) ? rawQuantity : 0;

        $('#config-unit-price').text(rupiah(unitPrice));
        $('#config-subtotal').text(
            rupiah(unitPrice * Math.max(0, quantity))
        );

        let stockValid = quantity >= 1 && quantity <= 999;
        let warningMessage = '';

        if (!stockValid) {
            warningMessage = 'Jumlah harus antara 1 sampai 999.';
        }

        if (
            stockValid &&
            currentProduct.track_stock &&
            quantity > Number(currentProduct.stock)
        ) {
            stockValid = false;

            warningMessage =
                'Stok ' + currentProduct.name +
                ' hanya tersedia ' + currentProduct.stock +
                ' unit. Kurangi jumlah pesanan.';
        }

        const warning = $('#config-stock-warning');

        if (warningMessage) {
            warning.text(warningMessage).removeClass('d-none');
        } else {
            warning.addClass('d-none').text('');
        }

        $('#btn-add-configured-product').prop('disabled', !stockValid);
    }


    function openProductConfig(product) {
        currentProduct = product;

        $('#config-product-id').val(product.id);
        $('#config-product-name').text(product.name);
        $('#config-quantity').val(1);
        $('#config-notes').val('');

        const variantWrapper = $('#config-variant-wrapper');
        const variantsContainer = $('#config-variants');

        variantsContainer.empty();

        if (product.variants.length > 0) {
            variantWrapper.removeClass('d-none');

            product.variants.forEach(function (variant, index) {
                const label = $('<label class="form-check border rounded p-3 mb-2 d-flex gap-2"></label>');
                const radio = $('<input class="form-check-input mt-1" type="radio" name="config-variant">')
                    .val(variant.id);

                const details = $('<div class="flex-fill"></div>');
                details.append($('<div class="fw-bold"></div>').text(variant.name));
                details.append($('<div class="text-secondary small"></div>').text(rupiah(variant.price)));

                label.append(radio, details);
                variantsContainer.append(label);

                if (index === 0) {
                    radio.prop('checked', true);
                }
            });
        } else {
            variantWrapper.addClass('d-none');
        }

        const optionsWrapper = $('#config-options-wrapper');
        optionsWrapper.empty();

        product.optionGroups.forEach(function (group) {
            const section = $('<div class="mb-3"></div>');
            section.append(
                $('<label class="form-label"></label>').text(group.name)
            );

            group.options.forEach(function (option) {
                const label = $('<label class="form-check border rounded p-2 mb-2 d-flex gap-2"></label>');

                const checkbox = $('<input class="form-check-input mt-1" type="checkbox">');
                checkbox.attr('data-option-id', option.id);
                checkbox.data('option', option);

                const details = $('<div class="flex-fill"></div>');
                details.append($('<div></div>').text(option.name));

                if (Number(option.price_adjustment) !== 0) {
                    const prefix = Number(option.price_adjustment) > 0 ? '+' : '';
                    details.append(
                        $('<div class="text-secondary small"></div>')
                            .text(prefix + rupiah(option.price_adjustment))
                    );
                }

                label.append(checkbox, details);
                section.append(label);
            });

            optionsWrapper.append(section);
        });

        calculateConfig();

        if (configModal) {
            configModal.show();
        } else {
            alert('Modal Bootstrap tidak ditemukan. Periksa layout aplikasi.');
        }
    }

    $('#search-product').on('input', function () {
        const keyword = $(this).val().toLowerCase().trim();
        let visible = 0;

        $('.product-card').each(function () {
            const name = String($(this).data('name'));
            const barcode = String($(this).data('barcode'));
            const match = name.includes(keyword) || barcode.includes(keyword);

            $(this).toggle(match);
            if (match) visible++;
        });

        $('#empty-search').toggleClass('d-none', visible > 0);
    });

    $(document).on('click', '.btn-add-product', function () {
        const product = productMap[String($(this).data('id'))];

        if (!product) {
            alert('Data produk tidak ditemukan.');
            return;
        }

        openProductConfig(product);
    });

    $(document).on(
        'change',
        '#config-variants input, #config-options-wrapper input',
        calculateConfig
    );

    $('#config-quantity').on('input change', calculateConfig);

    $('#btn-add-configured-product').on('click', function () {
        if (!currentProduct) return;
        calculateConfig();

        const requestedQuantity = Number($('#config-quantity').val());

        if (
            !Number.isInteger(requestedQuantity) ||
            requestedQuantity < 1 ||
            requestedQuantity > 999
        ) {
            alert('Jumlah harus antara 1 sampai 999.');
            return;
        }

        if (
            currentProduct.track_stock &&
            requestedQuantity > Number(currentProduct.stock)
        ) {
            alert(
                'Stok ' + currentProduct.name +
                ' hanya tersedia ' + currentProduct.stock + ' unit.'
            );
            return;
        }
        if (currentProduct.variants.length > 0 && !getSelectedVariant()) {
            alert('Silakan pilih varian produk.');
            return;
        }

        const variant = getSelectedVariant();
        const options = getSelectedOptions();

        const basePrice = variant
            ? Number(variant.price)
            : Number(currentProduct.price);

        const unitPrice = basePrice + options.reduce(function (sum, option) {
            return sum + Number(option.price_adjustment);
        }, 0);

        const quantity = Math.max(
            1,
            parseInt($('#config-quantity').val(), 10) || 1
        );

        const notes = $('#config-notes').val().trim();

        const optionIds = options
            .map(option => Number(option.id))
            .sort((a, b) => a - b);

        const signature = [
            currentProduct.id,
            variant ? variant.id : 0,
            optionIds.join(','),
            notes
        ].join('|');

        if (cart[signature]) {
            cart[signature].quantity += quantity;
        } else {
            cart[signature] = {
                key: signature,
                product_id: currentProduct.id,
                product_name: currentProduct.name,
                variant_id: variant ? variant.id : null,
                variant_name: variant ? variant.name : null,
                options: options.map(function (option) {
                    return {
                        id: option.id,
                        name: option.name,
                        price_adjustment: Number(option.price_adjustment)
                    };
                }),
                quantity: quantity,
                unit_price: unitPrice,
                notes: notes
            };
        }

        renderCart();

        if (configModal) {
            configModal.hide();
        }
    });

    function renderCart() {
        const container = $('#cart-items');
        container.empty();

        const items = Object.values(cart);
        let count = 0;
        let total = 0;

        if (items.length === 0) {
            container.html(
                '<div class="text-center text-secondary py-4">' +
                '<i class="ti ti-shopping-cart fs-1"></i>' +
                '<div class="mt-2">Keranjang masih kosong.</div></div>'
            );
        }

        items.forEach(function (item) {
            const subtotal = item.unit_price * item.quantity;
            count += item.quantity;
            total += subtotal;

            const row = $('<div class="border-bottom py-3"></div>');
            const header = $('<div class="d-flex justify-content-between gap-2"></div>');
            const title = $('<div class="fw-bold"></div>').text(item.product_name);
            const remove = $('<button type="button" class="btn btn-sm btn-ghost-danger"><i class="ti ti-trash"></i></button>');

            remove.on('click', function () {
                delete cart[item.key];
                renderCart();
            });

            header.append(title, remove);
            row.append(header);

            if (item.variant_name) {
                row.append(
                    $('<div class="small text-secondary"></div>')
                        .text('Varian: ' + item.variant_name)
                );
            }

            if (item.options.length) {
                row.append(
                    $('<div class="small text-secondary"></div>')
                        .text('Opsi: ' + item.options.map(o => o.name).join(', '))
                );
            }

            if (item.notes) {
                row.append(
                    $('<div class="small text-secondary"></div>')
                        .text('Catatan: ' + item.notes)
                );
            }

            row.append(
                $('<div class="text-secondary small mb-2"></div>')
                    .text(rupiah(item.unit_price) + ' / item')
            );

            const footer = $('<div class="d-flex justify-content-between align-items-center"></div>');
            const controls = $('<div class="btn-group btn-group-sm"></div>');

            const minus = $('<button type="button" class="btn btn-outline-secondary">−</button>');
            const qty = $('<span class="px-2 align-self-center"></span>').text(item.quantity);
            const plus = $('<button type="button" class="btn btn-outline-secondary">+</button>');

            minus.on('click', function () {
                item.quantity--;

                if (item.quantity <= 0) {
                    delete cart[item.key];
                }

                renderCart();
            });

            plus.on('click', function () {
                item.quantity++;
                renderCart();
            });

            controls.append(minus, qty, plus);

            footer.append(
                controls,
                $('<strong></strong>').text(rupiah(subtotal))
            );

            row.append(footer);
            container.append(row);
        });

        $('#cart-count').text(count);
        $('#cart-total').text(rupiah(total));
        $('#btn-save-order').prop('disabled', items.length === 0);
    }

    function toggleTable() {
        const dineIn = $('#order-type').val() === 'dine_in';
        $('#table-wrapper').toggle(dineIn);

        if (!dineIn) {
            $('#table-id').val('');
        }
    }

    $('#order-type').on('change', toggleTable);

    $('#clear-cart').on('click', function () {
        Object.keys(cart).forEach(function (key) {
            delete cart[key];
        });
        renderCart();
    });

    
    $('#btn-save-order').on('click', function () {
        const orderType = $('#order-type').val();
        const mejaId = $('#table-id').val();
        const items = Object.values(cart);

        if (items.length === 0) {
            alert('Keranjang masih kosong.');
            return;
        }

        if (orderType === 'dine_in' && !mejaId) {
            alert('Silakan pilih meja untuk pesanan dine-in.');
            $('#table-id').focus();
            return;
        }

        const payload = {
            _token: '{{ csrf_token() }}',
            order_type: orderType,
            meja_id: orderType === 'dine_in' ? mejaId : '',
            items: items.map(function (item) {
                return {
                    product_id: item.product_id,
                    variant_id: item.variant_id || '',
                    option_ids: item.options.map(function (option) {
                        return option.id;
                    }),
                    quantity: item.quantity,
                    notes: item.notes || ''
                };
            })
        };

        const button = $(this);
        const originalHtml = button.html();

        button.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

        $.ajax({
            url: '{{ route("pos.orders.store") }}',
            type: 'POST',
            data: payload,
            dataType: 'json',

            success: function (response) {
                if (response.status === 'success') {
                    alert(
                        response.message +
                        '\nNomor pesanan: ' + response.order_number
                    );

                    window.location.assign(response.redirect);
                }
            },

            error: function (xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors || {};
                    const messages = Object.values(errors).flat();

                    alert(messages.length
                        ? messages.join('\n')
                        : 'Data pesanan tidak valid.');
                } else {
                    alert(
                        xhr.responseJSON?.message ||
                        'Pesanan gagal disimpan. Periksa kembali dan coba lagi.'
                    );
                }
            },

            complete: function () {
                button.prop('disabled', false).html(originalHtml);
            }
        });
    });


    toggleTable();
    renderCart();
});
</script>
@endsection