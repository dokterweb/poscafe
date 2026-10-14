@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="page-header d-print-none mb-3">

        <div class="row align-items-center">

            <div class="col">

                <h2 class="page-title">
                    Backup & Restore
                </h2>

                <div class="text-muted mt-1">
                    Kelola backup database dan file RudyPOS.
                </div>

            </div>


            <div class="col-auto">

                <button
                    type="button"
                    id="btnCreateBackup"
                    class="btn btn-primary"
                >

                    <i class="ti ti-database-export me-1"></i>

                    Backup Sekarang

                </button>

            </div>

        </div>

    </div>


    {{-- =========================================================
         ALERT
    ========================================================== --}}

    <div
        id="backupAlert"
        class="alert d-none"
    ></div>


    <div class="row">


        {{-- =====================================================
             SUMMARY
        ====================================================== --}}

        <div class="col-md-4">

            <div class="card">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div class="subheader">
                            Total Backup
                        </div>

                    </div>

                    <div
                        id="totalBackup"
                        class="h1 mb-0"
                    >
                        {{ count($backups) }}
                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card">

                <div class="card-body">

                    <div class="subheader">
                        Backup Terakhir
                    </div>

                    <div
                        id="lastBackup"
                        class="h3 mb-0"
                    >

                        @if(count($backups) > 0)

                            {{ $backups[0]['date'] }}

                        @else

                            Belum ada backup

                        @endif

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card">

                <div class="card-body">

                    <div class="subheader">
                        Status
                    </div>

                    <div class="h3 mb-0">

                        <span class="badge bg-green-lt">
                            Sistem Backup Aktif
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         BACKUP LIST
    ========================================================== --}}

    <div class="card mt-3">

        <div class="card-header">

            <h3 class="card-title">
                Riwayat Backup
            </h3>

        </div>


        <div class="table-responsive">

            <table class="table table-vcenter card-table">

                <thead>

                    <tr>

                        <th>
                            File Backup
                        </th>

                        <th>
                            Tanggal
                        </th>

                        <th>
                            Ukuran
                        </th>

                        <th class="text-end">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody id="backupTableBody">

                    @forelse($backups as $backup)

                        <tr id="backup-row-{{ md5($backup['filename']) }}">

                            <td>

                                <div class="d-flex align-items-center">

                                    <span class="avatar avatar-sm me-2">
                                        <i class="ti ti-file-zip"></i>
                                    </span>

                                    <div>

                                        <div class="fw-bold">
                                            {{ $backup['name'] }}
                                        </div>

                                        <div class="text-muted">
                                            ZIP Backup
                                        </div>

                                    </div>

                                </div>

                            </td>


                            <td>
                                {{ $backup['date'] }}
                            </td>


                            <td>
                                {{ $backup['size'] }}
                            </td>


                            <td class="text-end">

                                <div class="btn-list justify-content-end">

                                    <a
                                        href="{{ route('backup.download', $backup['filename']) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >

                                        <i class="ti ti-download me-1"></i>

                                        Download

                                    </a>


                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger btn-delete-backup"
                                        data-filename="{{ $backup['filename'] }}"
                                        data-row="backup-row-{{ md5($backup['filename']) }}"
                                    >

                                        <i class="ti ti-trash me-1"></i>

                                        Hapus

                                    </button>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr id="emptyBackupRow">

                            <td
                                colspan="4"
                                class="text-center text-muted py-5"
                            >

                                <i class="ti ti-database-off fs-1 d-block mb-2"></i>

                                Belum ada backup.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>


@endsection

@section('scripts')

<script>

$(document).ready(function () {


    /*
    |--------------------------------------------------------------------------
    | Create Backup
    |--------------------------------------------------------------------------
    */

    $('#btnCreateBackup').on('click', function () {

        const button = $(this);


        /*
        |--------------------------------------------------------------------------
        | Confirmation
        |--------------------------------------------------------------------------
        */

        if (!confirm(
            'Buat backup RudyPOS sekarang?'
        )) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Loading
        |--------------------------------------------------------------------------
        */

        button
            .prop('disabled', true)
            .html(
                '<span class="spinner-border spinner-border-sm me-1"></span>' +
                ' Membuat Backup...'
            );


        /*
        |--------------------------------------------------------------------------
        | AJAX
        |--------------------------------------------------------------------------
        */

        $.ajax({

            url: "{{ route('backup.create') }}",

            type: "POST",

            headers: {
                'X-CSRF-TOKEN':
                    $('meta[name="csrf-token"]').attr('content'),

                'X-Requested-With':
                    'XMLHttpRequest',

                'Accept':
                    'application/json'
            },


            success: function (response) {

                showBackupAlert(
                    'success',
                    '<i class="ti ti-check me-1"></i>' +
                    response.message
                );


                /*
                |--------------------------------------------------------------------------
                | Tambahkan Row
                |--------------------------------------------------------------------------
                */

                addBackupRow(
                    response.backup
                );


                updateBackupSummary(
                    response.backup
                );

            },


            error: function (xhr) {

                console.error(
                    'Backup Error:',
                    xhr.responseJSON
                );


                let message =
                    'Backup gagal dibuat.';


                if (
                    xhr.responseJSON &&
                    xhr.responseJSON.error
                ) {

                    message =
                        xhr.responseJSON.error;

                }


                showBackupAlert(
                    'danger',
                    '<i class="ti ti-alert-circle me-1"></i>' +
                    message
                );

            },


            complete: function () {

                button
                    .prop('disabled', false)
                    .html(
                        '<i class="ti ti-database-export me-1"></i>' +
                        ' Backup Sekarang'
                    );

            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | Delete Backup
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'click',
        '.btn-delete-backup',
        function () {

            const button =
                $(this);

            const filename =
                button.data('filename');

            const rowId =
                button.data('row');


            if (!confirm(
                'Hapus backup ini? Data backup yang dihapus tidak dapat dikembalikan.'
            )) {
                return;
            }


            button
                .prop('disabled', true)
                .html(
                    '<span class="spinner-border spinner-border-sm"></span>'
                );


            $.ajax({

                url:
                    "{{ url('/backup') }}/" +
                    encodeURIComponent(filename),

                type: "DELETE",

                headers: {

                    'X-CSRF-TOKEN':
                        $('meta[name="csrf-token"]').attr('content'),

                    'X-Requested-With':
                        'XMLHttpRequest',

                    'Accept':
                        'application/json'
                },


                success: function (response) {

                    $('#' + rowId)
                        .fadeOut(
                            300,
                            function () {

                                $(this).remove();

                                updateBackupCount();

                            }
                        );


                    showBackupAlert(
                        'success',
                        '<i class="ti ti-check me-1"></i>' +
                        response.message
                    );

                },


                error: function (xhr) {

                    button
                        .prop('disabled', false)
                        .html(
                            '<i class="ti ti-trash me-1"></i>' +
                            ' Hapus'
                        );


                    let message =
                        'Backup gagal dihapus.';


                    if (
                        xhr.responseJSON &&
                        xhr.responseJSON.message
                    ) {

                        message =
                            xhr.responseJSON.message;

                    }


                    showBackupAlert(
                        'danger',
                        '<i class="ti ti-alert-circle me-1"></i>' +
                        message
                    );

                }

            });

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Alert
    |--------------------------------------------------------------------------
    */

    function showBackupAlert(
        type,
        message
    ) {

        $('#backupAlert')
            .removeClass(
                'd-none alert-success alert-danger'
            )
            .addClass(
                'alert-' + type
            )
            .html(message);

    }


    /*
    |--------------------------------------------------------------------------
    | Add Backup Row
    |--------------------------------------------------------------------------
    */

    function addBackupRow(
        backup
    ) {

        $('#emptyBackupRow')
            .remove();


        const rowId =
            'backup-row-' +
            md5Backup(backup.filename);


        const downloadUrl =
            "{{ url('/backup') }}/" +
            encodeURIComponent(
                backup.filename
            ) +
            "/download";


        const row = `

            <tr id="${rowId}">

                <td>

                    <div class="d-flex align-items-center">

                        <span class="avatar avatar-sm me-2">
                            <i class="ti ti-file-zip"></i>
                        </span>

                        <div>

                            <div class="fw-bold">
                                ${backup.name}
                            </div>

                            <div class="text-muted">
                                ZIP Backup
                            </div>

                        </div>

                    </div>

                </td>


                <td>
                    ${backup.date}
                </td>


                <td>
                    ${backup.size}
                </td>


                <td class="text-end">

                    <div class="btn-list justify-content-end">

                        <a
                            href="${downloadUrl}"
                            class="btn btn-sm btn-outline-primary"
                        >

                            <i class="ti ti-download me-1"></i>

                            Download

                        </a>


                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger btn-delete-backup"
                            data-filename="${backup.filename}"
                            data-row="${rowId}"
                        >

                            <i class="ti ti-trash me-1"></i>

                            Hapus

                        </button>

                    </div>

                </td>

            </tr>

        `;


        $('#backupTableBody')
            .prepend(row);

    }


    /*
    |--------------------------------------------------------------------------
    | Update Summary
    |--------------------------------------------------------------------------
    */

    function updateBackupSummary(
        backup
    ) {

        let total =
            parseInt(
                $('#totalBackup').text()
            ) || 0;


        total++;


        $('#totalBackup')
            .text(total);


        $('#lastBackup')
            .text(backup.date);

    }


    /*
    |--------------------------------------------------------------------------
    | Update Count
    |--------------------------------------------------------------------------
    */

    function updateBackupCount()
    {

        let total =
            $('#backupTableBody tr').length;


        /*
        |--------------------------------------------------------------------------
        | Empty State
        |--------------------------------------------------------------------------
        */

        if (total === 0) {

            $('#backupTableBody').html(`

                <tr id="emptyBackupRow">

                    <td
                        colspan="4"
                        class="text-center text-muted py-5"
                    >

                        <i class="ti ti-database-off fs-1 d-block mb-2"></i>

                        Belum ada backup.

                    </td>

                </tr>

            `);

        }


        $('#totalBackup')
            .text(total);

    }


    /*
    |--------------------------------------------------------------------------
    | Simple MD5-compatible Row ID
    |--------------------------------------------------------------------------
    |
    | Untuk sementara kita menggunakan hash sederhana.
    |
    */

    function md5Backup(
        text
    ) {

        let hash = 0;


        for (
            let i = 0;
            i < text.length;
            i++
        ) {

            hash =
                (
                    (
                        hash << 5
                    ) -
                    hash
                ) +
                text.charCodeAt(i);

            hash |= 0;
        }


        return Math.abs(hash);

    }

});

</script>
@endsection