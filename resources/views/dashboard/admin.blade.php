@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- ======================================================
        HEADER
    ======================================================= --}}

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

        <div>

            <h2 class="page-title mb-1">
                Dashboard
            </h2>

            <div class="text-muted">
                Ringkasan aktivitas RudyPOS.
            </div>

        </div>

        <div class="text-muted">

            {{ now()->translatedFormat('l, d F Y') }}

        </div>

    </div>


    {{-- ======================================================
        SUMMARY CARDS
    ======================================================= --}}

    <div class="row row-cards mb-4">


        {{-- ==================================================
            PENJUALAN HARI INI
        =================================================== --}}

        <div class="col-sm-6 col-lg-3">

            <div class="card">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div class="subheader">
                            Penjualan Hari Ini
                        </div>

                    </div>


                    <div class="h1 mb-1">

                        Rp{{ number_format(
                            $salesTodayAmount,
                            0,
                            ',',
                            '.'
                        ) }}

                    </div>


                    <div class="text-muted">

                        {{ number_format($salesToday) }}

                        transaksi

                    </div>

                </div>

            </div>

        </div>


        {{-- ==================================================
            PENJUALAN BULAN INI
        =================================================== --}}

        <div class="col-sm-6 col-lg-3">

            <div class="card">

                <div class="card-body">

                    <div class="subheader">
                        Penjualan Bulan Ini
                    </div>


                    <div class="h1 mb-1">

                        Rp{{ number_format(
                            $salesThisMonthAmount,
                            0,
                            ',',
                            '.'
                        ) }}

                    </div>


                    <div class="text-muted">

                        {{ number_format($salesThisMonth) }}

                        transaksi

                    </div>

                </div>

            </div>

        </div>


        {{-- ==================================================
            PEMBELIAN BULAN INI
        =================================================== --}}

        <div class="col-sm-6 col-lg-3">

            <div class="card">

                <div class="card-body">

                    <div class="subheader">
                        Pembelian Bulan Ini
                    </div>


                    <div class="h1 mb-1">

                        Rp{{ number_format(
                            $purchasesThisMonthAmount,
                            0,
                            ',',
                            '.'
                        ) }}

                    </div>


                    <div class="text-muted">

                        {{ number_format($purchasesThisMonth) }}

                        transaksi

                    </div>

                </div>

            </div>

        </div>


        {{-- ==================================================
            TOTAL STOCK
        =================================================== --}}

        <div class="col-sm-6 col-lg-3">

            <div class="card">

                <div class="card-body">

                    <div class="subheader">
                        Total Stock
                    </div>


                    <div class="h1 mb-1">

                        {{ number_format(
                            $totalStock
                        ) }}

                    </div>


                    <div class="text-muted">

                        dari

                        {{ number_format(
                            $totalProducts
                        ) }}

                        produk aktif

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ======================================================
        SECOND SUMMARY
    ======================================================= --}}

    <div class="row row-cards mb-4">


        {{-- ==================================================
            CUSTOMER
        =================================================== --}}

        <div class="col-sm-6 col-lg-3">

            <div class="card">

                <div class="card-body">

                    <div class="text-muted">
                        Total Customer
                    </div>

                    <div class="h2 mb-0">

                        {{ number_format(
                            $totalCustomers
                        ) }}

                    </div>

                </div>

            </div>

        </div>


        {{-- ==================================================
            SUPPLIER
        =================================================== --}}

        <div class="col-sm-6 col-lg-3">

            <div class="card">

                <div class="card-body">

                    <div class="text-muted">
                        Total Supplier
                    </div>

                    <div class="h2 mb-0">

                        {{ number_format(
                            $totalSuppliers
                        ) }}

                    </div>

                </div>

            </div>

        </div>


        {{-- ==================================================
            UNPAID
        =================================================== --}}

        <div class="col-sm-6 col-lg-3">

            <div class="card">

                <div class="card-body">

                    <div class="text-muted">
                        Belum Lunas
                    </div>

                    <div class="h2 mb-0 text-danger">

                        {{ number_format(
                            $unpaidSales
                        ) }}

                    </div>


                    <small class="text-muted">

                        Rp{{ number_format(
                            $unpaidSalesAmount,
                            0,
                            ',',
                            '.'
                        ) }}

                    </small>

                </div>

            </div>

        </div>


        {{-- ==================================================
            TRANSAKSI HARI INI
        =================================================== --}}

        <div class="col-sm-6 col-lg-3">

            <div class="card">

                <div class="card-body">

                    <div class="text-muted">
                        Transaksi Hari Ini
                    </div>

                    <div class="h2 mb-0">

                        {{ number_format(
                            $salesToday
                        ) }}

                    </div>


                    <small class="text-muted">

                        Penjualan

                    </small>

                </div>

            </div>

        </div>

    </div>


    {{-- ======================================================
        GRAFIK + QUICK ACTION
    ======================================================= --}}

    <div class="row row-cards mb-4">


        {{-- ==================================================
            SALES CHART
        =================================================== --}}

        <div class="col-lg-8">

            <div class="card">

                <div class="card-header">

                    <div>

                        <h3 class="card-title">
                            Penjualan 7 Hari Terakhir
                        </h3>

                        <div class="text-muted small">
                            Total penjualan per hari.
                        </div>

                    </div>

                </div>


                <div class="card-body">

                    <div
                        id="sales-chart"
                        style="height: 280px;"
                    ></div>

                </div>

            </div>

        </div>


        {{-- ==================================================
            QUICK ACTION
        =================================================== --}}

        <div class="col-lg-4">

            <div class="card">

                <div class="card-header">

                    <h3 class="card-title">
                        Quick Action
                    </h3>

                </div>


                <div class="card-body">

                    <div class="d-grid gap-2">


                        <a
                            href="{{ route('sales.create') }}"
                            class="btn btn-primary"
                        >

                            <i class="fas fa-cart-shopping me-1"></i>

                            Transaksi Penjualan

                        </a>


                        <a
                            href="{{ route('purchases.create') }}"
                            class="btn btn-success"
                        >

                            <i class="fas fa-boxes-stacked me-1"></i>

                            Pembelian Barang

                        </a>


                        <a
                            href="{{ route('stock-adjustments.create') }}"
                            class="btn btn-warning"
                        >

                            <i class="fas fa-sliders-h me-1"></i>

                            Stock Adjustment

                        </a>


                        <a
                            href="{{ route('stock-movements.index') }}"
                            class="btn btn-outline-secondary"
                        >

                            <i class="fas fa-clock-rotate-left me-1"></i>

                            Stock History

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ======================================================
        RECENT TRANSACTIONS
    ======================================================= --}}

    <div class="row row-cards">


        {{-- ==================================================
            RECENT SALES
        =================================================== --}}

        <div class="col-lg-7">

            <div class="card">

                <div class="card-header">

                    <h3 class="card-title">
                        Penjualan Terbaru
                    </h3>


                    <div class="card-actions">

                        <a
                            href="{{ route('sales.index') }}"
                            class="btn btn-sm btn-outline-primary"
                        >
                            Lihat Semua
                        </a>

                    </div>

                </div>


                <div class="table-responsive">

                    <table class="table table-vcenter card-table">

                        <thead>

                            <tr>

                                <th>
                                    Invoice
                                </th>

                                <th>
                                    Customer
                                </th>

                                <th>
                                    Tanggal
                                </th>

                                <th class="text-end">
                                    Total
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($recentSales as $sale)

                                <tr>

                                    <td>

                                        <a
                                            href="{{ route('sales.show', $sale) }}"
                                            class="fw-semibold text-decoration-none"
                                        >

                                            {{ $sale->invoice }}

                                        </a>

                                    </td>


                                    <td>

                                        {{ $sale->customer?->name ?? 'Umum' }}

                                    </td>


                                    <td>

                                        {{ $sale->date?->format('d/m/Y') }}

                                    </td>


                                    <td class="text-end">

                                        Rp{{ number_format(
                                            $sale->final_price,
                                            0,
                                            ',',
                                            '.'
                                        ) }}

                                    </td>


                                    <td>

                                        @if($sale->payment_status === 'lunas')

                                            <span class="badge bg-success">

                                                Lunas

                                            </span>

                                        @else

                                            <span class="badge bg-danger">

                                                Belum Lunas

                                            </span>

                                        @endif

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="5"
                                        class="text-center text-muted py-4"
                                    >

                                        Belum ada transaksi penjualan.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        {{-- ==================================================
            RECENT PURCHASES
        =================================================== --}}

        <div class="col-lg-5">

            <div class="card">

                <div class="card-header">

                    <h3 class="card-title">
                        Pembelian Terbaru
                    </h3>


                    <div class="card-actions">

                        <a
                            href="{{ route('purchases.index') }}"
                            class="btn btn-sm btn-outline-primary"
                        >

                            Lihat Semua

                        </a>

                    </div>

                </div>


                <div class="table-responsive">

                    <table class="table table-vcenter card-table">

                        <thead>

                            <tr>

                                <th>
                                    Invoice
                                </th>

                                <th>
                                    Supplier
                                </th>

                                <th class="text-end">
                                    Total
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($recentPurchases as $purchase)

                                <tr>

                                    <td>

                                        <a
                                            href="{{ route('purchases.show', $purchase) }}"
                                            class="fw-semibold text-decoration-none"
                                        >

                                            {{ $purchase->invoice }}

                                        </a>

                                        <div class="small text-muted">

                                            {{ $purchase->date?->format('d/m/Y') }}

                                        </div>

                                    </td>


                                    <td>

                                        {{ $purchase->supplier?->name ?? '-' }}

                                    </td>


                                    <td class="text-end">

                                        Rp{{ number_format(
                                            $purchase->total,
                                            0,
                                            ',',
                                            '.'
                                        ) }}

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="3"
                                        class="text-center text-muted py-4"
                                    >

                                        Belum ada transaksi pembelian.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const chartElement =
            document.querySelector(
                '#sales-chart'
            );


        if (!chartElement) {
            return;
        }


        const chartData =
            @json($salesChart);


        const options = {

            chart: {

                type: 'bar',

                height: 280,

                toolbar: {
                    show: false
                }

            },


            series: [

                {

                    name: 'Penjualan',

                    data: chartData.map(
                        item => item.amount
                    )

                }

            ],


            xaxis: {

                categories:
                    chartData.map(
                        item => item.label
                    )

            },


            yaxis: {

                labels: {

                    formatter:
                        function (value) {

                            return 'Rp ' +
                                Number(value)
                                    .toLocaleString(
                                        'id-ID'
                                    );

                        }

                    }

                },


            tooltip: {

                y: {

                    formatter:
                        function (value) {

                            return 'Rp ' +
                                Number(value)
                                    .toLocaleString(
                                        'id-ID'
                                    );

                        }

                }

            },


            dataLabels: {

                enabled: false

            }

        };


        const chart =
            new ApexCharts(
                chartElement,
                options
            );


        chart.render();

    }

);

</script>


@endsection