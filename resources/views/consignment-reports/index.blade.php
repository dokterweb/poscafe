@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h2 class="mb-1">Laporan Konsinyasi</h2>
            <div class="text-muted">
                Ringkasan penjualan, keuntungan kafe, dan kewajiban pembayaran mitra.
            </div>
        </div>
    </div>

    {{-- Filter Periode --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET"
                  action="{{ route('consignment-reports.index') }}"
                  class="row g-3 align-items-end">

                <div class="col-md-4">
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input
                        type="date"
                        name="start_date"
                        id="start_date"
                        class="form-control"
                        value="{{ $startDate }}"
                    >
                </div>

                <div class="col-md-4">
                    <label for="end_date" class="form-label">Tanggal Akhir</label>
                    <input
                        type="date"
                        name="end_date"
                        id="end_date"
                        class="form-control"
                        value="{{ $endDate }}"
                    >
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-filter me-1"></i>
                        Filter
                    </button>

                    <a href="{{ route('consignment-reports.index') }}"
                       class="btn btn-outline-secondary">
                        <i class="ti ti-refresh me-1"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Kartu Ringkasan --}}
    <div class="row row-cards mb-4">

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">Total Penjualan</div>
                    <div class="h2 mb-1">
                        Rp {{ number_format((float) ($summary['sales_amount'] ?? 0), 0, ',', '.') }}
                    </div>
                    <div class="text-muted small">Nilai penjualan produk konsinyasi</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">Hak Mitra</div>
                    <div class="h2 mb-1 text-primary">
                        Rp {{ number_format((float) ($summary['partner_amount'] ?? 0), 0, ',', '.') }}
                    </div>
                    <div class="text-muted small">Total nilai yang menjadi hak mitra</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">Keuntungan Kafe</div>
                    <div class="h2 mb-1 text-success">
                        Rp {{ number_format((float) ($summary['cafe_profit'] ?? 0), 0, ',', '.') }}
                    </div>
                    <div class="text-muted small">Selisih harga jual dan harga mitra</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">Sisa Kewajiban</div>
                    <div class="h2 mb-1 text-danger">
                        Rp {{ number_format((float) ($summary['outstanding_amount'] ?? 0), 0, ',', '.') }}
                    </div>
                    <div class="text-muted small">Hak mitra yang belum dibayar</div>
                </div>
            </div>
        </div>

    </div>

    {{-- Ringkasan Settlement --}}
    <div class="row row-cards mb-4">

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">Settlement Sudah Dibayar</div>
                    <div class="h3 mb-0 text-success">
                        Rp {{ number_format((float) ($summary['settled_amount'] ?? 0), 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">Settlement Draft</div>
                    <div class="h3 mb-0">
                        Rp {{ number_format((float) ($summary['draft_amount'] ?? 0), 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Tabel Laporan Per Mitra --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Laporan Per Mitra Konsinyasi</h3>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Mitra</th>
                        <th class="text-center">Jumlah Item</th>
                        <th class="text-center">Total Qty</th>
                        <th class="text-end">Penjualan</th>
                        <th class="text-end">Hak Mitra</th>
                        <th class="text-end">Profit Kafe</th>
                        <th class="text-end">Sudah Dibayar</th>
                        <th class="text-end">Settlement Draft</th>
                        <th class="text-end">Sisa Kewajiban</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($reports as $report)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td class="fw-bold">
                                {{ $report->partner_name }}
                            </td>

                            <td class="text-center">
                                {{ number_format((int) $report->transaction_items, 0, ',', '.') }}
                            </td>

                            <td class="text-center">
                                {{ number_format((int) $report->total_quantity, 0, ',', '.') }}
                            </td>

                            <td class="text-end text-nowrap">
                                Rp {{ number_format((float) $report->sales_amount, 0, ',', '.') }}
                            </td>

                            <td class="text-end text-nowrap">
                                Rp {{ number_format((float) $report->partner_amount, 0, ',', '.') }}
                            </td>

                            <td class="text-end text-nowrap text-success">
                                Rp {{ number_format((float) $report->cafe_profit, 0, ',', '.') }}
                            </td>

                            <td class="text-end text-nowrap">
                                Rp {{ number_format((float) $report->settled_amount, 0, ',', '.') }}
                            </td>

                            <td class="text-end text-nowrap">
                                Rp {{ number_format((float) $report->draft_amount, 0, ',', '.') }}
                            </td>

                            <td class="text-end text-nowrap fw-bold
                                {{ (float) $report->outstanding_amount > 0 ? 'text-danger' : 'text-success' }}">
                                Rp {{ number_format((float) $report->outstanding_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="empty">
                                    <div class="empty-icon">
                                        <i class="ti ti-report-analytics"
                                           style="font-size: 3rem;"></i>
                                    </div>
                                    <p class="empty-title">Belum ada data konsinyasi</p>
                                    <p class="empty-subtitle text-muted">
                                        Tidak ditemukan penjualan konsinyasi pada periode ini.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if ($reports->isNotEmpty())
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="2">TOTAL</td>

                            <td class="text-center">
                                {{ number_format((int) ($summary['transaction_items'] ?? 0), 0, ',', '.') }}
                            </td>

                            <td class="text-center">
                                {{ number_format((int) ($summary['total_quantity'] ?? 0), 0, ',', '.') }}
                            </td>

                            <td class="text-end text-nowrap">
                                Rp {{ number_format((float) ($summary['sales_amount'] ?? 0), 0, ',', '.') }}
                            </td>

                            <td class="text-end text-nowrap">
                                Rp {{ number_format((float) ($summary['partner_amount'] ?? 0), 0, ',', '.') }}
                            </td>

                            <td class="text-end text-nowrap text-success">
                                Rp {{ number_format((float) ($summary['cafe_profit'] ?? 0), 0, ',', '.') }}
                            </td>

                            <td class="text-end text-nowrap">
                                Rp {{ number_format((float) ($summary['settled_amount'] ?? 0), 0, ',', '.') }}
                            </td>

                            <td class="text-end text-nowrap">
                                Rp {{ number_format((float) ($summary['draft_amount'] ?? 0), 0, ',', '.') }}
                            </td>

                            <td class="text-end text-nowrap text-danger">
                                Rp {{ number_format((float) ($summary['outstanding_amount'] ?? 0), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <div class="card-footer text-muted small">
            <i class="ti ti-info-circle me-1"></i>
            Settlement berstatus <strong>paid</strong> dianggap sudah dibayar.
            Settlement berstatus <strong>draft</strong> ditampilkan terpisah dan
            belum dianggap sebagai pembayaran.
        </div>
    </div>

</div>
@endsection
