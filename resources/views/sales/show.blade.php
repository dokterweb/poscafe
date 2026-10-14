@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">

                <div class="col">

                    <div class="page-pretitle">
                        Penjualan
                    </div>

                    <h2 class="page-title">
                        Detail Penjualan
                    </h2>

                </div>

                <div class="col-auto ms-auto">

                    <div class="btn-list">

                        <a href="{{ route('sales.index') }}"
                        class="btn">

                            <i class="ti ti-arrow-left"></i>

                            Kembali

                        </a>


                        <a href="{{ route('sales.print', $sale) }}"
                        target="_blank"
                        class="btn btn-success">

                            <i class="ti ti-printer"></i>

                            Cetak

                        </a>

                    </div>

                </div>

            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            {{-- =====================================================
                INFORMASI TRANSAKSI
            ====================================================== --}}

            <div class="row row-cards mb-3">

                <div class="col-md-4">

                    <div class="card">

                        <div class="card-body">

                            <div class="text-secondary mb-1">
                                Invoice
                            </div>

                            <div class="h2 mb-0">
                                {{ $sale->invoice }}
                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="card">

                        <div class="card-body">

                            <div class="text-secondary mb-1">
                                Tanggal
                            </div>

                            <div class="h2 mb-0">

                                {{ $sale->date?->format('d/m/Y') }}

                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="card">

                        <div class="card-body">

                            <div class="text-secondary mb-1">
                                Status Bayar
                            </div>

                            <div class="mt-2">

                                @if ($sale->payment_status === 'lunas')

                                    <span class="badge bg-success fs-3">

                                        Lunas

                                    </span>

                                @else

                                    <span class="badge bg-warning text-dark fs-3">

                                        Belum Lunas

                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                CUSTOMER
            ====================================================== --}}

            <div class="card mb-3">

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="ti ti-user me-2"></i>

                        Informasi Customer

                    </h3>

                </div>


                <div class="card-body">

                    <div class="row">

                        <div class="col-md-4">

                            <div class="text-secondary">
                                Nama
                            </div>

                            <div class="fw-bold">

                                {{ $sale->customer?->name ?? 'Cash / Umum' }}

                            </div>

                        </div>


                        <div class="col-md-4">

                            <div class="text-secondary">
                                Phone
                            </div>

                            <div class="fw-bold">

                                {{ $sale->customer?->phone ?? '-' }}

                            </div>

                        </div>


                        <div class="col-md-4">

                            <div class="text-secondary">
                                Alamat
                            </div>

                            <div class="fw-bold">

                                {{ $sale->customer?->address ?? '-' }}

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                DETAIL PRODUK
            ====================================================== --}}

            <div class="card mb-3">

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="ti ti-shopping-cart me-2"></i>

                        Detail Produk

                    </h3>

                </div>


                <div class="table-responsive">

                    <table class="table table-vcenter card-table">

                        <thead>

                            <tr>

                                <th style="width: 50px;">
                                    No.
                                </th>

                                <th>
                                    Produk
                                </th>

                                <th class="text-end">
                                    Harga
                                </th>

                                <th class="text-center">
                                    Qty
                                </th>

                                <th class="text-end">
                                    Discount
                                </th>

                                <th class="text-end">
                                    Subtotal
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse ($sale->details as $detail)

                                <tr>

                                    <td>
                                        {{ $loop->iteration }}
                                    </td>


                                    <td>

                                        <div class="fw-bold">

                                            {{ $detail->product?->name ?? '-' }}

                                        </div>

                                        @if ($detail->product?->barcode)

                                            <div class="text-secondary small">

                                                {{ $detail->product->barcode }}

                                            </div>

                                        @endif

                                    </td>


                                    <td class="text-end">

                                        Rp
                                        {{ number_format(
                                            $detail->price,
                                            0,
                                            ',',
                                            '.'
                                        ) }}

                                    </td>


                                    <td class="text-center">

                                        {{ number_format(
                                            $detail->qty,
                                            0,
                                            ',',
                                            '.'
                                        ) }}

                                    </td>


                                    <td class="text-end">

                                        {{ number_format(
                                            $detail->discount,
                                            2,
                                            ',',
                                            '.'
                                        ) }}%

                                    </td>


                                    <td class="text-end fw-bold">

                                        Rp
                                        {{ number_format(
                                            $detail->total,
                                            0,
                                            ',',
                                            '.'
                                        ) }}

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="6"
                                        class="text-center text-muted py-4">

                                        Tidak ada detail produk.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>


            {{-- =====================================================
                RINGKASAN
            ====================================================== --}}

            <div class="row justify-content-end">

                <div class="col-md-5">

                    <div class="card">

                        <div class="card-header">

                            <h3 class="card-title">
                                Ringkasan Transaksi
                            </h3>

                        </div>


                        <div class="card-body">

                            <div class="row mb-3">

                                <div class="col">
                                    Total Harga
                                </div>

                                <div class="col-auto fw-bold">

                                    Rp
                                    {{ number_format(
                                        $sale->total_price,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                </div>

                            </div>


                            <div class="row mb-3">

                                <div class="col">
                                    Total Discount
                                </div>

                                <div class="col-auto fw-bold text-danger">

                                    Rp
                                    {{ number_format(
                                        $sale->discount,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                </div>

                            </div>


                            <div class="row pt-3 border-top">

                                <div class="col">

                                    <strong>
                                        Grand Total
                                    </strong>

                                </div>

                                <div class="col-auto">

                                    <strong class="fs-2">

                                        Rp
                                        {{ number_format(
                                            $sale->final_price,
                                            0,
                                            ',',
                                            '.'
                                        ) }}

                                    </strong>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                CATATAN & USER
            ====================================================== --}}

            <div class="row mt-3">

                <div class="col-md-6">

                    <div class="card">

                        <div class="card-body">

                            <div class="text-secondary mb-1">
                                Catatan
                            </div>

                            <div>

                                {{ $sale->note ?: '-' }}

                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-md-6">

                    <div class="card">

                        <div class="card-body">

                            <div class="text-secondary mb-1">
                                Dibuat Oleh
                            </div>

                            <div class="fw-bold">

                                {{ $sale->user?->name ?? '-' }}

                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

@endsection