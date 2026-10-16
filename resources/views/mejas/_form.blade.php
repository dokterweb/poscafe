@csrf

@if(isset($meja))
    @method('PUT')
@endif

<div class="mb-3">
    <label for="nomor" class="form-label">Nomor Meja <span class="text-danger">*</span></label>
    <input
        type="text"
        name="nomor"
        id="nomor"
        class="form-control"
        maxlength="20"
        value="{{ old('nomor', $meja->nomor ?? '') }}"
        placeholder="Contoh: M01"
        required
    >
    <div class="invalid-feedback" data-error="nomor"></div>
</div>

<div class="mb-3">
    <label for="nama" class="form-label">Nama Meja</label>
    <input
        type="text"
        name="nama"
        id="nama"
        class="form-control"
        maxlength="100"
        value="{{ old('nama', $meja->nama ?? '') }}"
        placeholder="Contoh: Meja Dekat Jendela"
    >
    <div class="invalid-feedback" data-error="nama"></div>
</div>

<div class="mb-3">
    <label for="kapasitas" class="form-label">Kapasitas Kursi <span class="text-danger">*</span></label>
    <input
        type="number"
        name="kapasitas"
        id="kapasitas"
        class="form-control"
        min="1"
        value="{{ old('kapasitas', $meja->kapasitas ?? 2) }}"
        required
    >
    <div class="invalid-feedback" data-error="kapasitas"></div>
</div>

<div class="mb-3">
    <label for="is_active" class="form-label">Status</label>
    <select name="is_active" id="is_active" class="form-select" required>
        <option value="1"
            @selected((string) old('is_active', isset($meja) ? (int) $meja->is_active : 1) === '1')>
            Aktif
        </option>
        <option value="0"
            @selected((string) old('is_active', isset($meja) ? (int) $meja->is_active : 1) === '0')>
            Nonaktif
        </option>
    </select>
    <div class="invalid-feedback" data-error="is_active"></div>
</div>

<div class="d-flex justify-content-between">
    <a href="{{ route('mejas.index') }}" class="btn btn-secondary">
        Kembali
    </a>

    <button type="submit" class="btn btn-primary" id="btn-submit">
        <i class="ti ti-device-floppy me-1"></i>
        <span id="btn-submit-text">
            {{ isset($meja) ? 'Simpan Perubahan' : 'Simpan Meja' }}
        </span>
    </button>
</div>

@section('scripts')
<script>
$(function () {
    const form = $('#form-meja');

    form.on('submit', function (e) {
        e.preventDefault();

        $('.is-invalid').removeClass('is-invalid');
        $('[data-error]').text('');

        const button = $('#btn-submit');
        const originalText = $('#btn-submit-text').text();

        button.prop('disabled', true);
        $('#btn-submit-text').text('Menyimpan...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',

            success: function (response) {
                if (response.status === 'success') {
                    window.location.href = response.redirect;
                }
            },

            error: function (xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors || {};

                    $.each(errors, function (field, messages) {
                        const input = form.find('[name="' + field + '"]');

                        input.addClass('is-invalid');

                        // Tampilkan pesan validasi untuk field yang bermasalah.
                        const errorElement = form.find('[data-error="' + field + '"]');
                        if (errorElement.length) {
                            errorElement.text(messages[0]);
                        } else {
                            alert(messages[0]);
                        }
                    });
                } else {
                    const message = xhr.responseJSON?.message
                        || 'Terjadi kesalahan. Silakan coba kembali.';
                    alert(message);
                }
            },

            complete: function () {
                button.prop('disabled', false);
                $('#btn-submit-text').text(originalText);
            }
        });
    });
});
</script>
@endsection
