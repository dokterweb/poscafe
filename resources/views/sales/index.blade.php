@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">Transaksi</div>
                    <h2 class="page-title">Penjualan</h2>
                </div>
                <div class="col-auto ms-auto">
                    <a href="{{ route('sales.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus"></i>
                        Transaksi Baru
                    </a>
                </div>
            </div>
        </div>
        
    </div>

    <div class="page-body">
        <div class="container-xl">
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
                                Total Nilai Penjualan
                            </div>
                            <div class="fs-4 fw-semibold text-primary">
                                Rp {{ number_format($totalSales, 0, ',', '.') }}
                            </div>
                            <div class="text-muted small">
                                berdasarkan filter
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row row-cards">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            Daftar Penjualan
                        </h3>
                    </div>
                    <div class="card-body border-bottom">
                        <form method="GET" action="{{ route('sales.index') }}">
                            <div class="row g-2 align-items-end">
                                {{-- SEARCH --}}
                                <div class="col-md-4">
                                    <label class="form-label">Search</label>
                                    <input type="text" name="search" class="form-control"
                                        value="{{ request('search') }}" placeholder="Invoice atau nama customer">
                                </div>
                                {{-- DATE FROM --}}
                                <div class="col-md-2">
                                    <label class="form-label">Dari</label>
                                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                                {{-- DATE TO --}}
                                <div class="col-md-2">
                                    <label class="form-label">Sampai</label>
                                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                                {{-- PAYMENT STATUS --}}
                                <div class="col-md-2">
                                    <label class="form-label">Status Bayar</label>
                                    <select name="payment_status" class="form-select">
                                        <option value="all">Semua</option>
                                        <option value="lunas" @selected(request('payment_status') === 'lunas')>
                                            Lunas
                                        </option>
                                        <option value="belum_lunas" @selected(request('payment_status') === 'belum_lunas')>
                                            Belum Lunas
                                        </option>
                                    </select>
                                </div>
                                {{-- BUTTON --}}
                                <div class="col-md-2">
                                    <div class="btn-list">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ti ti-search"></i>
                                            Cari
                                        </button>
                                        <a href="{{ route('sales.index') }}" class="btn">
                                            <i class="ti ti-refresh"></i>
                                            Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    @if ($sales->total() > 0)
                    <div class="px-3 pt-3 text-muted">
                        Menampilkan
                        <strong>{{ $sales->firstItem() }}</strong>
                        -
                        <strong>{{ $sales->lastItem() }}</strong>
                        dari
                        <strong>{{ $sales->total() }}</strong>
                        transaksi
                    </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">No.</th>
                                    <th>Invoice</th>
                                    <th>Name Customer</th>
                                    <th>Discount</th>
                                    <th>Belanja</th>
                                    <th>Date</th>
                                    <th>Status Bayar</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sales as $sale)
                                    <tr id="sale-row-{{ $sale->id }}">
                                        <td>{{ $sales->firstItem() + $loop->index }}</td>
                                        <td><strong>{{ $sale->invoice }}</strong></td>
                                        <td>{{ $sale->customer?->name ?? 'Cash / Umum' }}</td>
                                        <td>Rp{{ number_format($sale->discount,0,',','.') }}</td>
                                        <td><strong>Rp{{ number_format($sale->final_price,0,',','.') }}</strong></td>
                                        <td>{{ $sale->date?->format('d/m/Y') }}</td>
                                        <td>
                                            @if ($sale->payment_status === 'lunas')
                                                <span class="badge bg-success">Lunas</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Belum Lunas</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-list justify-content-center">
                                                <a href="{{ route('sales.show',$sale) }}"class="btn btn-sm btn-outline-secondary"title="Detail">
                                                    <i class="fa-regular fa-eye"></i>
                                                </a>
                                                <a href="{{ route('sales.edit',$sale) }}"class="btn btn-sm btn-warning"title="Edit">
                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                </a>
                                                <a href="{{ route('sales.print',$sale) }}"target="_blank"class="btn btn-sm btn-success"title="Cetak">
                                                    <i class="fa-solid fa-print"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger btn-delete-sale" data-id="{{ $sale->id }}"
                                                        data-invoice="{{ $sale->invoice }}" title="Hapus">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-5">
                                            <i class="ti ti-shopping-cart-off" style="font-size: 40px;"></i>
                                            <div class="mt-2">
                                                Belum ada transaksi penjualan.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($sales->hasPages())
                        <div class="card-footer">
                            {{ $sales->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')

<script>

function escapeHtml(text) {
    return $('<div>').text(text).html();
}

$(document).on('click','.btn-delete-sale',function () {

        let button =$(this);
        let saleId =button.data('id');
        let invoice =button.data('invoice');


        /*
        |--------------------------------------------------------------------------
        | KONFIRMASI
        |--------------------------------------------------------------------------
        */

        Swal.fire({
            icon:'warning',
            title:'Hapus Transaksi?',
            html:
                'Transaksi invoice ' +'<strong>' +
                escapeHtml(invoice) +'</strong>' +
                ' akan dihapus.' + '<br><br>' +
                '<span class="text-danger">' +
                '<strong>Stock produk akan dikembalikan.</strong>' +
                '</span>',

            showCancelButton: true,
            confirmButtonText:'<i class="ti ti-trash"></i> Ya, Hapus',
            cancelButtonText:'Batal',
            confirmButtonColor:'#d63939',
            cancelButtonColor:'#6c757d',
            reverseButtons:true,
            allowOutsideClick:false
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }
            /*
            |--------------------------------------------------------------------------
            | DISABLE BUTTON
            |--------------------------------------------------------------------------
            */
            button.prop('disabled',true);
            button.html(
                '<span class="spinner-border spinner-border-sm"></span>'
            );
            /*
            |--------------------------------------------------------------------------
            | AJAX DELETE
            |--------------------------------------------------------------------------
            */
            $.ajax({
                url:"{{ url('/sales') }}/" +saleId,
                type:'POST',
                data: {
                    _token:"{{ csrf_token() }}",
                    _method:'DELETE'
                },
                dataType:'json',
                /*
                |--------------------------------------------------------------------------
                | SUCCESS
                |--------------------------------------------------------------------------
                */
                success:
                    function (response) {
                        Swal.fire({
                            icon:'success',
                            title:'Berhasil',
                            text:response.message || 'Transaksi berhasil dihapus.',
                            confirmButtonText:'OK'
                        }).then(function () {
                            /*
                            |------------------------------------------
                            | HAPUS ROW DARI TABLE
                            |------------------------------------------
                            */
                            $('#sale-row-' + saleId).fadeOut(300,function () {
                                        $(this).remove();
                                    }
                                );
                        });
                    },
                /*
                |--------------------------------------------------------------------------
                | ERROR
                |--------------------------------------------------------------------------
                */
                error:
                    function (xhr) {
                        console.error('Delete sale error:',xhr);
                        let message ='Transaksi gagal dihapus.';
                        if (
                            xhr.responseJSON &&
                            xhr.responseJSON.message
                        ) {
                            message =xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon:'error',
                            title:'Gagal',
                            text:message
                        });
                        /*
                        |------------------------------------------
                        | KEMBALIKAN BUTTON
                        |------------------------------------------
                        */
                        button.prop('disabled',false);
                        button.html(
                            '<i class="ti ti-x"></i>'
                        );
                    }
            });
        });
    });
</script>

@endsection