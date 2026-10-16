@extends('layouts.app')

@section('title', 'Mitra Titipan')

@section('content')
<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">Mitra Titipan</h2>
            <div class="text-muted mt-1">
                Kelola toko atau pemilik barang titipan.
            </div>
        </div>
        <div class="col-auto">
            <a href="{{ route('pos.consignment-partners.create') }}"
               class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>
                Tambah Mitra
            </a>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <a class="btn-close" data-bs-dismiss="alert"
                   aria-label="close"></a>
            </div>
        @endif

        <div class="card">
            <div class="card-body border-bottom">
                <form method="GET"
                      action="{{ route('pos.consignment-partners.index') }}">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input type="text"
                                   name="keyword"
                                   class="form-control"
                                   placeholder="Cari nama, kontak, atau telepon..."
                                   value="{{ request('keyword') }}">
                        </div>

                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">Semua status</option>
                                <option value="aktif"
                                    @selected(request('status') === 'aktif')>
                                    Aktif
                                </option>
                                <option value="nonaktif"
                                    @selected(request('status') === 'nonaktif')>
                                    Nonaktif
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex gap-2">
                            <button class="btn btn-primary" type="submit">
                                Cari
                            </button>
                            <a href="{{ route('pos.consignment-partners.index') }}"
                               class="btn btn-outline-secondary">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Nama Mitra</th>
                            <th>Kontak</th>
                            <th>Telepon</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($partners as $partner)
                            <tr>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $partner->name }}
                                    </div>
                                    @if($partner->address)
                                        <div class="text-muted small">
                                            {{ $partner->address }}
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $partner->contact_person ?: '-' }}</td>
                                <td>{{ $partner->phone ?: '-' }}</td>
                                <td>
                                    @if($partner->is_active)
                                        <span class="badge bg-success text-white">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-secondary text-white">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('pos.consignment-partners.edit', $partner) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"
                                    class="text-center text-muted py-4">
                                    Belum ada data mitra titipan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                {{ $partners->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
