@extends('layouts.app')

@section('content')

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Customer</h2>
            </div>
            <div class="col-auto ms-auto">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateCustomer">
                    <i class="ti ti-plus"></i>
                    Tambah Customer
                </button>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('customers.index') }}">
                    <div class="row g-2">
                        <div class="col-md-8">
                            <label class="form-label">Cari Customer</label>
                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <i class="ti ti-search"></i>
                                </span>
                                <input type="text" name="search" class="form-control" placeholder="Cari nama, phone, atau alamat..."
                                    value="{{ $search }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-search me-1"></i>
                                    Cari
                                </button>
                                @if ($search)
                                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary" >
                                        <i class="ti ti-refresh me-1"></i>
                                        Reset
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Nama Customer</th>
                            <th>Phone</th>
                            <th>Alamat</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="customer-table">
                        @forelse ($customers as $customer)
                            <tr id="customer-row-{{ $customer->id }}">
                                <td> {{ $customers->firstItem() + $loop->index }}</td>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->phone ?? '-' }}</td>
                                <td>{{ $customer->address ?? '-' }}</td>
                                <td>
                                    <div class="btn-list justify-content-center">
                                        <button type="button" class="btn btn-sm btn-warning btn-edit-customer" data-id="{{ $customer->id }}">
                                            <i class="far fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger btn-delete-customer" data-id="{{ $customer->id }}">
                                            <i class="far fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="empty-customer">
                                <td colspan="5" class="text-center text-muted py-4">
                                    Belum ada customer.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($customers->hasPages())

            <div class="card-footer">
                <div class="d-flex align-items-center justify-content-between">
                    {{-- INFO DATA --}}
                    <div class="text-muted">
                        Menampilkan
                        <strong>{{ $customers->firstItem() }}</strong>
                        sampai
                        <strong>{{ $customers->lastItem() }}</strong>
                        dari
                        <strong>{{ $customers->total() }}</strong>
                        customer
                    </div>
                    {{-- PAGINATION --}}
                    <ul class="pagination mb-0">
                        {{-- PREVIOUS --}}
                        @if ($customers->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="icon"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        stroke-width="2"
                                        stroke="currentColor"
                                        fill="none"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M15 6l-6 6l6 6"/>
                                    </svg>
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $customers->previousPageUrl() }}" rel="prev">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="icon"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        stroke-width="2"
                                        stroke="currentColor"
                                        fill="none"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M15 6l-6 6l6 6"/>
                                    </svg>
                                </a>
                            </li>
                        @endif
                        {{-- PAGE NUMBERS --}}
                        @foreach (
                            $customers->getUrlRange(
                                max(1, $customers->currentPage() - 2),
                                min(
                                    $customers->lastPage(),
                                    $customers->currentPage() + 2
                                )
                            ) as $page => $url
                        )
                            <li class="page-item {{ $page == $customers->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $url }}" >
                                    {{ $page }}
                                </a>
                            </li>
                        @endforeach
                        {{-- NEXT --}}
                        @if ($customers->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $customers->nextPageUrl() }}" rel="next">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="icon"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        stroke-width="2"
                                        stroke="currentColor"
                                        fill="none"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M9 6l6 6l-6 6" />
                                    </svg>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="icon"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        stroke-width="2"
                                        stroke="currentColor"
                                        fill="none"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M9 6l6 6l-6 6"/>
                                    </svg>
                                </span>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        @endif
        </div>
    </div>
</div>

{{-- MODAL CREATE --}}
<div class="modal modal-blur fade" id="modalCreateCustomer" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCreateCustomer">
                @csrf
                <div class="modal-body">
                    <div id="create-customer-error"></div>
                    <div class="mb-3">
                        <label class="form-label">Nama Customer
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control"placeholder="Nama customer">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control"placeholder="Nomor telepon">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="3"placeholder="Alamat customer"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSaveCustomer">
                        <i class="ti ti-device-floppy"></i>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================================================== --}}
{{-- MODAL EDIT CUSTOMER --}}
{{-- ===================================================== --}}

<div class="modal modal-blur fade" id="modalEditCustomer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditCustomer">
                @csrf
                <input type="hidden" name="customer_id" id="edit_customer_id">
                <div class="modal-body">
                    <div id="edit-customer-error"></div>
                    {{-- Nama --}}
                    <div class="mb-3">
                        <label class="form-label">Nama Customer
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="edit_customer_name" class="form-control" placeholder="Nama customer">
                    </div>
                    {{-- Phone --}}
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" id="edit_customer_phone" class="form-control"placeholder="Nomor telepon">
                    </div>
                    {{-- Address --}}
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" id="edit_customer_address" class="form-control" rows="3" placeholder="Alamat customer"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnUpdateCustomer">
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

<script>

$(document).ready(function () {
    $('#formCreateCustomer').on('submit', function (e) {
        e.preventDefault();
        let form = $(this);
        let button = $('#btnSaveCustomer');
        $('#create-customer-error').html('');
        button.prop('disabled', true);
        $.ajax({
            url: "{{ route('customers.store') }}",
            type: "POST",
            data: form.serialize(),
            dataType: "json",
            success: function (response) {
                let customer = response.data;
                $('#empty-customer').remove();
                let row = `
                    <tr id="customer-row-${customer.id}">
                        <td>New</td>
                        <td>${escapeHtml(customer.name)}</td>
                        <td>${escapeHtml(customer.phone || '-')}</td>
                        <td>${escapeHtml(customer.address || '-')}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-warning btn-edit-customer" data-id="${customer.id}">
                                <i class="ti ti-edit"></i>Edit
                            </button>
                            <button type="button" class="btn btn-sm btn-danger btn-delete-customer" data-id="${customer.id}">
                                <i class="ti ti-trash"></i>Del
                            </button>
                        </td>
                    </tr>
                `;
                $('#customer-table').prepend(row);
                bootstrap.Modal
                    .getOrCreateInstance(
                        document.getElementById(
                            'modalCreateCustomer'
                        )
                    )
                    .hide();
                form[0].reset();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                });
            },

            error: function (xhr) {
                if (xhr.status === 422) {
                    showCustomerValidationError(
                        '#create-customer-error',
                        xhr.responseJSON.errors
                    );
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Customer gagal disimpan.'
                    });
                }
            },
            complete: function () {
                button.prop('disabled', false);
            }
        });
    });

    function escapeHtml(text)
    {
        return $('<div>')
            .text(text ?? '')
            .html();
    }


    function showCustomerValidationError(container,errors) 
    {
        let html = `
            <div class="alert alert-danger">
                <ul class="mb-0">
        `;
        $.each(errors, function (field, messages) {
            $.each(messages, function (index, message) {
                html += `
                    <li>
                        ${escapeHtml(message)}
                    </li>
                `;
            });
        });
        html += `
                </ul>
            </div>
        `;
        $(container).html(html);
    }

    // EDIT CUSTOMER - OPEN MODAL
    $(document).on('click', '.btn-edit-customer', function () {

        let customerId = $(this).data('id');

        $('#edit-customer-error').html('');

        $('#formEditCustomer')[0].reset();

        $('#edit_customer_id').val(customerId);

        $.ajax({

            url: "{{ url('/customers') }}/" + customerId + "/edit",

            type: "GET",

            dataType: "json",

            beforeSend: function () {

                $('#btnUpdateCustomer')
                    .prop('disabled', true);

            },

            success: function (response) {

                if (response.status === 'success') {

                    let customer = response.data;

                    $('#edit_customer_id')
                        .val(customer.id);

                    $('#edit_customer_name')
                        .val(customer.name);

                    $('#edit_customer_phone')
                        .val(customer.phone || '');

                    $('#edit_customer_address')
                        .val(customer.address || '');

                    // -----------------------------------------
                    // OPEN MODAL
                    // -----------------------------------------

                    let modalElement =
                        document.getElementById(
                            'modalEditCustomer'
                        );

                    let modal =
                        bootstrap.Modal
                            .getOrCreateInstance(modalElement);

                    modal.show();

                }

            },

            error: function () {

                Swal.fire({

                    icon: 'error',

                    title: 'Gagal',

                    text: 'Data customer tidak dapat diambil.'

                });

            },

            complete: function () {

                $('#btnUpdateCustomer')
                    .prop('disabled', false);

            }

        });

    });

    // UPDATE CUSTOMER
    $('#formEditCustomer').on('submit', function (e) {

        e.preventDefault();

        let form = $(this);

        let customerId =
            $('#edit_customer_id').val();

        let button =
            $('#btnUpdateCustomer');

        $('#edit-customer-error').html('');

        button.prop('disabled', true);

        $.ajax({

            url: "{{ url('/customers') }}/" + customerId,

            type: "POST",

            data: form.serialize() + '&_method=PUT',

            dataType: "json",

            success: function (response) {

                if (response.status === 'success') {

                    let customer = response.data;

                    // -----------------------------------------
                    // UPDATE ROW
                    // -----------------------------------------

                    let row =
                        $('#customer-row-' + customer.id);

                    row.find('td:eq(1)')
                        .text(customer.name);

                    row.find('td:eq(2)')
                        .text(customer.phone || '-');

                    row.find('td:eq(3)')
                        .text(customer.address || '-');

                    // -----------------------------------------
                    // CLOSE MODAL
                    // -----------------------------------------

                    let modalElement =
                        document.getElementById(
                            'modalEditCustomer'
                        );

                    let modal =
                        bootstrap.Modal
                            .getOrCreateInstance(modalElement);

                    modal.hide();

                    // -----------------------------------------
                    // RESET
                    // -----------------------------------------

                    form[0].reset();

                    // -----------------------------------------
                    // SUCCESS
                    // -----------------------------------------

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

                    showCustomerValidationError(
                        '#edit-customer-error',
                        xhr.responseJSON.errors
                    );

                } else {

                    Swal.fire({

                        icon: 'error',

                        title: 'Gagal',

                        text: 'Customer gagal diperbarui.'

                    });

                }

            },

            complete: function () {

                button.prop('disabled', false);

            }

        });

    });

    // DELETE CUSTOMER
    $(document).on('click', '.btn-delete-customer', function () {

        let button = $(this);

        let customerId = button.data('id');

        Swal.fire({

            title: 'Hapus Customer?',

            text: 'Data customer yang dihapus tidak dapat dikembalikan.',

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

                url: "{{ url('/customers') }}/" + customerId,

                type: "POST",

                data: {

                    _token: "{{ csrf_token() }}",

                    _method: "DELETE"

                },

                dataType: "json",

                success: function (response) {

                    if (response.status === 'success') {

                        // ---------------------------------
                        // REMOVE ROW
                        // ---------------------------------

                        $('#customer-row-' + customerId)
                            .fadeOut(300, function () {

                                $(this).remove();

                                // ---------------------------------
                                // TABLE KOSONG
                                // ---------------------------------

                                if (
                                    $('#customer-table tr').length === 0
                                ) {

                                    $('#customer-table').html(`
                                        <tr id="empty-customer">

                                            <td colspan="5"
                                                class="text-center text-muted py-4">

                                                Belum ada customer.

                                            </td>

                                        </tr>
                                    `);

                                }

                            });


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

                    button.prop('disabled', false);

                    let message =
                        'Customer gagal dihapus.';

                    // ---------------------------------
                    // CUSTOMER SUDAH DIGUNAKAN
                    // ---------------------------------

                    if (xhr.status === 409) {

                        message =
                            'Customer tidak dapat dihapus karena sudah digunakan dalam transaksi.';

                    } else if (
                        xhr.responseJSON &&
                        xhr.responseJSON.message
                    ) {

                        message =
                            xhr.responseJSON.message;

                    }

                    Swal.fire({

                        icon: 'error',

                        title: 'Tidak dapat dihapus',

                        text: message

                    });

                }

            });

        });

    });    
});

</script>



@endsection