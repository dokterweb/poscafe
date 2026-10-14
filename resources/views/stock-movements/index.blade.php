@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- ======================================================
        PAGE HEADER
    ======================================================= --}}

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">

        <div>

            <h2 class="page-title mb-1">
                Stock History
            </h2>

            <div class="text-muted">
                Riwayat seluruh pergerakan stok produk.
            </div>

        </div>

    </div>


    {{-- ======================================================
        FILTER
    ======================================================= --}}

    <div class="card mb-3">

        <div class="card-body">

            <form method="GET" action="{{ route('stock-movements.index') }}">

                <div class="row g-2 align-items-end">

                    <div class="col-md-3">

                        <label class="form-label">
                            Cari Produk
                        </label>

                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            class="form-control"
                            placeholder="Nama produk / barcode..."
                        >

                    </div>


                    <div class="col-md-2">

                        <label class="form-label">
                            Tipe
                        </label>

                        <select
                            name="type"
                            class="form-select"
                        >

                            <option value="">
                                Semua
                            </option>

                            <option
                                value="in"
                                @selected(request('type') === 'in')
                            >
                                Stock Masuk
                            </option>

                            <option
                                value="out"
                                @selected(request('type') === 'out')
                            >
                                Stock Keluar
                            </option>

                        </select>

                    </div>


                    <div class="col-md-2">

                        <label class="form-label">
                            Sumber
                        </label>

                        <select
                            name="source"
                            class="form-select"
                        >

                            <option value="">
                                Semua
                            </option>

                            <option
                                value="purchase"
                                @selected(request('source') === 'purchase')
                            >
                                Pembelian
                            </option>

                            <option
                                value="sale"
                                @selected(request('source') === 'sale')
                            >
                                Penjualan
                            </option>

                            <option
                                value="adjustment"
                                @selected(request('source') === 'adjustment')
                            >
                                Adjustment
                            </option>

                        </select>

                    </div>


                    <div class="col-md-2">

                        <label class="form-label">
                            Dari Tanggal
                        </label>

                        <input
                            type="date"
                            name="date_from"
                            value="{{ request('date_from') }}"
                            class="form-control"
                        >

                    </div>


                    <div class="col-md-2">

                        <label class="form-label">
                            Sampai Tanggal
                        </label>

                        <input
                            type="date"
                            name="date_to"
                            value="{{ request('date_to') }}"
                            class="form-control"
                        >

                    </div>


                    <div class="col-md-1">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                            title="Cari"
                        >

                            <i class="fas fa-search"></i>

                        </button>

                    </div>

                </div>
            </form>

        </div>

    </div>


    {{-- ======================================================
        SUMMARY
    ======================================================= --}}

    <div class="mb-3">

        <span class="text-muted">

            Menampilkan
            <strong>
                {{ $movements->total() }}
            </strong>
            histori stok.

        </span>

    </div>


    {{-- ======================================================
        TABLE
    ======================================================= --}}

    <div class="card">

        <div class="table-responsive">

            <table class="table table-vcenter card-table">

                <thead>

                    <tr>

                        <th width="60">
                            No
                        </th>

                        <th>
                            Tanggal
                        </th>

                        <th>
                            Produk
                        </th>

                        <th>
                            Tipe
                        </th>

                        <th class="text-end">
                            Qty
                        </th>

                        <th class="text-end">
                            Stock Sebelum
                        </th>

                        <th class="text-end">
                            Stock Sesudah
                        </th>

                        <th>
                            Sumber
                        </th>

                        <th>
                            Keterangan
                        </th>

                        <th>
                            User
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($movements as $movement)

                        <tr>

                            {{-- NO --}}

                            <td>

                                {{
                                    $movements->firstItem()
                                    + $loop->index
                                }}

                            </td>


                            {{-- TANGGAL --}}

                            <td>

                                <div class="fw-semibold">

                                    {{
                                        $movement->created_at
                                            ?->format('d/m/Y')
                                    }}

                                </div>

                                <small class="text-muted">

                                    {{
                                        $movement->created_at
                                            ?->format('H:i')
                                    }}

                                </small>

                            </td>


                            {{-- PRODUK --}}

                            <td>

                                @if($movement->product)

                                    <div class="fw-semibold">

                                        {{ $movement->product->name }}

                                    </div>

                                    @if($movement->product->barcode)

                                        <small class="text-muted">

                                            {{ $movement->product->barcode }}

                                        </small>

                                    @endif

                                @else

                                    <span class="text-muted">
                                        Produk tidak ditemukan
                                    </span>

                                @endif

                            </td>


                            {{-- TYPE --}}

                            <td>

                                @if($movement->type === 'in')

                                    <span class="badge bg-success">

                                        <i class="fas fa-arrow-down me-1"></i>

                                        IN

                                    </span>

                                @else

                                    <span class="badge bg-danger">

                                        <i class="fas fa-arrow-up me-1"></i>

                                        OUT

                                    </span>

                                @endif

                            </td>


                            {{-- QTY --}}

                            <td class="text-end">

                                <span class="fw-semibold">

                                    {{ number_format($movement->qty) }}

                                </span>

                            </td>


                            {{-- STOCK BEFORE --}}

                            <td class="text-end">

                                {{ number_format($movement->stock_before) }}

                            </td>


                            {{-- STOCK AFTER --}}

                            <td class="text-end">

                                <span class="fw-semibold">

                                    {{ number_format($movement->stock_after) }}

                                </span>

                            </td>


                            {{-- REFERENCE --}}
                            <td>
                            @if($movement->reference_type === 'purchase')

                                @php
                                    $purchase = $purchases->get(
                                        $movement->reference_id
                                    );
                                @endphp

                                <span class="badge bg-azure">
                                    Pembelian
                                </span>

                                @if($purchase)

                                    <div class="mt-1">

                                        <a
                                            href="{{ route('purchases.show', $purchase) }}"
                                            class="fw-semibold text-decoration-none"
                                        >
                                            {{ $purchase->invoice }}
                                        </a>

                                    </div>

                                    <small class="text-muted">
                                        {{ $purchase->date?->format('d/m/Y') }}
                                    </small>

                                @else

                                    <div class="small text-muted">
                                        Transaksi sudah dihapus
                                    </div>

                                @endif


                            @elseif($movement->reference_type === 'sale')

                                @php
                                    $sale = $sales->get(
                                        $movement->reference_id
                                    );
                                @endphp

                                <span class="badge bg-purple">
                                    Penjualan
                                </span>

                                @if($sale)

                                    <div class="mt-1">

                                        <a
                                            href="{{ route('sales.show', $sale) }}"
                                            class="fw-semibold text-decoration-none"
                                        >
                                            {{ $sale->invoice }}
                                        </a>

                                    </div>

                                    <small class="text-muted">
                                        {{ $sale->date?->format('d/m/Y') }}
                                    </small>

                                @else

                                    <div class="small text-muted">
                                        Transaksi sudah dihapus
                                    </div>

                                @endif


                            @elseif($movement->reference_type === 'adjustment')

                                <span class="badge bg-warning text-dark">
                                    Adjustment
                                </span>

                                <div class="mt-1">

                                    <a
                                        href="{{ route('stock-adjustments.index') }}"
                                        class="fw-semibold text-decoration-none"
                                    >
                                        Koreksi Stok
                                    </a>

                                </div>


                            @else

                                <span class="text-muted">
                                    -
                                </span>

                            @endif
                            </td>                            
                            {{-- NOTE --}}

                            <td>

                                @if($movement->note)

                                    <span
                                        title="{{ $movement->note }}"
                                    >

                                        {{ Str::limit(
                                            $movement->note,
                                            40
                                        ) }}

                                    </span>

                                @else

                                    <span class="text-muted">
                                        -
                                    </span>

                                @endif

                            </td>


                            {{-- USER --}}

                            <td>

                                {{ $movement->user?->name ?? '-' }}

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="10"
                                class="text-center py-5"
                            >

                                <div class="text-muted">

                                    <i
                                        class="fas fa-box-open fa-2x mb-2"
                                    ></i>

                                    <div>
                                        Belum ada histori stock.
                                    </div>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- ==================================================
            PAGINATION
        =================================================== --}}

        @if($movements->hasPages())

            <div class="card-footer">

                {{ $movements->links() }}

            </div>

        @endif

    </div>

</div>

@endsection