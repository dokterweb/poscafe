@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- ======================================================
         HEADER
         ====================================================== --}}

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="mb-1">
                Report Pembelian
            </h2>

            <div class="text-muted">
                Laporan transaksi pembelian barang dari supplier.
            </div>

        </div>


        <a href="{{ route('purchases.index') }}"
           class="btn btn-outline-secondary">

            <i class="bi bi-arrow-left me-1"></i>

            Pembelian

        </a>

    </div>


    {{-- ======================================================
         FILTER
         ====================================================== --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <form method="GET"
                  action="{{ route('purchases.report') }}">

                <div class="row g-3 align-items-end">


                    {{-- SEARCH --}}

                    <div class="col-md-3">

                        <label class="form-label fw-semibold">
                            Invoice
                        </label>

                        <input type="text"
                               name="search"
                               class="form-control"
                               value="{{ request('search') }}"
                               placeholder="Cari invoice...">

                    </div>


                    {{-- SUPPLIER --}}

                    <div class="col-md-3">

                        <label class="form-label fw-semibold">
                            Supplier
                        </label>

                        <select name="supplier_id"
                                class="form-select">

                            <option value="">
                                Semua Supplier
                            </option>

                            @foreach($suppliers as $supplier)

                                <option value="{{ $supplier->id }}"
                                    @selected(
                                        request('supplier_id') == $supplier->id
                                    )>

                                    {{ $supplier->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- DATE FROM --}}

                    <div class="col-md-2">

                        <label class="form-label fw-semibold">
                            Dari
                        </label>

                        <input type="date"
                               name="date_from"
                               class="form-control"
                               value="{{ request('date_from') }}">

                    </div>


                    {{-- DATE TO --}}

                    <div class="col-md-2">

                        <label class="form-label fw-semibold">
                            Sampai
                        </label>

                        <input type="date"
                               name="date_to"
                               class="form-control"
                               value="{{ request('date_to') }}">

                    </div>


                    {{-- BUTTON --}}

                    <div class="col-md-2">

                        <div class="d-flex gap-2">

                            <button type="submit"
                                    class="btn btn-primary">

                                <i class="bi bi-search"></i>

                            </button>


                            <a href="{{ route('purchases.report') }}"
                               class="btn btn-outline-secondary">

                                <i class="bi bi-arrow-counterclockwise"></i>

                            </a>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- ======================================================
         SUMMARY
         ====================================================== --}}

    <div class="row g-3 mb-4">


        {{-- TRANSACTION --}}

        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="text-muted small mb-1">
                        Total Transaksi
                    </div>

                    <div class="fs-3 fw-semibold">

                        {{ number_format(
                            $totalTransaction,
                            0,
                            ',',
                            '.'
                        ) }}

                    </div>

                    <small class="text-muted">
                        transaksi pembelian
                    </small>

                </div>

            </div>

        </div>


        {{-- TOTAL QTY --}}

        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="text-muted small mb-1">
                        Total Barang
                    </div>

                    <div class="fs-3 fw-semibold">

                        {{ number_format(
                            $totalQty,
                            0,
                            ',',
                            '.'
                        ) }}

                    </div>

                    <small class="text-muted">
                        unit dibeli
                    </small>

                </div>

            </div>

        </div>


        {{-- TOTAL PURCHASE --}}

        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="text-muted small mb-1">
                        Total Pembelian
                    </div>

                    <div class="fs-3 fw-semibold text-primary">

                        Rp
                        {{ number_format(
                            $totalPurchase,
                            0,
                            ',',
                            '.'
                        ) }}

                    </div>

                    <small class="text-muted">
                        nilai pembelian
                    </small>

                </div>

            </div>

        </div>

    </div>


    {{-- ======================================================
         TABLE
         ====================================================== --}}

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h5 class="mb-0 fw-semibold">
                        Detail Transaksi Pembelian
                    </h5>

                    <small class="text-muted">
                        Data berdasarkan filter yang dipilih.
                    </small>

                </div>

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th width="60">
                                #
                            </th>

                            <th>
                                Invoice
                            </th>

                            <th>
                                Tanggal
                            </th>

                            <th>
                                Supplier
                            </th>

                            <th class="text-center">
                                Item
                            </th>

                            <th class="text-end">
                                Total
                            </th>

                            <th width="80"
                                class="text-center">
                                Detail
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($purchases as $purchase)

                            <tr>

                                <td>

                                    {{ $purchases->firstItem() + $loop->index }}

                                </td>


                                <td>

                                    <div class="fw-semibold">

                                        {{ $purchase->invoice }}

                                    </div>

                                </td>


                                <td>

                                    {{ $purchase->date?->format('d/m/Y') }}

                                </td>


                                <td>

                                    {{ $purchase->supplier?->name ?? '-' }}

                                </td>


                                <td class="text-center">

                                    {{ number_format(
                                        $purchase->details->sum('qty'),
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                </td>


                                <td class="text-end">

                                    <span class="fw-semibold">

                                        Rp
                                        {{ number_format(
                                            $purchase->total,
                                            0,
                                            ',',
                                            '.'
                                        ) }}

                                    </span>

                                </td>


                                <td class="text-center">

                                    <a href="{{ route(
                                        'purchases.show',
                                        $purchase
                                    ) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Detail">

                                        <i class="far fa-eye"></i>

                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="7"
                                    class="text-center py-5">

                                    <div class="text-muted">

                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>

                                        Tidak ada data pembelian
                                        berdasarkan filter.

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- ==================================================
             PAGINATION
             ================================================== --}}

        @if($purchases->hasPages())

            <div class="card-footer bg-white">

                <div class="d-flex justify-content-between align-items-center">

                    <div class="text-muted small">

                        Menampilkan

                        {{ $purchases->firstItem() }}

                        sampai

                        {{ $purchases->lastItem() }}

                        dari

                        {{ $purchases->total() }}

                        transaksi

                    </div>


                    <div>

                        {{ $purchases->links() }}

                    </div>

                </div>

            </div>

        @endif

    </div>

</div>

@endsection