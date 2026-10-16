@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">Master Pilihan Tambahan</h2>
                </div>
                <div class="col-auto ms-auto">
                    <button type="button" class="btn btn-primary"
                            id="btn-add-group">
                        <i class="ti ti-plus"></i> Tambah Kelompok
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th width="60">No.</th>
                                <th>Kelompok</th>
                                <th>Pilihan</th>
                                <th>Produk</th>
                                <th width="150">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="group-table">
                            @forelse ($optionGroups as $group)
                                <tr id="group-row-{{ $group->id }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $group->name }}</strong>
                                    </td>
                                    <td>
                                        @forelse ($group->options as $option)
                                            <span class="badge {{ $option->is_active ? 'bg-green-lt' : 'bg-secondary-lt' }} me-1 mb-1">
                                                {{ $option->name }}
                                                @if ((float) $option->price_adjustment > 0)
                                                    (+Rp {{ number_format($option->price_adjustment, 0, ',', '.') }})
                                                @endif
                                                @if (!$option->is_active)
                                                    (Nonaktif)
                                                @endif
                                            </span>
                                        @empty
                                            <span class="text-muted">Belum ada pilihan</span>
                                        @endforelse
                                    </td>
                                    <td>{{ $group->products_count }}</td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-sm btn-warning btn-edit-group"
                                                data-id="{{ $group->id }}">
                                            <i class="far fa-edit"></i>
                                        </button>

                                        <button type="button"
                                                class="btn btn-sm btn-danger btn-delete-group"
                                                data-id="{{ $group->id }}"
                                                data-name="{{ $group->name }}">
                                            <i class="far fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="empty-group">
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Belum ada kelompok pilihan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH / EDIT --}}
<div class="modal modal-blur fade" id="modalOptionGroup"
     tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="group-modal-title">
                    Tambah Kelompok Pilihan
                </h5>
                <button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>

            <form id="formOptionGroup">
                @csrf
                <input type="hidden" id="group_id">

                <div class="modal-body">
                    <div id="group-error"></div>

                    <div class="mb-3">
                        <label class="form-label">
                            Nama Kelompok <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="group_name"
                               class="form-control" maxlength="100"
                               placeholder="Contoh: Suhu" required>
                    </div>

                    <hr>

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h3 class="h3 mb-1">Daftar Pilihan</h3>
                            <div class="text-muted small">
                                Contoh: Hot, Iced, Extra Shot.
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm"
                                id="btn-add-option">
                            <i class="ti ti-plus"></i> Tambah Pilihan
                        </button>
                    </div>

                    <div id="options-container"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"
                            id="btn-save-group">
                        <i class="ti ti-device-floppy"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    let optionIndex = 0;
    let editingId = null;

    const modalElement = document.getElementById('modalOptionGroup');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    function formatNumber(value) {
        return new Intl.NumberFormat('id-ID')
            .format(Number(value || 0));
    }

    function optionRow(option = {}) {
        const index = optionIndex++;
        const idInput = option.id
            ? `<input type="hidden" name="options[${index}][id]" value="${option.id}">`
            : '';

        const active = option.is_active === false ||
                       option.is_active === 0 ||
                       option.is_active === '0'
            ? ''
            : 'checked';

        return `
            <div class="card card-sm mb-2 option-row">
                <div class="card-body">
                    ${idInput}

                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">Nama Pilihan</label>
                            <input type="text"
                                   name="options[${index}][name]"
                                   class="form-control"
                                   maxlength="100"
                                   placeholder="Hot / Iced"
                                   value="${escapeHtml(option.name ?? '')}"
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tambahan Harga (Rp)</label>
                            <input type="number"
                                   name="options[${index}][price_adjustment]"
                                   class="form-control"
                                   min="0" step="0.01"
                                   value="${option.price_adjustment ?? 0}"
                                   required>
                        </div>

                        <div class="col-md-2">
                            <input type="hidden"
                                   name="options[${index}][is_active]"
                                   value="0">
                            <label class="form-check mb-2">
                                <input type="checkbox"
                                       name="options[${index}][is_active]"
                                       value="1"
                                       class="form-check-input"
                                       ${active}>
                                <span class="form-check-label">Aktif</span>
                            </label>
                        </div>

                        <div class="col-md-1">
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm btn-remove-option"
                                    title="Hapus pilihan dari form">
                                <i class="far fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    function addOption(option = {}) {
        $('#options-container').append(optionRow(option));
    }

    function resetForm() {
        $('#formOptionGroup')[0].reset();
        $('#group_id').val('');
        $('#group-error').empty();
        $('#options-container').empty();
        optionIndex = 0;
        editingId = null;
    }

    function showErrors(errors) {
        let html = '<div class="alert alert-danger"><ul class="mb-0">';

        Object.values(errors).flat().forEach(function (message) {
            html += `<li>${escapeHtml(message)}</li>`;
        });

        html += '</ul></div>';
        $('#group-error').html(html);
    }

    $('#btn-add-group').on('click', function () {
        resetForm();
        $('#group-modal-title').text('Tambah Kelompok Pilihan');
        addOption();
        modal.show();
    });

    $('#btn-add-option').on('click', function () {
        addOption();
    });

    $(document).on('click', '.btn-remove-option', function () {
        $(this).closest('.option-row').remove();
    });

    modalElement.addEventListener('hidden.bs.modal', resetForm);

    // SIMPAN / UPDATE
    $('#formOptionGroup').on('submit', function (e) {
        e.preventDefault();

        const button = $('#btn-save-group');
        const isEdit = editingId !== null;
        const url = isEdit
            ? "{{ url('/option-groups') }}/" + editingId
            : "{{ route('option-groups.store') }}";

        $('#group-error').empty();

        button.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize() + (isEdit ? '&_method=PUT' : ''),
            dataType: 'json',

            success: function (response) {
                if (response.status === 'success') {
                    modal.hide();

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1300,
                        showConfirmButton: false
                    }).then(function () {
                        window.location.reload();
                    });
                }
            },

            error: function (xhr) {
                console.error(xhr.responseText);

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    showErrors(xhr.responseJSON.errors);
                    return;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: xhr.responseJSON?.message ||
                          'Data kelompok pilihan gagal disimpan.'
                });
            },

            complete: function () {
                button.prop('disabled', false)
                    .html('<i class="ti ti-device-floppy"></i> Simpan');
            }
        });
    });

    // EDIT
    $(document).on('click', '.btn-edit-group', function () {
        const id = $(this).data('id');

        // Ambil data dari endpoint edit khusus yang disediakan di bawah.
        $.ajax({
            url: "{{ url('/option-groups') }}/" + id + "/data",
            type: 'GET',
            dataType: 'json',

            success: function (response) {
                const group = response.data;

                resetForm();
                editingId = group.id;

                $('#group_id').val(group.id);
                $('#group_name').val(group.name);
                $('#group-modal-title').text('Edit Kelompok Pilihan');

                (group.options || []).forEach(function (option) {
                    addOption(option);
                });

                if (!group.options || group.options.length === 0) {
                    addOption();
                }

                modal.show();
            },

            error: function (xhr) {
                console.error(xhr.responseText);
                Swal.fire('Gagal', 'Data kelompok tidak dapat diambil.', 'error');
            }
        });
    });

    // DELETE
    $(document).on('click', '.btn-delete-group', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');

        Swal.fire({
            title: 'Hapus kelompok?',
            text: `Kelompok "${name}" akan dihapus beserta pilihannya.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: "{{ url('/option-groups') }}/" + id,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    _method: 'DELETE'
                },
                dataType: 'json',

                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1300,
                        showConfirmButton: false
                    }).then(function () {
                        window.location.reload();
                    });
                },

                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: xhr.responseJSON?.message ||
                              'Kelompok pilihan gagal dihapus.'
                    });
                }
            });
        });
    });
});
</script>
@endsection
