@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h2 class="mb-1">Buat Retur Konsinyasi</h2>
            <div class="text-muted">
                Draft retur belum mengubah stok maupun settlement.
            </div>
        </div>

        <a href="{{ route('consignment-returns.index') }}"
           class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('consignment-returns.store') }}" id="returnForm">
        @csrf

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Informasi Retur</h3>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label" for="consignment_partner_id">
                            Mitra <span class="text-danger">*</span>
                        </label>
                        <select name="consignment_partner_id"
                                id="consignment_partner_id"
                                class="form-select"
                                required>
                            <option value="">-- Pilih Mitra --</option>
                            @foreach ($partners as $partner)
                                <option value="{{ $partner->id }}"
                                    @selected(old('consignment_partner_id') == $partner->id)>
                                    {{ $partner->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="return_date">
                            Tanggal Retur <span class="text-danger">*</span>
                        </label>
                        <input type="date"
                               name="return_date"
                               id="return_date"
                               class="form-control"
                               value="{{ old('return_date', now()->toDateString()) }}"
                               required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="return_type">
                            Jenis Retur <span class="text-danger">*</span>
                        </label>
                        <select name="return_type" id="return_type" class="form-select" required>
                            <option value="stock_return"
                                @selected(old('return_type', 'stock_return') === 'stock_return')>
                                Retur stok belum terjual
                            </option>
                            <option value="sales_return"
                                @selected(old('return_type') === 'sales_return')>
                                Retur terkait penjualan
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="reason">
                            Alasan Retur <span class="text-danger">*</span>
                        </label>
                        <select name="reason" id="reason" class="form-select" required>
                            @foreach ([
                                'Rusak',
                                'Kedaluwarsa',
                                'Tidak laku',
                                'Cacat produk',
                                'Kesalahan pengiriman',
                                'Lainnya',
                            ] as $reason)
                                <option value="{{ $reason }}"
                                    @selected(old('reason', 'Rusak') === $reason)>
                                    {{ $reason }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="financial_treatment">
                            Kebijakan Finansial <span class="text-danger">*</span>
                        </label>
                        <select name="financial_treatment"
                                id="financial_treatment"
                                class="form-select"
                                required>
                            <option value="no_adjustment"
                                @selected(old('financial_treatment', 'no_adjustment') === 'no_adjustment')>
                                Tanpa penyesuaian hak mitra
                            </option>
                            <option value="deduct_from_draft"
                                @selected(old('financial_treatment') === 'deduct_from_draft')>
                                Kurangi settlement draft
                            </option>
                            <option value="credit_next_settlement"
                                @selected(old('financial_treatment') === 'credit_next_settlement')>
                                Kredit settlement berikutnya
                            </option>
                            <option value="partner_responsibility"
                                @selected(old('financial_treatment') === 'partner_responsibility')>
                                Tanggung jawab mitra
                            </option>
                        </select>
                        <div class="form-hint" id="treatmentHint">
                            Untuk retur stok yang belum terjual, pilih tanpa penyesuaian hak mitra.
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="notes">Catatan</label>
                        <textarea name="notes"
                                  id="notes"
                                  class="form-control"
                                  rows="3"
                                  maxlength="5000">{{ old('notes') }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Item Retur</h3>
                <div class="card-actions">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addReturnItem">
                        <i class="ti ti-plus me-1"></i> Tambah Item
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th style="min-width: 280px;">Produk / Item Penjualan</th>
                            <th style="width: 140px;">Qty Retur</th>
                            <th style="width: 70px;"></th>
                        </tr>
                    </thead>
                    <tbody id="returnItems">
                        <tr class="return-row">
                            <td>
                                <select name="items[0][product_id]"
                                        class="form-select product-select"
                                        required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach ($products as $product)
                                        <option
                                            value="{{ $product->id }}"
                                            data-partner="{{ $product->consignment_partner_id }}"
                                            data-stock="{{ $product->stock }}"
                                            data-price="{{ $product->partner_price }}">
                                            {{ $product->name }}
                                            (Stok: {{ $product->stock }})
                                        </option>
                                    @endforeach
                                </select>

                                <select name="items[0][order_item_id]"
                                        class="form-select order-item-select d-none"
                                        disabled>
                                    <option value="">-- Pilih Item Penjualan --</option>
                                    @foreach ($orderItems as $item)
                                        <option
                                            value="{{ $item->id }}"
                                            data-partner="{{ $item->consignment_partner_id }}"
                                            data-qty="{{ $item->quantity }}"
                                            data-price="{{ $item->partner_unit_price }}">
                                            {{ $item->order_number }} —
                                            {{ $item->product_name }}
                                            (Qty: {{ $item->quantity }})
                                        </option>
                                    @endforeach
                                </select>

                                <div class="form-hint item-hint"></div>
                            </td>

                            <td>
                                <input type="number"
                                       name="items[0][quantity]"
                                       class="form-control quantity-input"
                                       min="1"
                                       max="999999"
                                       value="1"
                                       required>
                            </td>

                            <td>
                                <button type="button"
                                        class="btn btn-outline-danger btn-icon remove-row"
                                        aria-label="Hapus item">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i>
                    Simpan Draft Retur
                </button>
            </div>
        </div>
    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const partnerSelect = document.getElementById('consignment_partner_id');
    const typeSelect = document.getElementById('return_type');
    const treatmentSelect = document.getElementById('financial_treatment');
    const treatmentHint = document.getElementById('treatmentHint');
    const tbody = document.getElementById('returnItems');
    const addButton = document.getElementById('addReturnItem');

    let rowIndex = 1;

    function filterOptions(select, partnerId) {
        Array.from(select.options).forEach(option => {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            option.hidden = !partnerId || option.dataset.partner !== partnerId;

            if (option.hidden && option.selected) {
                select.value = '';
            }
        });
    }

    function updateTreatmentOptions() {
        const stockReturn = typeSelect.value === 'stock_return';

        Array.from(treatmentSelect.options).forEach(option => {
            option.hidden = stockReturn
                ? option.value !== 'no_adjustment'
                : option.value === 'no_adjustment';
        });

        const allowed = stockReturn ? 'no_adjustment' : null;

        if (allowed) {
            treatmentSelect.value = allowed;
            treatmentHint.textContent =
                'Retur stok yang belum terjual tidak mengubah hak pembayaran mitra.';
        } else {
            if (treatmentSelect.value === 'no_adjustment') {
                treatmentSelect.value = 'partner_responsibility';
            }

            treatmentHint.textContent =
                'Pilih kebijakan finansial sesuai kondisi settlement dan keputusan admin.';
        }

        tbody.querySelectorAll('.return-row').forEach(updateRow);
    }

    function updateRow(row) {
        const productSelect = row.querySelector('.product-select');
        const orderSelect = row.querySelector('.order-item-select');
        const quantityInput = row.querySelector('.quantity-input');
        const hint = row.querySelector('.item-hint');
        const partnerId = partnerSelect.value;
        const stockReturn = typeSelect.value === 'stock_return';

        productSelect.classList.toggle('d-none', !stockReturn);
        productSelect.disabled = !stockReturn;
        productSelect.required = stockReturn;

        orderSelect.classList.toggle('d-none', stockReturn);
        orderSelect.disabled = stockReturn;
        orderSelect.required = !stockReturn;

        filterOptions(productSelect, partnerId);
        filterOptions(orderSelect, partnerId);

        const selected = stockReturn
            ? productSelect.selectedOptions[0]
            : orderSelect.selectedOptions[0];

        if (selected && selected.value) {
            quantityInput.max = stockReturn
                ? (selected.dataset.stock || 999999)
                : (selected.dataset.qty || 999999);

            hint.textContent = stockReturn
                ? 'Stok tersedia: ' + selected.dataset.stock
                : 'Qty penjualan: ' + selected.dataset.qty;
        } else {
            quantityInput.max = 999999;
            hint.textContent = '';
        }
    }

    function createRow(index) {
        const firstRow = tbody.querySelector('.return-row');
        const row = firstRow.cloneNode(true);

        row.querySelectorAll('select, input').forEach(element => {
            element.name = element.name.replace(/\[\d+\]/, '[' + index + ']');

            if (element.tagName === 'SELECT') {
                element.value = '';
            } else {
                element.value = '1';
                element.max = 999999;
            }
        });

        tbody.appendChild(row);
        updateRow(row);
    }

    partnerSelect.addEventListener('change', function () {
        tbody.querySelectorAll('.return-row').forEach(updateRow);
    });

    typeSelect.addEventListener('change', updateTreatmentOptions);
    treatmentSelect.addEventListener('change', updateTreatmentOptions);

    addButton.addEventListener('click', function () {
        createRow(rowIndex++);
    });

    tbody.addEventListener('change', function (event) {
        if (
            event.target.classList.contains('product-select') ||
            event.target.classList.contains('order-item-select')
        ) {
            updateRow(event.target.closest('.return-row'));
        }
    });

    tbody.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-row');

        if (!button) return;

        const rows = tbody.querySelectorAll('.return-row');

        if (rows.length === 1) {
            alert('Minimal harus ada satu item retur.');
            return;
        }

        button.closest('.return-row').remove();
    });

    updateTreatmentOptions();
    tbody.querySelectorAll('.return-row').forEach(updateRow);
});
</script>
@endsection
