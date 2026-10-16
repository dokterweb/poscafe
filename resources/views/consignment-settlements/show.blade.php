@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h2 class="mb-1">Detail Settlement</h2>
            <div class="text-muted">
                {{ $settlement->settlement_number }}
            </div>
        </div>

        <a href="{{ route('consignment-settlements.index') }}"
           class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>
            Kembali
        </a>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
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

    {{-- Informasi Settlement --}}
    <div class="row row-cards mb-4">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Informasi Settlement</h3>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Nomor Settlement</div>
                            <div class="fw-bold">
                                {{ $settlement->settlement_number }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted small">Mitra</div>
                            <div class="fw-bold">
                                {{ $settlement->partner->name ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted small">Periode</div>
                            <div>
                                @if ($settlement->period_start || $settlement->period_end)
                                    {{ $settlement->period_start?->format('d/m/Y') ?? '—' }}
                                    s/d
                                    {{ $settlement->period_end?->format('d/m/Y') ?? '—' }}
                                @else
                                    Tidak ditentukan
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted small">Dibuat Oleh</div>
                            <div>
                                {{ $settlement->creator->name ?? '-' }}
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="text-muted small">Catatan</div>
                            <div>{{ $settlement->notes ?: 'Tidak ada catatan.' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted">Total Settlement</div>

                    <div class="h1 mb-3">
                        Rp {{ number_format((float) $settlement->total_amount, 0, ',', '.') }}
                    </div>

                    <div class="text-muted small mb-1">Status</div>

                    @if ($settlement->status === 'paid')
                        <span class="badge bg-success text-white">Paid</span>
                    @else
                        <span class="badge bg-warning text-dark">Draft</span>
                    @endif

                    @if ($settlement->status === 'paid')
                        <hr>

                        <div class="text-muted small">Metode Pembayaran</div>
                        <div class="fw-bold mb-3">
                            @switch($settlement->payment_method)
                                @case('cash')
                                    Tunai
                                    @break
                                @case('transfer')
                                    Transfer Bank
                                    @break
                                @case('qris')
                                    QRIS
                                    @break
                                @default
                                    {{ $settlement->payment_method ?? '-' }}
                            @endswitch
                        </div>

                        <div class="text-muted small">Referensi Pembayaran</div>
                        <div class="mb-3">
                            {{ $settlement->payment_reference ?: '—' }}
                        </div>

                        <div class="text-muted small">Tanggal Pembayaran</div>
                        <div>
                            {{ $settlement->paid_at?->format('d/m/Y H:i') ?? '—' }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Rincian Item --}}
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Rincian Item Settlement</h3>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter table-striped card-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Produk</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Harga Mitra</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($settlement->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <div class="fw-bold">{{ $item->product_name }}</div>
                                <div class="text-muted small">
                                    Order item #{{ $item->order_item_id }}
                                </div>
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
                            <td colspan="5" class="text-center py-4">
                                Belum ada item settlement.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="4" class="text-end">TOTAL</td>
                        <td class="text-end text-nowrap">
                            Rp {{ number_format((float) $settlement->items->sum('total_amount'), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Form Pembayaran: hanya untuk draft --}}
    @if ($settlement->status === 'draft')
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-wallet me-1"></i>
                    Bayar Settlement
                </h3>
            </div>

            <form method="POST"
                  action="{{ route('consignment-settlements.pay', $settlement->id) }}"
                  id="paymentForm">
                @csrf

                <div class="card-body">
                    <div class="alert alert-warning">
                        Pastikan jumlah dan rincian settlement sudah benar.
                        Setelah pembayaran dikonfirmasi, status akan berubah menjadi
                        <strong>Paid</strong>.
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="payment_method" class="form-label">
                                Metode Pembayaran <span class="text-danger">*</span>
                            </label>

                            <select name="payment_method"
                                    id="payment_method"
                                    class="form-select"
                                    required>
                                <option value="">-- Pilih Metode --</option>
                                <option value="cash" @selected(old('payment_method') === 'cash')>
                                    Tunai
                                </option>
                                <option value="transfer" @selected(old('payment_method') === 'transfer')>
                                    Transfer Bank
                                </option>
                                <option value="qris" @selected(old('payment_method') === 'qris')>
                                    QRIS
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="payment_reference" class="form-label">
                                Referensi Pembayaran
                            </label>

                            <input type="text"
                                   name="payment_reference"
                                   id="payment_reference"
                                   class="form-control"
                                   maxlength="100"
                                   value="{{ old('payment_reference') }}"
                                   placeholder="Nomor referensi / bukti transfer">

                            <div class="form-hint">
                                Opsional, isi jika tersedia.
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="paid_at" class="form-label">
                                Tanggal dan Waktu Pembayaran
                                <span class="text-danger">*</span>
                            </label>

                            <input type="datetime-local"
                                   name="paid_at"
                                   id="paid_at"
                                   class="form-control"
                                   value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}"
                                   required>
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-check me-1"></i>
                        Konfirmasi Pembayaran
                    </button>
                </div>
            </form>
        </div>

        <script>
        document.getElementById('paymentForm').addEventListener('submit', function (event) {
            if (!confirm(
                'Konfirmasi pembayaran settlement {{ $settlement->settlement_number }} sebesar Rp {{ number_format((float) $settlement->total_amount, 0, ',', '.') }}?'
            )) {
                event.preventDefault();
            }
        });
        </script>
    @endif

</div>
@endsection
