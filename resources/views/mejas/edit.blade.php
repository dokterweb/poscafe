@extends('layouts.app')

@section('title', 'Edit Meja')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Edit Meja {{ $meja->nomor }}</h2>
                <div class="text-secondary mt-1">
                    Perbarui informasi meja.
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
                              action="{{ route('mejas.update', $meja->id) }}"
                              method="POST">
                            @include('mejas._form', ['meja' => $meja])
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
