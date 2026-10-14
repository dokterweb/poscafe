@extends('layouts.app')

@section('content')
<div class="page-wrapper">
  <div class="page-header d-print-none">
      <div class="container-xl">
          <div class="row g-2 align-items-center">
              <div class="col">
                  <h2 class="page-title">
                      Supplier
                  </h2>
              </div>

              <div class="col-auto ms-auto">
                  <button type="button"
                          class="btn btn-primary"
                          data-bs-toggle="modal"
                          data-bs-target="#modalCreateSupplier">
                      <i class="ti ti-plus"></i>
                      Tambah Supplier
                  </button>
              </div>
          </div>
      </div>
  </div>

    <!-- Page body -->
    <div class="page-body">
      <div class="container-xl">
        <div class="row row-cards">
          <div class="col-lg-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Data Supplier</h3>
              </div>
              <div class="table-responsive">
                <table class="table table-vcenter card-table">
                  <thead>
                    <tr>
                      <th width="50">No</th>
                      <th>Nama</th>
                      <th>Telepon</th>
                      <th>Alamat</th>
                      <th>Keterangan</th>
                      <th width="100">Aksi</th>
                    </tr>
                  </thead>
                  <tbody id="supplier-table">
                    @forelse ($suppliers as $supplier)
                      <tr id="supplier-row-{{ $supplier->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $supplier->name }}</td>
                        <td>{{ $supplier->phone ?? '-' }}</td>
                        <td>{{ $supplier->address ?? '-' }}</td>
                        <td>{{ $supplier->description ?? '-' }}</td>
                        <td>
                            <div class="btn-list flex-nowrap">
                             <button type="button" class="btn btn-sm btn-warning btn-edit-supplier"
                                    data-id="{{ $supplier->id }}"title="Edit">
                                <i class="ti ti-edit"></i>Edit
                            </button>
                            <button type="button" class="btn btn-sm btn-danger btn-delete-supplier"
                                    data-id="{{ $supplier->id }}" title="Hapus">
                                <i class="ti ti-trash"></i>Del
                            </button>
                             </div>
                        </td>
                      </tr>
                    @empty
                      <tr id="empty-supplier">
                        <td colspan="6" class="text-center text-muted py-4">
                            Belum ada supplier.
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
    </div>
</div>

{{-- MODAL CREATE SUPPLIER --}}
<div class="modal modal-blur fade" id="modalCreateSupplier" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formCreateSupplier">
          @csrf
        <div class="modal-body">
          <div id="create-error"></div>
          <div class="mb-3">
              <label class="form-label">Nama Supplier
                  <span class="text-danger">*</span>
              </label>
              <input type="text" name="name" class="form-control" placeholder="Nama supplier">
          </div>
          <div class="mb-3">
              <label class="form-label">Telepon</label>
              <input type="text" name="phone" class="form-control" placeholder="Nomor telepon">
          </div>
          <div class="mb-3">
              <label class="form-label">Alamat</label>
              <textarea name="address" class="form-control" rows="2" placeholder="Alamat supplier"></textarea>
          </div>
          <div class="mb-3">
              <label class="form-label">Keterangan </label>
              <textarea name="description" class="form-control" rows="2" placeholder="Keterangan"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              Batal
          </button>
          <button type="submit" class="btn btn-primary" id="btnSaveSupplier">
              <i class="ti ti-device-floppy"></i>
              Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- MODAL EDIT SUPPLIER --}}
<div class="modal modal-blur fade" id="modalEditSupplier" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditSupplier">
                @csrf
                <input type="hidden" name="supplier_id" id="edit_supplier_id">
                <div class="modal-body">
                    <div id="edit-error"></div>
                    <div class="mb-3">
                        <label class="form-label">Nama Supplier
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="edit_name" class="form-control" placeholder="Nama supplier">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control" placeholder="Nomor telepon">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" id="edit_address" class="form-control" rows="2" placeholder="Alamat supplier"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="2"placeholder="Keterangan"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnUpdateSupplier">
                        <i class="ti ti-device-floppy"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

$(document).ready(function () {

    // =====================================================
    // CREATE SUPPLIER
    // =====================================================

    $('#formCreateSupplier').on('submit', function (e) {

        e.preventDefault();

        let form = $(this);

        let button = $('#btnSaveSupplier');

        button.prop('disabled', true);

        $('#create-error').html('');

        $.ajax({

            url: "{{ route('suppliers.store') }}",

            type: "POST",

            data: form.serialize(),

            dataType: "json",

            success: function (response) {

                if (response.status === 'success') {

                    let modalElement =
                        document.getElementById('modalCreateSupplier');

                    let modal =
                        bootstrap.Modal.getOrCreateInstance(modalElement);

                    modal.hide();

                    form[0].reset();

                    $('#empty-supplier').remove();

                    let supplier = response.data;

                    let row = `
                        <tr id="supplier-row-${supplier.id}">

                            <td>New</td>

                            <td>
                                ${escapeHtml(supplier.name)}
                            </td>

                            <td>
                                ${escapeHtml(supplier.phone || '-')}
                            </td>

                            <td>
                                ${escapeHtml(supplier.address || '-')}
                            </td>

                            <td>
                                ${escapeHtml(supplier.description || '-')}
                            </td>

                            <td>

                                <button type="button"
                                        class="btn btn-sm btn-warning btn-edit-supplier"
                                        data-id="${supplier.id}">
                                    <i class="ti ti-edit"></i>
                                </button>

                                <button type="button"
                                        class="btn btn-sm btn-danger">
                                    <i class="ti ti-trash"></i>
                                </button>

                            </td>

                        </tr>
                    `;

                    $('#supplier-table').prepend(row);

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                }

            },

            error: function (xhr) {

                if (xhr.status === 422) {

                    showValidationError(
                        '#create-error',
                        xhr.responseJSON.errors
                    );

                } else {

                    showError(
                        'Terjadi kesalahan saat menyimpan supplier.'
                    );

                }

            },

            complete: function () {

                button.prop('disabled', false);

            }

        });

    });


    // =====================================================
    // EDIT SUPPLIER - OPEN MODAL
    // =====================================================

    $(document).on('click', '.btn-edit-supplier', function () {

        let supplierId = $(this).data('id');

        $('#edit-error').html('');

        $('#formEditSupplier')[0].reset();

        $('#edit_supplier_id').val(supplierId);

        $.ajax({

            url: "{{ url('/suppliers') }}/" +
                 supplierId +
                 "/edit",

            type: "GET",

            dataType: "json",

            beforeSend: function () {

                $('#btnUpdateSupplier')
                    .prop('disabled', true);

            },

            success: function (response) {

                if (response.status === 'success') {

                    let supplier = response.data;

                    $('#edit_supplier_id')
                        .val(supplier.id);

                    $('#edit_name')
                        .val(supplier.name);

                    $('#edit_phone')
                        .val(supplier.phone || '');

                    $('#edit_address')
                        .val(supplier.address || '');

                    $('#edit_description')
                        .val(supplier.description || '');

                    let modalElement =
                        document.getElementById('modalEditSupplier');

                    let modal =
                        bootstrap.Modal.getOrCreateInstance(modalElement);

                    modal.show();
                }

            },

            error: function () {

                showError(
                    'Data supplier tidak dapat diambil.'
                );

            },

            complete: function () {

                $('#btnUpdateSupplier')
                    .prop('disabled', false);

            }

        });

    });


    // =====================================================
    // UPDATE SUPPLIER
    // =====================================================

    $('#formEditSupplier').on('submit', function (e) {

        e.preventDefault();

        let form = $(this);

        let supplierId =
            $('#edit_supplier_id').val();

        let button =
            $('#btnUpdateSupplier');

        $('#edit-error').html('');

        button.prop('disabled', true);

        $.ajax({

            url: "{{ url('/suppliers') }}/" + supplierId,

            type: "POST",

            data: form.serialize() + '&_method=PUT',

            dataType: "json",

            success: function (response) {

                if (response.status === 'success') {

                    let supplier = response.data;

                    let row =
                        $('#supplier-row-' + supplier.id);

                    row.find('td:eq(1)')
                        .text(supplier.name);

                    row.find('td:eq(2)')
                        .text(supplier.phone || '-');

                    row.find('td:eq(3)')
                        .text(supplier.address || '-');

                    row.find('td:eq(4)')
                        .text(supplier.description || '-');

                    let modalElement =
                        document.getElementById('modalEditSupplier');

                    let modal =
                        bootstrap.Modal.getOrCreateInstance(modalElement);

                    modal.hide();

                    form[0].reset();

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                }

            },

            error: function (xhr) {

                if (xhr.status === 422) {

                    showValidationError(
                        '#edit-error',
                        xhr.responseJSON.errors
                    );

                } else {

                    showError(
                        'Terjadi kesalahan saat memperbarui supplier.'
                    );

                }

            },

            complete: function () {

                button.prop('disabled', false);

            }

        });

    });


    // =====================================================
    // HELPER VALIDATION
    // =====================================================

    function showValidationError(container, errors) {

        let html = `
            <div class="alert alert-danger">
                <ul class="mb-0">
        `;

        $.each(errors, function (field, messages) {

            $.each(messages, function (index, message) {

                html += `<li>${escapeHtml(message)}</li>`;

            });

        });

        html += `
                </ul>
            </div>
        `;

        $(container).html(html);
    }


    // =====================================================
    // HELPER ERROR
    // =====================================================

    function showError(message) {

        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: message
        });

    }


    // =====================================================
    // ESCAPE HTML
    // =====================================================

    function escapeHtml(text) {

        return $('<div>')
            .text(text ?? '')
            .html();

    }

});

// =====================================================
// DELETE SUPPLIER
// =====================================================

$(document).on('click', '.btn-delete-supplier', function () {

    let button = $(this);

    let supplierId = button.data('id');

    Swal.fire({

        title: 'Hapus Supplier?',
        
        text: 'Data supplier yang dihapus tidak dapat dikembalikan.',

        icon: 'warning',

        showCancelButton: true,

        confirmButtonText: 'Ya, Hapus',

        cancelButtonText: 'Batal',

        reverseButtons: true,

        focusCancel: true

    }).then((result) => {

        if (!result.isConfirmed) {
            return;
        }

        // ---------------------------------------------
        // DISABLE BUTTON
        // ---------------------------------------------

        button.prop('disabled', true);

        // ---------------------------------------------
        // AJAX DELETE
        // ---------------------------------------------

        $.ajax({

            url: "{{ url('/suppliers') }}/" + supplierId,

            type: "POST",

            data: {
                _token: "{{ csrf_token() }}",
                _method: "DELETE"
            },

            dataType: "json",

            success: function (response) {

                if (response.status === 'success') {

                    // ---------------------------------
                    // HAPUS ROW DARI TABLE
                    // ---------------------------------

                    $('#supplier-row-' + supplierId).fadeOut(
                        300,
                        function () {

                            $(this).remove();

                            // ---------------------------------
                            // JIKA SUDAH TIDAK ADA DATA
                            // ---------------------------------

                            if ($('#supplier-table tr').length === 0) {

                                $('#supplier-table').html(`
                                    <tr id="empty-supplier">

                                        <td colspan="6"
                                            class="text-center text-muted py-4">

                                            Belum ada supplier.

                                        </td>

                                    </tr>
                                `);

                            }

                        }
                    );

                    // ---------------------------------
                    // SUCCESS ALERT
                    // ---------------------------------

                    Swal.fire({

                        icon: 'success',

                        title: 'Berhasil',

                        text: response.message,

                        timer: 1500,

                        showConfirmButton: false

                    });

                }

            },

            error: function (xhr) {

                // ---------------------------------
                // ENABLE BUTTON
                // ---------------------------------

                button.prop('disabled', false);

                // ---------------------------------
                // ERROR
                // ---------------------------------

                let message =
                    'Supplier gagal dihapus.';

                if (
                    xhr.responseJSON &&
                    xhr.responseJSON.message
                ) {

                    message =
                        xhr.responseJSON.message;

                }

                Swal.fire({

                    icon: 'error',

                    title: 'Gagal',

                    text: message

                });

            }

        });

    });

});
</script>
@endsection