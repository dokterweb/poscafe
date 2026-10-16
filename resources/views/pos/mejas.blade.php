@extends('layouts.app')

@section('title', 'Manajemen Meja')

@section('content')
<div class="page-header d-print-none">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Manajemen Meja</h2>
                <div class="text-secondary mt-1">
                    Pantau meja kosong dan pesanan dine-in yang sedang berjalan.
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

        {{-- RINGKASAN --}}
        <div class="row row-deck row-cards mb-3">
            <div class="col-6 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Total Meja Aktif</div>
                        <div class="h1 mb-0">{{ $totalMeja }}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Meja Kosong</div>
                        <div class="h1 mb-0 text-success">{{ $mejaKosong }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Meja Terisi</div>
                        <div class="h1 mb-0 text-warning">{{ $mejaTerisi }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- FILTER --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        <input
                            type="text"
                            id="search-meja"
                            class="form-control"
                            placeholder="Cari nomor atau nama meja..."
                        >
                    </div>

                    <div class="col-md-4">
                        <select id="filter-status" class="form-select">
                            <option value="">Semua status</option>
                            <option value="kosong">Kosong</option>
                            <option value="terisi">Terisi</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="button" id="reset-filter"
                                class="btn btn-outline-secondary w-100">
                            Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU MEJA --}}
        <div class="row row-cards" id="meja-list">
            @forelse($mejas as $meja)
                @php
                    $order = $meja->orders->first();
                    $terisi = $order !== null;
                @endphp

                <div
                    class="col-6 col-md-4 col-xl-3 meja-card"
                    data-name="{{ strtolower($meja->nomor . ' ' . ($meja->nama ?? '')) }}"
                    data-status="{{ $terisi ? 'terisi' : 'kosong' }}"
                >
                    <div class="card h-100 {{ $terisi ? 'border-warning' : 'border-success' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <div class="h2 mb-1">{{ $meja->nomor }}</div>
                                    <div class="text-secondary small">
                                        {{ $meja->nama ?: 'Meja' }}
                                    </div>
                                </div>

                                @if($terisi)
                                    <span class="badge bg-warning-lt">Terisi</span>
                                @else
                                    <span class="badge bg-success-lt">Kosong</span>
                                @endif
                            </div>

                            <div class="mt-3 text-secondary small">
                                <i class="ti ti-users me-1"></i>
                                {{ $meja->kapasitas }} kursi
                            </div>

                            @if($terisi)
                                <hr>

                                <div class="small text-secondary">
                                    Nomor pesanan
                                </div>
                                <div class="fw-bold text-break">
                                    {{ $order->order_number }}
                                </div>

                                <div class="mt-2 small text-secondary">
                                    {{ $order->items->sum('quantity') }} item
                                </div>

                                <div class="h3 mt-1 mb-3 text-warning">
                                    Rp {{ number_format($order->total, 0, ',', '.') }}
                                </div>

                                <a href="{{ route('pos.orders.show', $order->id) }}"
                                   class="btn btn-warning w-100">
                                    <i class="ti ti-edit me-1"></i>
                                    Buka Pesanan
                                </a>
                            @else
                                <div class="mt-3 mb-3 text-secondary small">
                                    Belum ada pesanan aktif.
                                </div>

                                <a href="{{ route('pos.index', ['meja_id' => $meja->id]) }}"
                                   class="btn btn-outline-success w-100">
                                    <i class="ti ti-plus me-1"></i>
                                    Buat Pesanan
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center text-secondary py-5">
                            Belum ada meja aktif. Tambahkan meja melalui Master Meja.
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <div id="empty-meja-filter" class="text-center text-secondary py-5 d-none">
            Tidak ada meja yang sesuai dengan pencarian.
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function () {
    function filterMeja() {
        const keyword = $('#search-meja').val().toLowerCase().trim();
        const status = $('#filter-status').val();
        let visible = 0;

        $('.meja-card').each(function () {
            const name = String($(this).data('name'));
            const mejaStatus = String($(this).data('status'));

            const matchName = name.includes(keyword);
            const matchStatus = !status || status === mejaStatus;
            const show = matchName && matchStatus;

            $(this).toggle(show);

            if (show) visible++;
        });

        $('#empty-meja-filter').toggleClass('d-none', visible > 0);
    }

    $('#search-meja').on('input', filterMeja);
    $('#filter-status').on('change', filterMeja);

    $('#reset-filter').on('click', function () {
        $('#search-meja').val('');
        $('#filter-status').val('');
        filterMeja();
    });
});
</script>
@endsection
