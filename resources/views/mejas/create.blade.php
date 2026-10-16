@extends('layouts.app')

@section('title', 'Tambah Meja')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Tambah Meja</h2>
                <div class="text-secondary mt-1">
                    Tambahkan meja baru untuk transaksi dine-in.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Meja</h3>
                    </div>

                    <div class="card-body">
                        <form id="form-meja"
                              action="{{ route('mejas.store') }}"
                              method="POST">
                            @include('mejas._form')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
