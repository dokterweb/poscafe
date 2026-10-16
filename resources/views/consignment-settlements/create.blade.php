@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h2 class="mb-1">Buat Draft Settlement</h2>
            <div class="text-muted">
                Pilih item penjualan yang akan dibayarkan kepada mitra.
            </div>
        </div>

        <a href="{{ route('consignment-settlements.index') }}"
           class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>
            Kembali
        </a>
    </div>

    {{-- Error validasi --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Filter mitra dan periode --}}
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">1. Pilih Mitra dan Periode</h3>
        </div>

        <div class="card-body">
            <form method="GET"
                  action="{{ route('consignment-settlements.create') }}"
                  class="row g-3 align-items-end">

                <div class="col-md-4">
                    <label for="partner_id" class="form-label">
                        Mitra Konsinyasi <span class="text-danger">*</span>
                    </label>

                    <select name="partner_id"
                            id="partner_id"
                            class="form-select"
                            required>
                        <option value="">-- Pilih Mitra --</option>

                        @foreach ($partners as $partner)
                            <option
                                value="{{ $partner->id }}"
                                @selected((string) $partnerId === (string) $partner->id)
                            >
                                {{ $partner->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="start_date" class="form-label">
                        Penjualan Mulai
                    </label>

                    <input type="date"
                           name="start_date"
                           id="start_date"
                           class="form-control"
                           value="{{ $startDate }}">
                </div>

                <div class="col-md-3">
                    <label for="end_date" class="form-label">
                        Penjualan Sampai
                    </label>

                    <input type="date"
                           name="end_date"
                           id="end_date"
                           class="form-control"
                           value="{{ $endDate }}">
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-search me-1"></i>
                        Tampilkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if ($partnerId)
        {{-- Form penyimpanan settlement --}}
        <form method="POST"
              action="{{ route('consignment-settlements.store') }}"
              id="settlementForm">
            @csrf

            <input type="hidden"
                   name="consignment_partner_id"
                   value="{{ $partnerId }}">

            <input type="hidden"
                   name="period_start"
                   value="{{ $startDate }}">

            <input type="hidden"
                   name="period_end"
                   value="{{ $endDate }}">

            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">2. Pilih Item Penjualan</h3>
                </div>

                <div class="table-responsive">
                    <table class="table table-vcenter table-striped card-table">
                        <thead>
                            <tr>
                                <th style="width: 45px;">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           id="selectAllItems"
                                           aria-label="Pilih semua item">
                                </th>
                                <th>Tanggal Bayar</th>
                                <th>No. Order</th>
                                <th>Produk</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Harga Mitra</th>
                                <th class="text-end">Total Hak Mitra</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($availableItems as $item)
                                <tr>
                                    <td>
                                        <input type="checkbox"
                                               class="form-check-input settlement-item"
                                               name="item_ids[]"
                                               value="{{ $item->id }}"
                                               data-total="{{ (float) $item->partner_unit_price * (int) $item->quantity }}">
                                    </td>

                                    <td class="text-nowrap">
                                        {{ \Carbon\Carbon::parse($item->paid_at)->format('d/m/Y H:i') }}
                                    </td>

                                    <td>{{ $item->order_number }}</td>

                                    <td>
                                        <div class="fw-bold">
                                            {{ $item->product_name }}
                                        </div>

                                        @if ($item->variant_name)
                                            <div class="text-muted small">
                                                {{ $item->variant_name }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        {{ number_format((int) $item->quantity, 0, ',', '.') }}
                                    </td>

                                    <td class="text-end text-nowrap">
                                        Rp {{ number_format((float) $item->partner_unit_price, 0, ',', '.') }}
                                    </td>

                                    <td class="text-end text-nowrap fw-bold">
                                        Rp {{ number_format((float) $item->partner_unit_price * (int) $item->quantity, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="empty">
                                            <p class="empty-title">
                                                Tidak ada item yang tersedia
                                            </p>
                                            <p class="empty-subtitle text-muted">
                                                Belum ada penjualan konsinyasi yang memenuhi filter dan belum masuk settlement.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($availableItems->isNotEmpty())
                    <div class="card-body border-top">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-7">
                                <label for="notes" class="form-label">
                                    Catatan (opsional)
                                </label>

                                <textarea name="notes"
                                          id="notes"
                                          rows="2"
                                          class="form-control"
                                          maxlength="5000"
                                          placeholder="Contoh: Pembayaran penjualan minggu ini">{{ old('notes') }}</textarea>
                            </div>

                            <div class="col-md-5">
                                <div class="border rounded p-3 mb-3">
                                    <div class="text-muted">Jumlah item dipilih</div>
                                    <div class="h3 mb-2" id="selectedCount">0 item</div>

                                    <div class="text-muted">Total hak mitra</div>
                                    <div class="h2 mb-0" id="selectedTotal">
                                        Rp 0
                                    </div>
                                </div>

                                <button type="submit"
                                        class="btn btn-primary w-100"
                                        id="btnCreateSettlement">
                                    <i class="ti ti-file-plus me-1"></i>
                                    Buat Draft Settlement
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </form>
    @else
        <div class="alert alert-info">
            Pilih mitra terlebih dahulu untuk menampilkan item penjualannya.
        </div>
    @endif

</div>

@if ($partnerId && $availableItems->isNotEmpty())
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAllItems');
    const checkboxes = Array.from(
        document.querySelectorAll('.settlement-item')
    );
    const selectedCount = document.getElementById('selectedCount');
    const selectedTotal = document.getElementById('selectedTotal');
    const form = document.getElementById('settlementForm');

    function formatRupiah(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(value);
    }

    function updateSummary() {
        const selected = checkboxes.filter(item => item.checked);

        const total = selected.reduce((sum, item) => {
            return sum + Number(item.dataset.total || 0);
        }, 0);

        selectedCount.textContent = selected.length + ' item';
        selectedTotal.textContent = formatRupiah(total);

        selectAll.checked =
            checkboxes.length > 0 &&
            selected.length === checkboxes.length;

        selectAll.indeterminate =
            selected.length > 0 &&
            selected.length < checkboxes.length;
    }

    selectAll.addEventListener('change', function () {
        checkboxes.forEach(item => {
            item.checked = selectAll.checked;
        });

        updateSummary();
    });

    checkboxes.forEach(item => {
        item.addEventListener('change', updateSummary);
    });

    form.addEventListener('submit', function (event) {
        const selected = checkboxes.filter(item => item.checked);

        if (selected.length === 0) {
            event.preventDefault();
            alert('Pilih minimal satu item penjualan.');
            return;
        }

        if (!confirm(
            'Buat draft settlement untuk ' +
            selected.length +
            ' item dengan total ' +
            formatRupiah(
                selected.reduce(
                    (sum, item) => sum + Number(item.dataset.total || 0),
                    0
                )
            ) +
            '?'
        )) {
            event.preventDefault();
        }
    });

    updateSummary();
});
</script>
@endif
@endsection
