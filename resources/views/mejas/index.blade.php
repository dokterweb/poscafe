@extends('layouts.app')

@section('title', 'Master Meja')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Master Meja</h2>
                <div class="text-secondary mt-1">
                    Kelola meja yang digunakan untuk transaksi dine-in.
                </div>
            </div>

            <div class="col-auto ms-auto">
                <a href="{{ route('mejas.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Meja
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                <div>{{ session('success') }}</div>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        @endif

        <div class="card">
            <div class="card-body border-bottom">
                <form action="{{ route('mejas.index') }}" method="GET">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                value="{{ $search }}"
                                placeholder="Cari nomor atau nama meja..."
                            >
                        </div>

                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search me-1"></i>
                                Cari
                            </button>

                            <a href="{{ route('mejas.index') }}"
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
                            <th width="60">No.</th>
                            <th>Nomor Meja</th>
                            <th>Nama Meja</th>
                            <th class="text-center">Kapasitas</th>
                            <th class="text-center">Status</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($mejas as $index => $meja)
                            <tr>
                                <td>
                                    {{ $mejas->firstItem() + $index }}
                                </td>

                                <td class="fw-bold">
                                    {{ $meja->nomor }}
                                </td>

                                <td>
                                    {{ $meja->nama ?: '-' }}
                                </td>

                                <td class="text-center">
                                    {{ $meja->kapasitas }} kursi
                                </td>

                                <td class="text-center">
                                    @if($meja->is_active)
                                        <span class="badge bg-success-lt">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary-lt">Nonaktif</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a
                                            href="{{ route('mejas.edit', $meja->id) }}"
                                            class="btn btn-sm btn-warning"
                                            title="Edit meja"
                                        >
                                            <i class="ti ti-edit"></i>
                                        </a>

                                        <form
                                            action="{{ route('mejas.destroy', $meja->id) }}"
                                            method="POST"
                                            class="form-delete-meja"
                                            data-nomor="{{ $meja->nomor }}"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-danger"
                                                title="Hapus meja"
                                            >
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-secondary">
                                        <i class="ti ti-armchair fs-1"></i>
                                        <div class="mt-2">Belum ada data meja.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($mejas->hasPages())
                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-secondary">
                        Menampilkan {{ $mejas->firstItem() }}–{{ $mejas->lastItem() }}
                        dari {{ $mejas->total() }} meja
                    </p>

                    <div class="ms-auto">
                        {{ $mejas->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>


@endsection

@section('scripts')
<script>
$(function () {
    $(document).on('submit', '.form-delete-meja', function (e) {
        e.preventDefault();

        const form = this;
        const nomor = $(form).data('nomor');

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus meja?',
                text: 'Meja ' + nomor + ' akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d63939'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        } else {
            if (confirm('Yakin ingin menghapus meja ' + nomor + '?')) {
                form.submit();
            }
        }
    });
});
</script>
@endsection
