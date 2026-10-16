@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h2 class="mb-1">Detail Retur Konsinyasi</h2>
            <div class="text-muted">{{ $return->return_number }}</div>
        </div>

        <a href="{{ route('consignment-returns.index') }}"
           class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Kembali
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

    <div class="row row-cards mb-4">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Informasi Retur</h3>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Nomor Retur</div>
                            <div class="fw-bold">{{ $return->return_number }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted small">Mitra</div>
                            <div class="fw-bold">{{ $return->partner->name ?? '-' }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted small">Tanggal Retur</div>
                            <div>{{ $return->return_date?->format('d/m/Y') }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted small">Jenis Retur</div>
                            <div>
                                {{ $return->return_type === 'stock_return'
                                    ? 'Retur stok belum terjual'
                                    : 'Retur terkait penjualan' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted small">Alasan</div>
                            <div>{{ $return->reason }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted small">Dibuat oleh</div>
                            <div>{{ $return->creator->name ?? '-' }}</div>
                        </div>

                        <div class="col-12">
                            <div class="text-muted small">Catatan</div>
                            <div>{{ $return->notes ?: 'Tidak ada catatan.' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Status Retur</div>

                    @if ($return->status === 'processed')
                        <span class="badge bg-success text-white">Processed</span>
                    @elseif ($return->status === 'cancelled')
                        <span class="badge bg-secondary text-white">Cancelled</span>
                    @else
                        <span class="badge bg-warning text-dark">Draft</span>
                    @endif

                    <hr>

                    <div class="text-muted small">Kebijakan Finansial</div>
                    <div class="fw-bold mb-3">
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
                    </div>

                    @if ($return->processed_at)
                        <div class="text-muted small">Diproses pada</div>
                        <div>{{ $return->processed_at->format('d/m/Y H:i') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Rincian Item Retur</h3>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter table-striped card-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Produk</th>
                        <th>Referensi</th>
                        <th class="text-center">Qty Retur</th>
                        <th class="text-end">Harga Mitra</th>
                        <th class="text-end">Nilai Retur</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($return->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>{{ $item->product_name }}</td>

                            <td>
                                @if ($item->order_item_id)
                                    Item penjualan #{{ $item->order_item_id }}
                                @else
                                    Retur stok
                                @endif

                                @if ($item->consignment_settlement_id)
                                    <div class="text-muted small">
                                        Settlement #{{ $item->consignment_settlement_id }}
                                    </div>
                                @endif
                            </td>

                            <td class="text-center">
                                {{ number_format((int) $item->quantity, 0, ',', '.') }}
                            </td>

                            <td class="text-end text-nowrap">
                                Rp {{ number_format((float) $item->partner_unit_price, 0, ',', '.') }}
                            </td>

                            <td class="text-end text-nowrap">
                                Rp {{ number_format((float) $item->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                Tidak ada item retur.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="5" class="text-end">TOTAL NILAI RETUR</td>
                        <td class="text-end text-nowrap">
                            Rp {{ number_format((float) $return->items->sum('total_amount'), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if ($return->status === 'draft')
        <div class="card border-warning">
            <div class="card-body">
                <h3 class="mb-2">Proses Retur</h3>

                <p class="text-muted">
                    Periksa kembali jumlah barang, alasan, dan kebijakan finansial.
                    Setelah diproses, retur tidak dapat diproses ulang.
                </p>

                <form method="POST"
                      action="{{ route('consignment-returns.process', $return->id) }}"
                      onsubmit="return confirm('Proses retur ini sekarang? Pastikan semua rincian sudah benar.');">
                    @csrf

                    <button type="submit" class="btn btn-warning">
                        <i class="ti ti-check me-1"></i>
                        Proses Retur
                    </button>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection
