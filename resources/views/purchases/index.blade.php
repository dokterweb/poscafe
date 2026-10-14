@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">Transaksi</div>
                    <h2 class="page-title">Pembelian</h2>
                </div>
                <div class="col-auto ms-auto">
                    <a href="{{ route('purchases.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus"></i>
                        Pembelian Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-1"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            <div class="row g-3 mb-4">

                <div class="col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="text-muted small mb-1">
                                Total Transaksi
                            </div>

                            <div class="fs-4 fw-semibold">

                                {{ number_format($totalTransaction, 0, ',', '.') }}

                            </div>

                            <div class="text-muted small">
                                transaksi pembelian
                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="text-muted small mb-1">
                                Total Nilai Pembelian
                            </div>

                            <div class="fs-4 fw-semibold text-primary">

                                Rp {{ number_format($totalPurchase, 0, ',', '.') }}

                            </div>

                            <div class="text-muted small">
                                berdasarkan filter
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            {{-- ======================================================
                FILTER
                ====================================================== --}}
            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body">

                    <form method="GET" action="{{ route('purchases.index') }}">

                        <div class="row g-3 align-items-end">

                            {{-- SEARCH --}}
                            <div class="col-md-5">

                                <label class="form-label fw-semibold">
                                    Cari
                                </label>

                                <input type="text"
                                    name="search"
                                    class="form-control"
                                    value="{{ request('search') }}"
                                    placeholder="Cari invoice atau nama supplier...">

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


                            {{-- BUTTON SEARCH --}}

                            <div class="col-md-3">

                                <div class="d-flex gap-2">

                                    <button type="submit"
                                            class="btn btn-primary">

                                        <i class="bi bi-search me-1"></i>

                                        Cari

                                    </button>


                                    <a href="{{ route('purchases.index') }}"
                                    class="btn btn-outline-secondary">

                                        <i class="bi bi-arrow-counterclockwise me-1"></i>

                                        Reset

                                    </a>

                                </div>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

            <div class="card border-0 shadow-sm">

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th width="60">#</th>
                                    <th>Invoice</th>
                                    <th>Supplier</th>
                                    <th>Tanggal</th>
                                    <th class="text-end">Total</th>
                                    <th>User</th>
                                    <th width="120" class="text-center">Aksi</th>
                                </tr>

                            </thead>
                            <tbody>
                                @forelse($purchases as $purchase)
                                    <tr>
                                        <td>{{ $purchases->firstItem() + $loop->index }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $purchase->invoice }}</div>
                                        </td>
                                        <td>
                                            @if($purchase->supplier)
                                                <div class="fw-semibold">{{ $purchase->supplier->name }}</div>
                                                @if($purchase->supplier->phone)
                                                    <small class="text-muted">{{ $purchase->supplier->phone }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $purchase->date?->format('d/m/Y') }}</td>
                                        <td class="text-end">
                                            <span class="fw-semibold">Rp{{ number_format($purchase->total, 0, ',', '.') }}</span>
                                        </td>
                                        <td>{{ $purchase->user?->name ?? '-' }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                                <i class="far fa-eye"></i>
                                            </a>
                                            {{-- DELETE --}}
                                            <form action="{{ route('purchases.destroy', $purchase) }}" method="POST" class="d-inline"
                                                onsubmit="return confirmDeletePurchase('{{ $purchase->invoice }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                                Belum ada transaksi pembelian.
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
    </div>
</div>

@endsection