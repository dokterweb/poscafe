@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h2 class="mb-1">Retur Konsinyasi</h2>
            <div class="text-muted">
                Catatan pengembalian barang dan penyesuaian mitra.
            </div>
        </div>

        <a href="{{ route('consignment-returns.create') }}"
           class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>
            Buat Retur
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
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

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET"
                  action="{{ route('consignment-returns.index') }}"
                  class="row g-3 align-items-end">

                <div class="col-md-4">
                    <label class="form-label">Mitra</label>
                    <select name="partner_id" class="form-select">
                        <option value="">Semua Mitra</option>
                        @foreach ($partners as $partner)
                            <option value="{{ $partner->id }}"
                                @selected(request('partner_id') == $partner->id)>
                                {{ $partner->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Jenis Retur</label>
                    <select name="return_type" class="form-select">
                        <option value="">Semua Jenis</option>
                        <option value="stock_return"
                            @selected(request('return_type') === 'stock_return')>
                            Retur Stok
                        </option>
                        <option value="sales_return"
                            @selected(request('return_type') === 'sales_return')>
                            Retur Penjualan
                        </option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="draft"
                            @selected(request('status') === 'draft')>Draft</option>
                        <option value="processed"
                            @selected(request('status') === 'processed')>Processed</option>
                        <option value="cancelled"
                            @selected(request('status') === 'cancelled')>Cancelled</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Filter</button>
                    <a href="{{ route('consignment-returns.index') }}"
                       class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter table-striped card-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nomor Retur</th>
                        <th>Tanggal</th>
                        <th>Mitra</th>
                        <th>Jenis</th>
                        <th>Alasan</th>
                        <th class="text-center">Item</th>
                        <th>Kebijakan Finansial</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($returns as $return)
                        <tr>
                            <td>{{ $returns->firstItem() + $loop->index }}</td>
                            <td class="fw-bold">
                                <a href="{{ route('consignment-returns.show', $return->id) }}">
                                    {{ $return->return_number }}
                                </a>
                            </td>
                            <td>{{ $return->return_date?->format('d/m/Y') }}</td>
                            <td>{{ $return->partner->name ?? '-' }}</td>
                            <td>
                                {{ $return->return_type === 'stock_return'
                                    ? 'Retur Stok'
                                    : 'Retur Penjualan' }}
                            </td>
                            <td>{{ $return->reason }}</td>
                            <td class="text-center">{{ $return->items_count }}</td>
                            <td>
                                @switch($return->financial_treatment)
                                    @case('no_adjustment')
                                        Tanpa penyesuaian
                                        @break
                                    @case('deduct_from_draft')
                                        Kurangi settlement draft
                                        @break
                                    @case('credit_next_settlement')
                                        Kredit settlement berikutnya
                                        @break
                                    @case('partner_responsibility')
                                        Tanggung jawab mitra
                                        @break
                                @endswitch
                            </td>
                            <td>
                                @if ($return->status === 'processed')
                                    <span class="badge bg-success text-white">Processed</span>
                                @elseif ($return->status === 'cancelled')
                                    <span class="badge bg-secondary text-white">Cancelled</span>
                                @else
                                    <span class="badge bg-warning text-dark">Draft</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                Belum ada catatan retur konsinyasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($returns->hasPages())
            <div class="card-footer">
                {{ $returns->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
