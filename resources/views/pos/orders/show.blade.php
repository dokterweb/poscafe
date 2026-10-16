@extends('layouts.app')

@section('title', 'Detail Pesanan')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Detail Pesanan</h2>
                <div class="text-secondary mt-1">
                    {{ $order->order_number }}
                    @if($order->order_type === 'dine_in')
                        · Meja {{ $order->meja->nomor ?? '-' }}
                    @else
                        · Takeaway
                    @endif
                </div>
            </div>

            <div class="col-auto ms-auto">
                <a href="{{ route('pos.index') }}"
                   class="btn btn-outline-secondary">
                    Kembali ke Kasir
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        
        <div class="card">
           
            <div class="card-header">
                <h3 class="card-title">Item Pesanan</h3>

                <span class="badge bg-orange-lt ms-auto me-2">
                    {{ $order->status }}
                </span>

                @if($order->status === 'open')
                    <button type="button"
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalTambahItem">
                        <i class="fas fa-plus me-1"></i>
                        Tambah Produk
                    </button>
                @endif
                @if($order->status === 'open')
                    <button type="button"
                            class="btn btn-success btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalPembayaran">
                        <i class="fas fa-money-bill-wave me-1"></i>
                        Bayar Pesanan
                    </button>
                @endif
                @if($order->status === 'paid')
                    <a
                        href="{{ route('pos.orders.receipt', $order->id) }}"
                        class="btn btn-success"
                        target="_blank"
                        rel="noopener"
                    >
                        <i class="ti ti-printer"></i>
                        Cetak Struk
                    </a>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Varian / Opsi</th>
                            <th style="width: 110px">Jumlah</th>
                            <th class="text-end">Harga</th>
                            <th class="text-end">Subtotal</th>
                            <th style="width: 150px">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($order->items as $item)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $item->product_name }}</div>
                                    @if($item->notes)
                                        <div class="text-secondary small">
                                            Catatan: {{ $item->notes }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    @if($item->variant_name)
                                        <div>{{ $item->variant_name }}</div>
                                    @endif

                                    @foreach(($item->selected_options ?? []) as $option)
                                        <div class="text-secondary small">
                                            {{ $option['name'] ?? '' }}
                                        </div>
                                    @endforeach

                                    @if(!$item->variant_name && empty($item->selected_options))
                                        -
                                    @endif
                                </td>

                                <td>
                                    @if($order->status === 'open')
                                        <input
                                            type="number"
                                            class="form-control form-control-sm item-quantity"
                                            min="1"
                                            max="999"
                                            value="{{ $item->quantity }}"
                                            data-item-id="{{ $item->id }}"
                                            style="width: 85px"
                                        >
                                    @else
                                        {{ $item->quantity }}
                                    @endif
                                </td>

                                <td class="text-end">
                                    Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                </td>

                                <td class="text-end fw-bold">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>

                                <td>
                                    @if($order->status === 'open')
                                        <div class="d-flex gap-1">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-primary btn-update-item"
                                                data-item-id="{{ $item->id }}"
                                            >
                                                <i class="fas fa-check"></i>
                                            </button>

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-danger btn-delete-item"
                                                data-item-id="{{ $item->id }}"
                                                data-product-name="{{ $item->product_name }}"
                                            >
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    Belum ada item pesanan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Subtotal</th>
                            <th colspan="2" class="text-end">
                                Rp {{ number_format($order->subtotal, 0, ',', '.') }}
                            </th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">Total</th>
                            <th colspan="2" class="text-end text-primary">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>


        @if($order->status === 'open')
        <div class="modal modal-blur fade"
            id="modalTambahItem"
            tabindex="-1"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Produk ke Pesanan</h5>
                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Tutup"></button>
                    </div>

                    <form id="formTambahItem">
                        @csrf

                        <div class="modal-body">
                            <div id="tambah-item-error"></div>

                            <div class="mb-3">
                                <label class="form-label">Cari Produk</label>
                                <input type="text"
                                    id="cariProdukItem"
                                    class="form-control"
                                    placeholder="Ketik nama produk...">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Produk <span class="text-danger">*</span></label>
                                <select id="item_product_id"
                                        name="product_id"
                                        class="form-select"
                                        required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">
                                            {{ $product->name }} — Rp {{ number_format($product->price, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3 d-none" id="variantContainer">
                                <label class="form-label">Varian <span class="text-danger">*</span></label>
                                <select id="item_variant_id"
                                        name="variant_id"
                                        class="form-select">
                                    <option value="">-- Pilih Varian --</option>
                                </select>
                            </div>

                            <div id="optionGroupsContainer"></div>

                            <div id="item-stock-info" class="alert alert-info py-2 mb-3 d-none">
                                <div class="d-flex justify-content-between">
                                    <span>Stok tersedia</span>
                                    <strong id="item-stock-available">0</strong>
                                </div>
                            </div>

                            <div id="item-stock-warning"
                                class="alert alert-danger py-2 mb-3 d-none"
                                role="alert">
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Jumlah</label>
                                    <input type="number"
                                        name="quantity"
                                        id="item_quantity"
                                        class="form-control"
                                        min="1"
                                        max="999"
                                        value="1"
                                        required>
                                </div>

                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Catatan</label>
                                    <input type="text"
                                        name="notes"
                                        id="item_notes"
                                        class="form-control"
                                        maxlength="1000"
                                        placeholder="Contoh: gula sedikit">
                                </div>
                            </div>

                            <div class="alert alert-info mb-0">
                                <div class="d-flex justify-content-between">
                                    <span>Estimasi harga satuan</span>
                                    <strong id="itemUnitPrice">Rp 0</strong>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <span>Estimasi subtotal</span>
                                    <strong id="itemSubtotal">Rp 0</strong>
                                </div>
                                <small class="text-secondary">
                                    Harga akhir akan dihitung ulang oleh server.
                                </small>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">
                                Batal
                            </button>

                            <button type="submit"
                                    class="btn btn-primary"
                                    id="btnSimpanItem">
                                <i class="fas fa-plus me-1"></i>
                                Tambahkan ke Pesanan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif


        @if($order->status === 'open')
            @section('scripts')
            <script>
            $(function () {
                const csrfToken = '{{ csrf_token() }}';

                function sendItemRequest(url, method, data = {}) {
                    return $.ajax({
                        url: url,
                        type: method,
                        data: {
                            ...data,
                            _token: csrfToken,
                            _method: method !== 'POST' ? method : undefined
                        },
                        dataType: 'json'
                    });
                }

                $('.btn-update-item').on('click', function () {
                    const button = $(this);
                    const itemId = button.data('item-id');
                    const quantity = button.closest('tr')
                        .find('.item-quantity').val();

                    if (!quantity || Number(quantity) < 1 || Number(quantity) > 999) {
                        alert('Jumlah harus antara 1 dan 999.');
                        return;
                    }

                    button.prop('disabled', true);

                    sendItemRequest(
                        '{{ route("pos.orders.items.update", [$order->id, "__ITEM__"]) }}'
                            .replace('__ITEM__', itemId),
                        'PATCH',
                        { quantity: quantity }
                    ).done(function (response) {
                        window.location.assign(response.redirect);
                    }).fail(function (xhr) {
                        alert(xhr.responseJSON?.message
                            || xhr.responseJSON?.errors?.quantity?.[0]
                            || 'Gagal memperbarui item.');
                        button.prop('disabled', false);
                    });
                });

                $('.btn-delete-item').on('click', function () {
                    const button = $(this);
                    const itemId = button.data('item-id');
                    const productName = button.data('product-name');

                    if (!confirm('Hapus ' + productName + ' dari pesanan ini?')) {
                        return;
                    }

                    button.prop('disabled', true);

                    sendItemRequest(
                        '{{ route("pos.orders.items.destroy", [$order->id, "__ITEM__"]) }}'
                            .replace('__ITEM__', itemId),
                        'DELETE'
                    ).done(function (response) {
                        window.location.assign(response.redirect);
                    }).fail(function (xhr) {
                        alert(xhr.responseJSON?.message
                            || 'Gagal menghapus item.');
                        button.prop('disabled', false);
                    });
                });
            });

                            
                const productsForOrder = @json($productsForOrder);

                const rupiahItem = value =>
                    'Rp ' + new Intl.NumberFormat('id-ID', {
                        maximumFractionDigits: 0
                    }).format(value || 0);

                function escapeItemHtml(value) {
                    return $('<div>').text(value ?? '').html();
                }

                function resetItemConfig() {
                    $('#variantContainer').addClass('d-none');
                    $('#item_variant_id')
                        .empty()
                        .append('<option value="">-- Pilih Varian --</option>');

                    $('#optionGroupsContainer').empty();
                    $('#item_quantity').val(1);
                    $('#item_notes').val('');
                    $('#itemUnitPrice').text('Rp 0');
                    $('#itemSubtotal').text('Rp 0');
                }

             
                function updateItemEstimate() {
                    const product = productsForOrder.find(
                        p => String(p.id) === String($('#item_product_id').val())
                    );

                    const warning = $('#item-stock-warning');
                    const stockInfo = $('#item-stock-info');
                    const submitButton = $('#btnSimpanItem');

                    warning.addClass('d-none').text('');
                    stockInfo.addClass('d-none');

                    if (!product) {
                        $('#itemUnitPrice, #itemSubtotal').text('Rp 0');
                        submitButton.prop('disabled', true);
                        return;
                    }

                    const variantId = $('#item_variant_id').val();

                    const variant = product.variants.find(
                        v => String(v.id) === String(variantId)
                    );

                    let unitPrice = variant ? variant.price : product.price;

                    $('#optionGroupsContainer input[name="option_ids[]"]:checked')
                        .each(function () {
                            const optionId = Number($(this).val());

                            product.option_groups.forEach(group => {
                                const option = group.options.find(
                                    o => Number(o.id) === optionId
                                );

                                if (option) {
                                    unitPrice += Number(option.price_adjustment);
                                }
                            });
                        });

                    const rawQuantity = $('#item_quantity').val();
                    const quantity = Number(rawQuantity);

                    $('#itemUnitPrice').text(rupiahItem(unitPrice));
                    $('#itemSubtotal').text(
                        rupiahItem(unitPrice * (Number.isFinite(quantity) ? quantity : 0))
                    );

                    let valid = Number.isInteger(quantity) && quantity >= 1 && quantity <= 999;

                    if (!valid) {
                        warning.text('Jumlah harus berupa bilangan bulat antara 1 dan 999.')
                            .removeClass('d-none');
                    }

                    if (product.track_stock) {
                        stockInfo.removeClass('d-none');

                        $('#item-stock-available').text(
                            Number(product.stock).toLocaleString('id-ID') + ' unit'
                        );

                        if (valid && quantity > Number(product.stock)) {
                            valid = false;

                            warning.text(
                                'Stok ' + product.name + ' hanya tersedia ' +
                                Number(product.stock).toLocaleString('id-ID') +
                                ' unit. Kurangi jumlah pesanan.'
                            ).removeClass('d-none');
                        }
                    }

                    submitButton.prop('disabled', !valid);
                }


                function renderItemConfig(product) {
                    resetItemConfig();

                    if (!product) return;

                    if (product.variants.length > 0) {
                        $('#variantContainer').removeClass('d-none');

                        product.variants.forEach(variant => {
                            $('#item_variant_id').append(
                                $('<option>', {
                                    value: variant.id,
                                    text: `${variant.name} — ${rupiahItem(variant.price)}`
                                })
                            );
                        });
                    }

                    product.option_groups.forEach((group, groupIndex) => {
                        const wrapper = $('<div class="mb-3"></div>');
                        wrapper.append(
                            $('<label class="form-label fw-bold"></label>')
                                .text(group.name)
                        );

                        group.options.forEach(option => {
                            const id = `item-option-${groupIndex}-${option.id}`;

                            const label = $('<label>', {
                                class: 'form-check mb-2',
                                for: id
                            });

                            const checkbox = $('<input>', {
                                type: 'checkbox',
                                class: 'form-check-input',
                                id: id,
                                name: 'option_ids[]',
                                value: option.id
                            });

                            const text = $('<span class="form-check-label"></span>')
                                .text(
                                    `${option.name} (+${rupiahItem(option.price_adjustment)})`
                                );

                            label.append(checkbox, text);
                            wrapper.append(label);
                        });

                        $('#optionGroupsContainer').append(wrapper);
                    });

                    updateItemEstimate();
                }

                $('#item_product_id').on('change', function () {
                    const product = productsForOrder.find(
                        p => String(p.id) === String(this.value)
                    );

                    renderItemConfig(product);
                });

                $('#cariProdukItem').on('input', function () {
                    const search = this.value.toLowerCase();

                    $('#item_product_id option').each(function (index) {
                        if (index === 0) return;

                        $(this).prop(
                            'hidden',
                            !$(this).text().toLowerCase().includes(search)
                        );
                    });
                });

                $('#item_variant_id, #item_quantity').on('change input', updateItemEstimate);

                $('#optionGroupsContainer').on(
                    'change',
                    'input[name="option_ids[]"]',
                    updateItemEstimate
                );

                
                $('#formTambahItem').on('submit', function (e) {
                    e.preventDefault();

                    const product = productsForOrder.find(
                        p => String(p.id) === String($('#item_product_id').val())
                    );

                    if (!product) {
                        alert('Silakan pilih produk.');
                        return;
                    }

                    // Validasi varian.
                    if (
                        product.variants.length > 0 &&
                        !$('#item_variant_id').val()
                    ) {
                        alert('Silakan pilih varian produk.');
                        return;
                    }

                    // Validasi jumlah.
                    const quantity = Number($('#item_quantity').val());

                    if (
                        !Number.isInteger(quantity) ||
                        quantity < 1 ||
                        quantity > 999
                    ) {
                        $('#tambah-item-error').html(
                            $('<div class="alert alert-danger"></div>').text(
                                'Jumlah harus berupa bilangan bulat antara 1 dan 999.'
                            )
                        );

                        return;
                    }

                    // Validasi stok sebelum request dikirim.
                    if (
                        product.track_stock &&
                        quantity > Number(product.stock)
                    ) {
                        $('#tambah-item-error').html(
                            $('<div class="alert alert-danger"></div>').text(
                                'Stok ' + product.name +
                                ' hanya tersedia ' +
                                Number(product.stock).toLocaleString('id-ID') +
                                ' unit. Kurangi jumlah pesanan.'
                            )
                        );

                        $('#item-stock-warning')
                            .text(
                                'Stok tidak mencukupi. Tersedia ' +
                                Number(product.stock).toLocaleString('id-ID') +
                                ' unit.'
                            )
                            .removeClass('d-none');

                        $('#btnSimpanItem').prop('disabled', true);

                        return;
                    }

                    // Siapkan data untuk dikirim ke server.
                    const button = $('#btnSimpanItem');

                    const data = {
                        _token: @json(csrf_token()),
                        product_id: product.id,
                        variant_id: $('#item_variant_id').val() || '',
                        option_ids: $('#optionGroupsContainer input[name="option_ids[]"]:checked')
                            .map(function () {
                                return $(this).val();
                            })
                            .get(),
                        quantity: quantity,
                        notes: $('#item_notes').val()
                    };

                    $('#tambah-item-error').empty();
                    button.prop('disabled', true);

                    $.ajax({
                        url: @json(route('pos.orders.items.store', $order->id)),
                        type: 'POST',
                        data: data,
                        dataType: 'json',
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .done(function (response) {
                        window.location.assign(response.redirect);
                    })
                    .fail(function (xhr) {
                        console.error(
                            'Tambah item gagal:',
                            xhr.status,
                            xhr.responseText
                        );

                        const errors = xhr.responseJSON?.errors || {};
                        const messages = Object.values(errors).flat();

                        const message = messages[0]
                            || xhr.responseJSON?.message
                            || 'Gagal menambahkan produk ke pesanan.';

                        $('#tambah-item-error').html(
                            $('<div class="alert alert-danger"></div>').text(message)
                        );
                    })
                    .always(function () {
                        // Jika request gagal, tombol diaktifkan kembali.
                        // Jika berhasil, halaman akan dialihkan.
                        button.prop('disabled', false);

                        // Hitung ulang status tombol berdasarkan jumlah dan stok.
                        updateItemEstimate();
                    });
                });


                $('#modalTambahItem').on('hidden.bs.modal', function () {
                    $('#formTambahItem')[0].reset();
                    $('#cariProdukItem').val('');
                    $('#item_product_id option').prop('hidden', false);
                    $('#tambah-item-error').empty();
                    resetItemConfig();
                });

                const orderTotal = Number(@json((float) $order->total));

                function formatPaymentRupiah(value) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID', {
                        maximumFractionDigits: 0
                    }).format(value || 0);
                }

                function parsePaymentAmount(value) {
                    const digits = String(value ?? '').replace(/\D/g, '');
                    return digits === '' ? 0 : Number(digits);
                }

                function formatPaymentInput(value) {
                    const digits = String(value ?? '').replace(/\D/g, '');

                    if (digits === '') return '';

                    return new Intl.NumberFormat('id-ID', {
                        maximumFractionDigits: 0
                    }).format(Number(digits));
                }

                function updatePaymentChange() {
                    const method = $('#payment_method').val();
                    const received = parsePaymentAmount($('#amount_received').val());

                    const change = method === 'cash'
                        ? Math.max(0, received - orderTotal)
                        : 0;

                    $('#paymentChange').text(formatPaymentRupiah(change));
                }

                function updatePaymentForm() {
                    const method = $('#payment_method').val();
                    const input = $('#amount_received');

                    if (method === 'cash') {
                        $('#amountReceivedLabel').text('Uang Diterima');
                        input.prop('readonly', false);

                        if (input.val() === '') {
                            input.val(formatPaymentInput(Math.ceil(orderTotal)));
                        }
                    } else {
                        $('#amountReceivedLabel').text('Nominal Pembayaran');
                        input.val(formatPaymentInput(orderTotal));
                        input.prop('readonly', true);
                    }

                    updatePaymentChange();
                }

                $('#payment_method').on('change', updatePaymentForm);

                $('#amount_received').on('input', function () {
                    const input = this;
                    const original = input.value;
                    const cursorFromEnd = original.length - input.selectionStart;

                    input.value = formatPaymentInput(original);

                    const newPosition = Math.max(
                        0,
                        input.value.length - cursorFromEnd
                    );

                    input.setSelectionRange(newPosition, newPosition);

                    updatePaymentChange();
                });

                $('#modalPembayaran').on('shown.bs.modal', function () {
                    updatePaymentForm();
                    $('#amount_received').trigger('focus');
                });

                $('#formPembayaran').on('submit', function (e) {
                    e.preventDefault();

                    const method = $('#payment_method').val();
                    const received = parsePaymentAmount($('#amount_received').val());

                    if (!Number.isFinite(received) || received < 0) {
                        alert('Nominal pembayaran tidak valid.');
                        return;
                    }

                    if (method === 'cash' && received < orderTotal) {
                        alert('Uang yang diterima belum mencukupi.');
                        return;
                    }

                    if (method !== 'cash' && received !== orderTotal) {
                        alert('Nominal pembayaran harus sama dengan total tagihan.');
                        return;
                    }

                    const button = $('#btnKonfirmasiPembayaran');

                    button.prop('disabled', true);
                    $('#pembayaran-error').empty();

                    $.ajax({
                        url: @json(route('pos.orders.pay', $order->id)),
                        type: 'POST',
                        data: {
                            _token: @json(csrf_token()),
                            payment_method: method,
                            amount_received: received
                        },
                        dataType: 'json',
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .done(function (response) {
                        window.location.assign(response.redirect);
                    })
                    .fail(function (xhr) {
                        console.error('Pembayaran gagal:', xhr.status, xhr.responseText);

                        const errors = xhr.responseJSON?.errors || {};
                        const message = Object.values(errors).flat()[0]
                            || xhr.responseJSON?.message
                            || 'Pembayaran gagal diproses.';

                        $('#pembayaran-error').html(
                            $('<div class="alert alert-danger"></div>').text(message)
                        );
                    })
                    .always(function () {
                        button.prop('disabled', false);
                    });
                });


            </script>
            @endsection
        @endif

        
        @if($order->status === 'open')
        <div class="modal modal-blur fade"
            id="modalPembayaran"
            tabindex="-1"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pembayaran Pesanan</h5>
                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"></button>
                    </div>

                    <form id="formPembayaran">
                        @csrf

                        <div class="modal-body">
                            <div id="pembayaran-error"></div>

                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <div class="text-secondary">
                                        Nomor Pesanan
                                    </div>
                                    <div class="fw-bold mb-3">
                                        {{ $order->order_number }}
                                    </div>

                                    <div class="text-secondary">
                                        Total Tagihan
                                    </div>
                                    <h2 class="text-primary mb-0">
                                        Rp {{ number_format($order->total, 0, ',', '.') }}
                                    </h2>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Metode Pembayaran</label>
                                <select name="payment_method"
                                        id="payment_method"
                                        class="form-select"
                                        required>
                                    <option value="cash">Tunai</option>
                                    <option value="transfer">Transfer Bank</option>
                                    <option value="qris">QRIS</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" id="amountReceivedLabel">
                                    Uang Diterima
                                </label>
                                <input type="text"
                                    name="amount_received"
                                    id="amount_received"
                                    class="form-control form-control-lg text-end"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    placeholder="Contoh: 100.000"
                                    required>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span>Kembalian</span>
                                <strong id="paymentChange">Rp 0</strong>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">
                                Batal
                            </button>

                            <button type="submit"
                                    class="btn btn-success"
                                    id="btnKonfirmasiPembayaran">
                                Konfirmasi Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
