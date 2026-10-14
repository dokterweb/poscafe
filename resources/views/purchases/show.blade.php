@extends('layouts.app')

@section('content')

<div class="page-wrapper">

    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">Detail Pembelian</div>
                    <h2 class="page-title">Informasi lengkap transaksi pembelian.</h2>
                </div>
                <div class="col-auto ms-auto">
                     <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Kembali
                    </a>
                    <form action="{{ route('purchases.destroy', $purchase) }}" method="POST" class="d-inline" onsubmit="return confirmDeletePurchase();">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-trash me-1"></i>
                            Hapus Pembelian
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
  
<div class="page-body">
        <div class="container-xl">
            <div class="row g-3 mb-4">


                {{-- INVOICE --}}

                <div class="col-md-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="text-muted small mb-1">
                                Invoice
                            </div>

                            <div class="fw-semibold fs-5">

                                {{ $purchase->invoice }}

                            </div>

                        </div>

                    </div>

                </div>


                {{-- TANGGAL --}}

                <div class="col-md-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="text-muted small mb-1">
                                Tanggal
                            </div>

                            <div class="fw-semibold fs-5">

                                {{ $purchase->date?->format('d/m/Y') }}

                            </div>

                        </div>

                    </div>

                </div>


                {{-- SUPPLIER --}}

                <div class="col-md-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="text-muted small mb-1">
                                Supplier
                            </div>

                            <div class="fw-semibold">

                                {{ $purchase->supplier?->name ?? '-' }}

                            </div>


                            @if($purchase->supplier?->phone)

                                <div class="text-muted small mt-1">

                                    <i class="bi bi-telephone me-1"></i>

                                    {{ $purchase->supplier->phone }}

                                </div>

                            @endif

                        </div>

                    </div>

                </div>


                {{-- USER --}}

                <div class="col-md-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="text-muted small mb-1">
                                Dibuat Oleh
                            </div>

                            <div class="fw-semibold">

                                {{ $purchase->user?->name ?? '-' }}

                            </div>

                        </div>

                    </div>

                </div>

            </div>


                {{-- ======================================================
                DETAIL TABLE
                ====================================================== --}}

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white py-3">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h5 class="mb-0 fw-semibold">
                                Barang Pembelian
                            </h5>

                            <small class="text-muted">
                                {{ $purchase->details->count() }} jenis produk
                            </small>

                        </div>


                        <div class="text-muted">

                            Total Qty:

                            <strong>
                                {{ number_format($totalQty, 0, ',', '.') }}
                            </strong>

                        </div>

                    </div>

                </div>


                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th width="60">
                                        #
                                    </th>

                                    <th>
                                        Produk
                                    </th>

                                    <th>
                                        Barcode
                                    </th>

                                    <th class="text-center">
                                        Qty
                                    </th>

                                    <th class="text-end">
                                        Harga Beli
                                    </th>

                                    <th class="text-end">
                                        Subtotal
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                @forelse($purchase->details as $detail)

                                    <tr>

                                        <td>

                                            {{ $loop->iteration }}

                                        </td>


                                        <td>

                                            <div class="fw-semibold">

                                                {{ $detail->product?->name ?? '-' }}

                                            </div>

                                        </td>


                                        <td>

                                            <span class="text-muted">

                                                {{ $detail->product?->barcode ?? '-' }}

                                            </span>

                                        </td>


                                        <td class="text-center">

                                            {{ number_format($detail->qty, 0, ',', '.') }}

                                        </td>


                                        <td class="text-end">

                                            Rp
                                            {{ number_format($detail->price, 0, ',', '.') }}

                                        </td>


                                        <td class="text-end fw-semibold">

                                            Rp
                                            {{ number_format($detail->total, 0, ',', '.') }}

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td colspan="6"
                                            class="text-center py-5">

                                            <div class="text-muted">

                                                Belum ada detail barang.

                                            </div>

                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>


                {{-- ==================================================
                    TOTAL
                    ================================================== --}}

                <div class="card-footer bg-white">

                    <div class="row justify-content-end">

                        <div class="col-md-5">

                            <div class="d-flex justify-content-between mb-2">

                                <span class="text-muted">
                                    Total Qty
                                </span>

                                <strong>

                                    {{ number_format($totalQty, 0, ',', '.') }}

                                </strong>

                            </div>


                            <hr>


                            <div class="d-flex justify-content-between">

                                <span class="fw-semibold">
                                    Grand Total
                                </span>

                                <span class="fw-bold fs-4 text-primary">

                                    Rp
                                    {{ number_format($purchase->total, 0, ',', '.') }}

                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


                {{-- ======================================================
                NOTE
                ====================================================== --}}

            @if($purchase->note)

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white">

                        <h6 class="mb-0 fw-semibold">

                            <i class="bi bi-sticky me-1"></i>

                            Catatan

                        </h6>

                    </div>

                    <div class="card-body">

                        <div class="text-muted">

                            {{ $purchase->note }}

                        </div>

                    </div>

                </div>

            @endif

        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>

function confirmDeletePurchase()
{
    return confirm(
        'Yakin ingin menghapus pembelian {{ $purchase->invoice }}?\n\n' +
        'Stok dari semua produk dalam pembelian ini akan dikurangi kembali.'
    );
}

</script>

@endsection