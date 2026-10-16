@extends('layouts.app')

@section('title', 'Dashboard Penjualan')

@section('content')
<div class="page-header d-print-none">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Dashboard Penjualan</h2>
                <div class="text-secondary mt-1">
                    Ringkasan aktivitas penjualan PosCafe.
                </div>
            </div>

            <div class="col-auto ms-auto text-secondary">
                <i class="ti ti-calendar me-1"></i>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-fluid">

        {{-- RINGKASAN HARI INI --}}
        <div class="row row-cards mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="subheader">Omzet Hari Ini</div>
                            <i class="ti ti-wallet fs-2 text-success"></i>
                        </div>

                        <div class="h1 mb-1">
                            Rp {{ number_format($salesTodayAmount, 0, ',', '.') }}
                        </div>

                        <div class="text-secondary small">
                            Total transaksi yang sudah dibayar
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="subheader">Transaksi Hari Ini</div>
                            <i class="ti ti-receipt fs-2 text-primary"></i>
                        </div>

                        <div class="h1 mb-1">
                            {{ number_format($salesToday, 0, ',', '.') }}
                        </div>

                        <div class="text-secondary small">
                            Pesanan berstatus paid
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="subheader">Rata-rata Transaksi</div>
                            <i class="ti ti-chart-bar fs-2 text-info"></i>
                        </div>

                        <div class="h1 mb-1">
                            Rp {{ number_format($averageTransactionToday, 0, ',', '.') }}
                        </div>

                        <div class="text-secondary small">
                            Omzet dibagi jumlah transaksi
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="subheader">Omzet Bulan Ini</div>
                            <i class="ti ti-calendar-stats fs-2 text-warning"></i>
                        </div>

                        <div class="h1 mb-1">
                            Rp {{ number_format($salesThisMonthAmount, 0, ',', '.') }}
                        </div>

                        <div class="text-secondary small">
                            {{ number_format($salesThisMonth, 0, ',', '.') }}
                            transaksi bulan ini
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- GRAFIK DAN QUICK ACTION --}}
        <div class="row row-cards mb-3">

            <div class="col-12 col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">Penjualan 7 Hari Terakhir</h3>
                            <div class="text-secondary small">
                                Omzet berdasarkan tanggal pembayaran.
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div id="sales-chart" style="height: 300px;"></div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title">Akses Cepat</h3>
                    </div>

                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('pos.index') }}"
                               class="btn btn-primary">
                                <i class="ti ti-shopping-cart me-1"></i>
                                Buka Kasir POS
                            </a>

                            <a href="{{ route('pos.mejas.index') }}"
                               class="btn btn-outline-primary">
                                <i class="ti ti-armchair me-1"></i>
                                Manajemen Meja
                            </a>

                            <a href="{{ route('pos.laporan-penjualan.index') }}"
                               class="btn btn-outline-success">
                                <i class="ti ti-report-analytics me-1"></i>
                                Laporan Penjualan
                            </a>

                            <a href="{{ route('pos.laporan-penjualan.export') }}"
                               class="btn btn-outline-secondary">
                                <i class="ti ti-file-spreadsheet me-1"></i>
                                Export Excel Hari Ini
                            </a>
                        </div>

                        <div class="alert alert-info mt-3 mb-0">
                            <div class="d-flex">
                                <i class="ti ti-info-circle me-2 fs-2"></i>
                                <div class="small">
                                    Dashboard menampilkan transaksi yang
                                    sudah dibayar. Pesanan yang masih open
                                    tidak masuk perhitungan omzet.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- REKAP PEMBAYARAN --}}
        @php
            $metodePembayaran = [
                'cash' => [
                    'label' => 'Tunai',
                    'icon' => 'ti-cash',
                    'color' => 'success',
                ],
                'transfer' => [
                    'label' => 'Transfer',
                    'icon' => 'ti-building-bank',
                    'color' => 'primary',
                ],
                'qris' => [
                    'label' => 'QRIS',
                    'icon' => 'ti-qrcode',
                    'color' => 'info',
                ],
            ];
        @endphp

        <div class="row row-cards mb-3">
            @foreach($metodePembayaran as $kode => $metode)
                @php
                    $rekap = $paymentSummary->get($kode);
                @endphp

                <div class="col-12 col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-{{ $metode['color'] }}-lt">
                                    <i class="ti {{ $metode['icon'] }} fs-2"></i>
                                </span>

                                <div class="ms-3">
                                    <div class="subheader">
                                        Pembayaran {{ $metode['label'] }}
                                    </div>

                                    <div class="h2 mb-1">
                                        Rp {{ number_format((float) ($rekap->total ?? 0), 0, ',', '.') }}
                                    </div>

                                    <div class="text-secondary small">
                                        {{ number_format((int) ($rekap->jumlah_transaksi ?? 0), 0, ',', '.') }}
                                        transaksi hari ini
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- TRANSAKSI TERBARU --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Transaksi Terbaru</h3>

                <div class="card-actions">
                    <a href="{{ route('pos.laporan-penjualan.index') }}"
                       class="btn btn-sm btn-outline-primary">
                        Lihat Laporan
                        <i class="ti ti-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nomor Pesanan</th>
                            <th>Tanggal Pembayaran</th>
                            <th>Tipe Pesanan</th>
                            <th>Metode Pembayaran</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <div class="fw-bold text-break">
                                        {{ $order->order_number }}
                                    </div>
                                </td>

                                <td>
                                    @if($order->payment?->paid_at)
                                        {{ $order->payment->paid_at->format('d/m/Y') }}
                                        <div class="text-secondary small">
                                            {{ $order->payment->paid_at->format('H:i') }}
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    @if($order->order_type === 'dine_in')
                                        <span class="badge bg-azure-lt">Dine-in</span>
                                    @else
                                        <span class="badge bg-purple-lt">Takeaway</span>
                                    @endif
                                </td>

                                <td>
                                    @switch($order->payment?->payment_method)
                                        @case('cash')
                                            <span class="badge bg-success-lt">Tunai</span>
                                            @break

                                        @case('transfer')
                                            <span class="badge bg-primary-lt">Transfer</span>
                                            @break

                                        @case('qris')
                                            <span class="badge bg-info-lt">QRIS</span>
                                            @break

                                        @default
                                            -
                                    @endswitch
                                </td>

                                <td class="text-end fw-bold">
                                    Rp {{ number_format((float) $order->total, 0, ',', '.') }}
                                </td>

                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('pos.orders.show', $order->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Detail transaksi">
                                            <i class="ti ti-eye"></i>
                                        </a>

                                        <a href="{{ route('pos.orders.receipt', $order->id) }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Cetak struk">
                                            <i class="ti ti-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7"
                                    class="text-center text-secondary py-5">
                                    <i class="ti ti-receipt-off fs-1 d-block mb-2"></i>
                                    Belum ada transaksi yang dibayar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartElement = document.querySelector('#sales-chart');

    if (!chartElement || typeof ApexCharts === 'undefined') {
        return;
    }

    const chartData = @json($salesChart);

    const options = {
        chart: {
            type: 'bar',
            height: 300,
            toolbar: {
                show: false
            }
        },

        series: [{
            name: 'Omzet',
            data: chartData.map(item => item.amount)
        }],

        xaxis: {
            categories: chartData.map(item => item.label)
        },

        yaxis: {
            labels: {
                formatter: function (value) {
                    return 'Rp ' + Number(value).toLocaleString('id-ID');
                }
            }
        },

        tooltip: {
            y: {
                formatter: function (value) {
                    return 'Rp ' + Number(value).toLocaleString('id-ID');
                }
            }
        },

        dataLabels: {
            enabled: false
        },

        noData: {
            text: 'Belum ada data penjualan'
        }
    };

    const chart = new ApexCharts(chartElement, options);
    chart.render();
});
</script>
@endsection
