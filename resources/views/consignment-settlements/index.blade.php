@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h2 class="mb-1">Settlement Konsinyasi</h2>
            <div class="text-muted">
                Kelola draft dan pembayaran hak mitra konsinyasi.
            </div>
        </div>

        <a href="{{ route('consignment-settlements.create') }}"
           class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>
            Buat Settlement
        </a>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <div class="d-flex">
                <div>
                    <i class="ti ti-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET"
                  action="{{ route('consignment-settlements.index') }}"
                  class="row g-3 align-items-end">

                <div class="col-md-5">
                    <label class="form-label" for="partner_id">Mitra</label>
                    <select name="partner_id" id="partner_id" class="form-select">
                        <option value="">Semua Mitra</option>
                        @foreach ($partners as $partner)
                            <option
                                value="{{ $partner->id }}"
                                @selected(request('partner_id') == $partner->id)
                            >
                                {{ $partner->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="status">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="draft" @selected(request('status') === 'draft')>
                            Draft
                        </option>
                        <option value="paid" @selected(request('status') === 'paid')>
                            Paid
                        </option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-filter me-1"></i> Filter
                    </button>

                    <a href="{{ route('consignment-settlements.index') }}"
                       class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter table-striped card-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nomor Settlement</th>
                        <th>Mitra</th>
                        <th>Periode</th>
                        <th class="text-center">Jumlah Item</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                        <th>Tanggal Pembayaran</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($settlements as $settlement)
                        <tr>
                            <td>
                                {{ $settlements->firstItem() + $loop->index }}
                            </td>

                            <td class="fw-bold">
                                <a href="{{ route('consignment-settlements.show', $settlement->id) }}">
                                    {{ $settlement->settlement_number }}
                                </a>
                            </td>

                            <td>
                                {{ $settlement->partner->name ?? '-' }}
                            </td>

                            <td>
                                @if ($settlement->period_start || $settlement->period_end)
                                    {{ $settlement->period_start?->format('d/m/Y') ?? '—' }}
                                    s/d
                                    {{ $settlement->period_end?->format('d/m/Y') ?? '—' }}
                                @else
                                    <span class="text-muted">Tidak ditentukan</span>
                                @endif
                            </td>

                            <td class="text-center">
                                {{ number_format($settlement->items_count, 0, ',', '.') }}
                            </td>

                            <td class="text-end text-nowrap">
                                Rp {{ number_format((float) $settlement->total_amount, 0, ',', '.') }}
                            </td>

                            <td>
                                @if ($settlement->status === 'paid')
                                    <span class="badge bg-success text-white">Paid</span>
                                @else
                                    <span class="badge bg-warning text-dark">Draft</span>
                                @endif
                            </td>

                            <td>
                                {{ $settlement->paid_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty">
                                    <p class="empty-title">Belum ada settlement</p>
                                    <p class="empty-subtitle text-muted">
                                        Klik Buat Settlement untuk memilih penjualan konsinyasi yang belum diselesaikan.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($settlements->hasPages())
            <div class="card-footer">
                {{ $settlements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
