@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- ======================================================
        HEADER
    ======================================================= --}}

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">

        <div>

            <h2 class="page-title mb-1">
                Stock Adjustment History
            </h2>

            <div class="text-muted">
                Riwayat seluruh penyesuaian stok.
            </div>

        </div>


        <div>

            <a
                href="{{ route('stock-adjustments.create') }}"
                class="btn btn-primary"
            >

                <i class="fas fa-plus me-1"></i>

                Adjustment Baru

            </a>

        </div>

    </div>


    {{-- ======================================================
        SUMMARY CARDS
    ======================================================= --}}

    <div class="row row-cards mb-3">

        {{-- TOTAL ADJUSTMENT --}}

        <div class="col-sm-6 col-lg-4">

            <div class="card">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div>

                            <div class="text-muted">
                                Total Adjustment
                            </div>

                            <div class="fs-2 fw-bold">
                                {{ number_format($totalAdjustment) }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- TOTAL IN --}}

        <div class="col-sm-6 col-lg-4">

            <div class="card">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div>

                            <div class="text-muted">
                                Stock Masuk
                            </div>

                            <div class="fs-2 fw-bold text-success">

                                +{{ number_format($totalIn) }}

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- TOTAL OUT --}}

        <div class="col-sm-6 col-lg-4">

            <div class="card">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div>

                            <div class="text-muted">
                                Stock Keluar
                            </div>

                            <div class="fs-2 fw-bold text-danger">

                                -{{ number_format($totalOut) }}

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ======================================================
        FILTER
    ======================================================= --}}

    <div class="card mb-3">

        <div class="card-body">

            <form
                method="GET"
                action="{{ route('stock-adjustments.index') }}"
            >

                <div class="row g-2 align-items-end">

                    {{-- SEARCH --}}

                    <div class="col-md-4">

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


                    {{-- TYPE --}}

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


                    {{-- DATE FROM --}}

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


                    {{-- DATE TO --}}

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


                    {{-- BUTTON --}}

                    <div class="col-md-2">

                        <div class="d-flex gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary flex-fill"
                            >

                                <i class="fas fa-search me-1"></i>

                                Cari

                            </button>


                            <a
                                href="{{ route('stock-adjustments.index') }}"
                                class="btn btn-outline-secondary"
                                title="Reset"
                            >

                                <i class="fas fa-refresh"></i>

                            </a>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- ======================================================
        INFO
    ======================================================= --}}

    <div class="mb-3">

        <span class="text-muted">

            Menampilkan
            <strong>
                {{ $adjustments->total() }}
            </strong>
            histori adjustment.

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
                            Keterangan
                        </th>

                        <th>
                            User
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($adjustments as $adjustment)

                        <tr>

                            {{-- NO --}}

                            <td>

                                {{
                                    $adjustments->firstItem()
                                    + $loop->index
                                }}

                            </td>


                            {{-- TANGGAL --}}

                            <td>

                                <div class="fw-semibold">

                                    {{
                                        $adjustment->created_at
                                            ?->format('d/m/Y')
                                    }}

                                </div>

                                <small class="text-muted">

                                    {{
                                        $adjustment->created_at
                                            ?->format('H:i')
                                    }}

                                </small>

                            </td>


                            {{-- PRODUK --}}

                            <td>

                                @if($adjustment->product)

                                    <div class="fw-semibold">

                                        {{ $adjustment->product->name }}

                                    </div>

                                    <small class="text-muted">

                                        {{
                                            $adjustment->product->barcode
                                            ?? '-'
                                        }}

                                    </small>

                                @else

                                    <span class="text-muted">
                                        Produk tidak ditemukan
                                    </span>

                                @endif

                            </td>


                            {{-- TYPE --}}

                            <td>

                                @if($adjustment->type === 'in')

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

                                    {{ number_format($adjustment->qty) }}

                                </span>

                            </td>


                            {{-- STOCK BEFORE --}}

                            <td class="text-end">

                                {{ number_format(
                                    $adjustment->stock_before
                                ) }}

                            </td>


                            {{-- STOCK AFTER --}}

                            <td class="text-end">

                                <span class="fw-semibold">

                                    {{ number_format(
                                        $adjustment->stock_after
                                    ) }}

                                </span>

                            </td>


                            {{-- NOTE --}}

                            <td>

                                @if($adjustment->note)

                                    <span
                                        title="{{ $adjustment->note }}"
                                    >

                                        {{
                                            \Illuminate\Support\Str::limit(
                                                $adjustment->note,
                                                60
                                            )
                                        }}

                                    </span>

                                @else

                                    <span class="text-muted">
                                        -
                                    </span>

                                @endif

                            </td>


                            {{-- USER --}}

                            <td>

                                {{ $adjustment->user?->name ?? '-' }}

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="9"
                                class="text-center py-5"
                            >

                                <div class="text-muted">

                                    <i
                                        class="fas fa-box-open fa-2x mb-2"
                                    ></i>

                                    <div>
                                        Belum ada histori adjustment.
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

        @if($adjustments->hasPages())

            <div class="card-footer">

                {{ $adjustments->links() }}

            </div>

        @endif

    </div>

</div>

@endsection