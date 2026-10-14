@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    {{-- Header --}}
    <div class="page-header d-print-none mb-3">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">Profile</h2>
                    <div class="text-muted mt-1">Kelola informasi akun Anda</div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">

                {{-- =========================================================
                    PROFILE CARD
                ========================================================== --}}
                <div class="col-md-4 col-lg-3">
                    <div class="card">
                        <div class="card-body text-center">
                            {{-- Avatar --}}
                            <div class="mb-3">
                                @if($user->avatar)
                                    <img id="profileAvatar" src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}"
                                        class="avatar avatar-xl rounded" style="object-fit: cover;">
                                @else
                                    <span id="profileAvatarDefault" class="avatar avatar-xl rounded">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </span>
                                @endif
                            </div>

                            {{-- Name --}}
                            <h3 id="profileName" class="m-0 mb-1">{{ $user->name }}</h3>
                            {{-- Email --}}
                            <div id="profileEmail" class="text-muted">{{ $user->email }}</div>
                            {{-- Role --}}
                            @if(method_exists($user, 'getRoleNames'))
                                <div class="mt-3">
                                    @foreach($user->getRoleNames() as $role)
                                        <span class="badge bg-blue-lt">{{ ucfirst($role) }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                {{-- =========================================================
                    EDIT PROFILE
                ========================================================== --}}
                <div class="col-md-8 col-lg-9">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Edit Profile</h3>
                        </div>
                        <div class="card-body">
                            {{-- Alert --}}
                            <div id="profileAlert" class="alert d-none" role="alert"></div>
                            <form id="profileForm" enctype="multipart/form-data">
                                @csrf
                                @method('PATCH')
                                <div class="row">
                                    {{-- =================================================
                                        NAMA
                                    ================================================== --}}
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nama
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="name" id="name" class="form-control"
                                            value="{{ old('name', $user->name) }}" required>
                                        <div id="error-name" class="invalid-feedback"></div>
                                    </div>
                                    {{-- =================================================
                                        EMAIL
                                    ================================================== --}}
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" name="email" id="email" class="form-control"
                                            value="{{ old('email', $user->email) }}" required>
                                        <div id="error-email" class="invalid-feedback"></div>
                                    </div>
                                    {{-- =================================================
                                        AVATAR
                                    ================================================== --}}
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Avatar</label>
                                        <input type="file" name="avatar" id="avatar" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                                        <div class="form-hint">
                                            Format JPG, JPEG, PNG atau WEBP.
                                            Maksimal 2 MB.
                                        </div>
                                        <div id="error-avatar" class="invalid-feedback"></div>
                                    </div>
                                    {{-- =================================================
                                        PREVIEW
                                    ================================================== --}}
                                    <div id="avatarPreviewContainer" class="col-md-12 mb-3 d-none">
                                        <label class="form-label">Preview Avatar</label>
                                        <div>
                                            <img id="avatarPreview" src="" alt="Preview" class="avatar avatar-xl rounded" style="object-fit: cover;">
                                        </div>
                                    </div>
                                </div>

                                {{-- Button --}}
                                <div class="mt-3">
                                    <button type="submit" id="btnSaveProfile" class="btn btn-primary">
                                        <i class="ti ti-device-floppy me-1"></i>
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card mt-3">

                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-lock me-2"></i>
                        Keamanan Akun
                    </h3>
                </div>

                <div class="card-body">
                    <div id="passwordAlert" class="alert d-none" role="alert"></div>
                    <form id="passwordForm" action="{{ route('profile.password.update') }}" method="POST">
                        @csrf
                        @method('PATCH')

                        {{-- Password Saat Ini --}}
                        <div class="mb-3">

                            <label class="form-label">
                                Password Saat Ini
                                <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">

                                <input type="password" name="current_password" id="current_password" class="form-control" autocomplete="current-password" required >
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="current_password" >
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <div id="error-current_password" class="invalid-feedback"></div>
                        </div>

                        {{-- Password Baru --}}
                        <div class="mb-3">

                            <label class="form-label">Password Baru
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control" autocomplete="new-password" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password"data-target="password">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-hint">Minimal 8 karakter.</div>
                            <div id="error-password" class="invalid-feedback"></div>
                        </div>

                        {{-- Konfirmasi Password --}}
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password Baru
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" autocomplete="new-password" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password_confirmation" >
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <div id="error-password_confirmation" class="invalid-feedback"></div>
                        </div>
                        <button type="submit" id="btnChangePassword" class="btn btn-warning" >
                            <i class="fas fa-key"></i>
                            Ubah Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
</div>



@endsection

@section('scripts')

{{-- =========================================================
     JAVASCRIPT
========================================================== --}}
<script>

$(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | Preview Avatar
    |--------------------------------------------------------------------------
    */

    $('#avatar').on('change', function () {

        const file = this.files[0];

        if (!file) {

            $('#avatarPreviewContainer').addClass('d-none');

            return;
        }

        const reader = new FileReader();

        reader.onload = function (e) {

            $('#avatarPreview')
                .attr('src', e.target.result);

            $('#avatarPreviewContainer')
                .removeClass('d-none');
        };

        reader.readAsDataURL(file);
    });


    /*
    |--------------------------------------------------------------------------
    | Submit Profile
    |--------------------------------------------------------------------------
    */

    $('#profileForm').on('submit', function (e) {

        e.preventDefault();

        const form = this;

        const formData = new FormData(form);

        const button = $('#btnSaveProfile');


        /*
        |--------------------------------------------------------------------------
        | Reset Error
        |--------------------------------------------------------------------------
        */

        $('.form-control').removeClass('is-invalid');

        $('.invalid-feedback').text('');

        $('#profileAlert')
            .addClass('d-none')
            .removeClass('alert-success alert-danger')
            .html('');


        /*
        |--------------------------------------------------------------------------
        | Loading
        |--------------------------------------------------------------------------
        */

        button
            .prop('disabled', true)
            .html(
                '<span class="spinner-border spinner-border-sm me-1"></span>' +
                ' Menyimpan...'
            );


        /*
        |--------------------------------------------------------------------------
        | AJAX
        |--------------------------------------------------------------------------
        */

        $.ajax({

            url: "{{ route('profile.update') }}",

            type: "POST",

            data: formData,

            processData: false,

            contentType: false,

            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },

            success: function (response) {

                /*
                |--------------------------------------------------------------------------
                | Alert Success
                |--------------------------------------------------------------------------
                */

                $('#profileAlert')
                    .removeClass('d-none alert-danger')
                    .addClass('alert-success')
                    .html(
                        '<i class="ti ti-check me-1"></i>' +
                        response.message
                    );


                /*
                |--------------------------------------------------------------------------
                | Update Name
                |--------------------------------------------------------------------------
                */

                $('#profileName')
                    .text(response.user.name);


                /*
                |--------------------------------------------------------------------------
                | Update Email
                |--------------------------------------------------------------------------
                */

                $('#profileEmail')
                    .text(response.user.email);


                /*
                |--------------------------------------------------------------------------
                | Update Avatar
                |--------------------------------------------------------------------------
                */

                if (response.user.avatar) {

                    $('#profileAvatarDefault').remove();

                    if ($('#profileAvatar').length) {

                        $('#profileAvatar')
                            .attr('src', response.user.avatar);

                    } else {

                        $('<img>', {
                            id: 'profileAvatar',
                            src: response.user.avatar,
                            alt: response.user.name,
                            class: 'avatar avatar-xl rounded',
                            css: {
                                objectFit: 'cover'
                            }
                        }).appendTo(
                            $('#profileAvatar').parent()
                        );

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | Reset File
                |--------------------------------------------------------------------------
                */

                $('#avatar').val('');

                $('#avatarPreviewContainer')
                    .addClass('d-none');


                /*
                |--------------------------------------------------------------------------
                | Hide Alert
                |--------------------------------------------------------------------------
                */

                setTimeout(function () {

                    $('#profileAlert')
                        .fadeOut(300, function () {

                            $(this)
                                .addClass('d-none')
                                .show();

                        });

                }, 3000);

            },

            error: function (xhr) {

                /*
                |--------------------------------------------------------------------------
                | Validation Error
                |--------------------------------------------------------------------------
                */

                if (xhr.status === 422) {

                    const errors = xhr.responseJSON.errors;

                    $.each(errors, function (field, messages) {

                        $('#' + field)
                            .addClass('is-invalid');

                        $('#error-' + field)
                            .text(messages[0]);

                    });

                    $('#profileAlert')
                        .removeClass('d-none alert-success')
                        .addClass('alert-danger')
                        .html(
                            '<i class="ti ti-alert-circle me-1"></i>' +
                            'Periksa kembali data yang dimasukkan.'
                        );

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Error lainnya
                |--------------------------------------------------------------------------
                */

                let message =
                    'Terjadi kesalahan. Silakan coba lagi.';

                if (
                    xhr.responseJSON &&
                    xhr.responseJSON.message
                ) {
                    message = xhr.responseJSON.message;
                }

                $('#profileAlert')
                    .removeClass('d-none alert-success')
                    .addClass('alert-danger')
                    .html(
                        '<i class="ti ti-alert-circle me-1"></i>' +
                        message
                    );

            },

            complete: function () {

                button
                    .prop('disabled', false)
                    .html(
                        '<i class="ti ti-device-floppy me-1"></i>' +
                        ' Simpan Perubahan'
                    );

            }

        });

    });

    /*
|--------------------------------------------------------------------------
| Toggle Password
|--------------------------------------------------------------------------
*/

$(document).on('click', '.toggle-password', function () {

    const targetId = $(this).data('target');

    const input = $('#' + targetId);

    const icon = $(this).find('i');


    if (input.attr('type') === 'password') {

        input.attr('type', 'text');

        icon
            .removeClass('ti-eye')
            .addClass('ti-eye-off');

    } else {

        input.attr('type', 'password');

        icon
            .removeClass('ti-eye-off')
            .addClass('ti-eye');

    }

});


    /*
    |--------------------------------------------------------------------------
    | Change Password
    |--------------------------------------------------------------------------
    */

    $('#passwordForm').on('submit', function (e) {

        e.preventDefault();

        const form = this;

        const formData = new FormData(form);

        const button = $('#btnChangePassword');

        const alertBox = $('#passwordAlert');


        /*
        |--------------------------------------------------------------------------
        | Reset Error
        |--------------------------------------------------------------------------
        */

        $('#passwordForm .form-control')
            .removeClass('is-invalid');

        $('#passwordForm .invalid-feedback')
            .text('');

        alertBox
            .addClass('d-none')
            .removeClass('alert-success alert-danger')
            .html('');


        /*
        |--------------------------------------------------------------------------
        | Loading
        |--------------------------------------------------------------------------
        */

        button
            .prop('disabled', true)
            .html(
                '<span class="spinner-border spinner-border-sm me-1"></span>' +
                ' Menyimpan...'
            );


        /*
        |--------------------------------------------------------------------------
        | AJAX
        |--------------------------------------------------------------------------
        */

        $.ajax({

            url: "{{ route('profile.password.update') }}",

            type: "POST",

            data: formData,

            processData: false,

            contentType: false,

            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },


            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            success: function (response) {

                alertBox
                    .removeClass('d-none alert-danger')
                    .addClass('alert-success')
                    .html(
                        '<i class="ti ti-check me-1"></i>' +
                        response.message
                    );


                /*
                |--------------------------------------------------------------------------
                | Reset Form
                |--------------------------------------------------------------------------
                */

                $('#passwordForm')[0].reset();


                /*
                |--------------------------------------------------------------------------
                | Hide Alert
                |--------------------------------------------------------------------------
                */

                setTimeout(function () {

                    alertBox.fadeOut(300, function () {

                        $(this)
                            .addClass('d-none')
                            .show();

                    });

                }, 3000);

            },


            /*
            |--------------------------------------------------------------------------
            | Error
            |--------------------------------------------------------------------------
            */

            error: function (xhr) {

                /*
                |--------------------------------------------------------------------------
                | Validation Error
                |--------------------------------------------------------------------------
                */

                if (xhr.status === 422) {

                    const errors =
                        xhr.responseJSON.errors || {};


                    $.each(errors, function (field, messages) {

                        $('#' + field)
                            .addClass('is-invalid');

                        $('#error-' + field)
                            .text(messages[0]);

                    });


                    alertBox
                        .removeClass('d-none alert-success')
                        .addClass('alert-danger')
                        .html(
                            '<i class="ti ti-alert-circle me-1"></i>' +
                            'Periksa kembali password yang dimasukkan.'
                        );

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Error lainnya
                |--------------------------------------------------------------------------
                */

                let message =
                    'Terjadi kesalahan. Silakan coba lagi.';


                if (
                    xhr.responseJSON &&
                    xhr.responseJSON.message
                ) {

                    message =
                        xhr.responseJSON.message;

                }


                alertBox
                    .removeClass('d-none alert-success')
                    .addClass('alert-danger')
                    .html(
                        '<i class="ti ti-alert-circle me-1"></i>' +
                        message
                    );

            },


            /*
            |--------------------------------------------------------------------------
            | Complete
            |--------------------------------------------------------------------------
            */

            complete: function () {

                button
                    .prop('disabled', false)
                    .html(
                        '<i class="ti ti-key me-1"></i>' +
                        ' Ubah Password'
                    );

            }

        });

    });

});

</script>

@endsection