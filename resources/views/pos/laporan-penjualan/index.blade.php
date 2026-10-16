@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="page-header d-print-none">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Laporan Penjualan</h2>
                <div class="text-secondary mt-1">
                    Pantau omzet, metode pembayaran, dan riwayat transaksi yang sudah dibayar.
                </div>
            </div>

            <div class="col-auto ms-auto">
                <a href="{{ route('pos.index') }}" class="btn btn-primary">
                    <i class="ti ti-shopping-cart me-1"></i>
                    Kasir POS
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-fluid">

        
{{-- FILTER LAPORAN --}}
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="ti ti-filter me-2"></i>
            Filter Laporan
        </h3>
    </div>

    <div class="card-body">
        <form action="{{ route('pos.laporan-penjualan.index') }}"
              method="GET">

            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label for="tanggal_mulai" class="form-label">
                        Tanggal Mulai
                    </label>
                    <input
                        type="date"
                        id="tanggal_mulai"
                        name="tanggal_mulai"
                        class="form-control"
                        value="{{ $tanggalMulai }}"
                        required
                    >
                </div>

                <div class="col-12 col-md-3">
                    <label for="tanggal_akhir" class="form-label">
                        Tanggal Akhir
                    </label>
                    <input
                        type="date"
                        id="tanggal_akhir"
                        name="tanggal_akhir"
                        class="form-control"
                        value="{{ $tanggalAkhir }}"
                        min="{{ $tanggalMulai }}"
                        required
                    >
                </div>

                <div class="col-12 col-md-3">
                    <label for="keyword" class="form-label">
                        Nomor Pesanan
                    </label>
                    <input
                        type="text"
                        id="keyword"
                        name="keyword"
                        class="form-control"
                        placeholder="Cari nomor pesanan..."
                        value="{{ $keyword }}"
                    >
                </div>

                <div class="col-12 col-md-3">
                    <label for="payment_method" class="form-label">
                        Metode Pembayaran
                    </label>
                    <select
                        id="payment_method"
                        name="payment_method"
                        class="form-select"
                    >
                        <option value="">Semua metode</option>
                        <option value="cash"
                            @selected($paymentMethod === 'cash')>
                            Tunai
                        </option>
                        <option value="transfer"
                            @selected($paymentMethod === 'transfer')>
                            Transfer
                        </option>
                        <option value="qris"
                            @selected($paymentMethod === 'qris')>
                            QRIS
                        </option>
                    </select>
                </div>

                <div class="col-12">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-search me-1"></i>
                            Cari Transaksi
                        </button>

                        <button
                            type="submit"
                            formaction="{{ route('pos.laporan-penjualan.export') }}"
                            formmethod="GET"
                            class="btn btn-success"
                        >
                            <i class="ti ti-file-spreadsheet me-1"></i>
                            Export Excel
                        </button>

                        <a
                            href="{{ route('pos.laporan-penjualan.index') }}"
                            class="btn btn-outline-secondary"
                        >
                            <i class="ti ti-refresh me-1"></i>
                            Reset Filter
                        </a>
                    </div>
                </div>

            </div>

            @if($errors->any())
                <div class="alert alert-danger mt-3 mb-0">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </form>
    </div>
</div>


        {{-- RINGKASAN PENJUALAN --}}
        <div class="row row-deck row-cards mb-3">
            <div class="col-12 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Omzet</div>
                            <div class="ms-auto text-success">
                                <i class="ti ti-wallet fs-2"></i>
                            </div>
                        </div>

                        <div class="h1 mb-1">
                            Rp {{ number_format($totalOmzet, 0, ',', '.') }}
                        </div>

                        <div class="text-secondary small">
                            Periode {{ \Carbon\Carbon::parse($tanggalMulai)->format('d/m/Y') }}
                            – {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d/m/Y') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Jumlah Transaksi</div>
                            <div class="ms-auto text-primary">
                                <i class="ti ti-receipt fs-2"></i>
                            </div>
                        </div>

                        <div class="h1 mb-1">
                            {{ number_format($jumlahTransaksi, 0, ',', '.') }}
                        </div>

                        <div class="text-secondary small">
                            Transaksi berhasil dibayar
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Rata-rata Transaksi</div>
                            <div class="ms-auto text-info">
                                <i class="ti ti-chart-bar fs-2"></i>
                            </div>
                        </div>

                        <div class="h1 mb-1">
                            Rp {{ number_format($rataRataTransaksi, 0, ',', '.') }}
                        </div>

                        <div class="text-secondary small">
                            Omzet dibagi jumlah transaksi
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- REKAP METODE PEMBAYARAN --}}
        <div class="row row-cards mb-3">
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

            @foreach($metodePembayaran as $kode => $metode)
                @php
                    $rekap = $rekapPembayaran->get($kode);
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
                                        transaksi
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- RIWAYAT TRANSAKSI --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Riwayat Transaksi
                </h3>

                <div class="card-actions text-secondary small">
                    {{ $transaksi->total() }} transaksi
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
                        @forelse($transaksi as $index => $order)
                            <tr>
                                <td>
                                    {{ $transaksi->firstItem() + $index }}
                                </td>

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
                                        <span class="badge bg-azure-lt">
                                            Dine-in
                                        </span>
                                    @else
                                        <span class="badge bg-purple-lt">
                                            Takeaway
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @switch($order->payment?->payment_method)
                                        @case('cash')
                                            <span class="badge bg-success-lt">
                                                Tunai
                                            </span>
                                            @break

                                        @case('transfer')
                                            <span class="badge bg-primary-lt">
                                                Transfer
                                            </span>
                                            @break

                                        @case('qris')
                                            <span class="badge bg-info-lt">
                                                QRIS
                                            </span>
                                            @break

                                        @default
                                            <span class="badge bg-secondary-lt">
                                                -
                                            </span>
                                    @endswitch
                                </td>

                                <td class="text-end fw-bold">
                                    Rp {{ number_format((float) $order->total, 0, ',', '.') }}
                                </td>

                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('pos.orders.show', $order->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Detail transaksi"
                                           aria-label="Detail transaksi">
                                            <i class="far fa-eye"></i>
                                        </a>

                                        <a href="{{ route('pos.orders.receipt', $order->id) }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Cetak ulang struk"
                                           aria-label="Cetak ulang struk">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="text-center text-secondary py-5">
                                        <i class="ti ti-report-analytics fs-1 d-block mb-2"></i>

                                        <div class="fw-bold">
                                            Belum ada transaksi
                                        </div>

                                        <div class="small">
                                            Tidak ditemukan transaksi yang sudah dibayar
                                            pada periode yang dipilih.
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transaksi->hasPages())
                <div class="card-footer">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <div class="text-secondary small">
                            Menampilkan {{ $transaksi->firstItem() ?? 0 }}
                            – {{ $transaksi->lastItem() ?? 0 }}
                            dari {{ $transaksi->total() }} transaksi
                        </div>

                        {{ $transaksi->links() }}
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
