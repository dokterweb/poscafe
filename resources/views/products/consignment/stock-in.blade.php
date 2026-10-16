
@extends('layouts.app')

@section('title', 'Stok Masuk Produk Titipan')

@section('content')
<div class="container-xl">

    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Stok Masuk Produk Titipan</h2>
                <div class="text-muted mt-1">
                    Catat penerimaan barang dari mitra.
                </div>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('products.consignment.index') }}"
                   class="btn btn-outline-secondary">
                    Kembali
                </a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-7 col-lg-6">

            <form method="POST"
                  action="{{ route('products.consignment.stock-in.store', $product) }}">
                @csrf

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Penerimaan</h3>
                    </div>

                    <div class="card-body">

                        <div class="mb-3">
                            <label class="form-label">Nama Produk</label>
                            <input type="text"
                                   class="form-control"
                                   value="{{ $product->name }}"
                                   readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mitra</label>
                            <input type="text"
                                   class="form-control"
                                   value="{{ $product->consignmentPartner->name ?? '-' }}"
                                   readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Stok Saat Ini</label>
                            <input type="number"
                                   class="form-control"
                                   value="{{ $product->stock }}"
                                   readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">
                                Jumlah Stok Masuk
                            </label>
                            <input type="number"
                                   name="qty"
                                   class="form-control @error('qty') is-invalid @enderror"
                                   value="{{ old('qty') }}"
                                   min="1"
                                   step="1"
                                   required>
                            @error('qty')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea name="note"
                                      class="form-control @error('note') is-invalid @enderror"
                                      rows="3"
                                      maxlength="500"
                                      placeholder="Contoh: Pengiriman tambahan dari mitra">{{ old('note') }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info mb-0">
                            Stok akan bertambah setelah data berhasil disimpan.
                        </div>

                    </div>

                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('products.consignment.index') }}"
                           class="btn btn-outline-secondary">
                            Batal
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-package-import me-1"></i>
                            Simpan Stok Masuk
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
