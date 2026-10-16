
@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">{{ $pageTitle }}</h2>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row">
            <div class="col-lg-8">
                <form method="POST"
                      action="{{ $partner->exists
                          ? route('pos.consignment-partners.update', $partner)
                          : route('pos.consignment-partners.store') }}">
                    @csrf
                    @if($partner->exists)
                        @method('PUT')
                    @endif

                    <div class="card">
                        <div class="card-body">
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label">
                                    Nama Mitra <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="name"
                                       class="form-control"
                                       maxlength="150"
                                       value="{{ old('name', $partner->name) }}"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Kontak</label>
                                <input type="text"
                                       name="contact_person"
                                       class="form-control"
                                       value="{{ old('contact_person', $partner->contact_person) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nomor Telepon</label>
                                <input type="text"
                                       name="phone"
                                       class="form-control"
                                       value="{{ old('phone', $partner->phone) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea name="address"
                                          class="form-control"
                                          rows="3">{{ old('address', $partner->address) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea name="notes"
                                          class="form-control"
                                          rows="3">{{ old('notes', $partner->notes) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="is_active" class="form-select" required>
                                    <option value="1"
                                        @selected((string) old('is_active', $partner->exists ? (int) $partner->is_active : 1) === '1')>
                                        Aktif
                                    </option>
                                    <option value="0"
                                        @selected((string) old('is_active', $partner->exists ? (int) $partner->is_active : 1) === '0')>
                                        Nonaktif
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="card-footer d-flex justify-content-between">
                            <a href="{{ route('pos.consignment-partners.index') }}"
                               class="btn btn-outline-secondary">
                                Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
