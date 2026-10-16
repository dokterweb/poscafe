
@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col">
                <div class="mb-1">
                    <a href="{{ route('products.index') }}" class="text-secondary">
                        <i class="ti ti-arrow-left me-1"></i>
                        Kembali ke Daftar Produk
                    </a>
                </div>

                <h2 class="page-title">Tambah Produk</h2>
                <div class="text-secondary mt-1">
                    Tambahkan produk beserta varian dan kelompok pilihan menu.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Notifikasi AJAX --}}
        <div id="create-alert-container"></div>

        <form id="formCreateProduct"
              action="{{ route('products.store') }}"
              method="POST">

            @csrf

            {{-- INFORMASI UTAMA --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-package me-2"></i>
                        Informasi Produk
                    </h3>
                </div>

                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label required">
                                Nama Produk
                            </label>

                            <input
                                type="text"
                                name="name"
                                id="name"
                                class="form-control"
                                placeholder="Contoh: Es Kopi Susu"
                                maxlength="150"
                                required
                            >

                            <div class="invalid-feedback" data-error="name"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Kategori
                            </label>

                            <select
                                name="category_id"
                                id="category_id"
                                class="form-select"
                            >
                                <option value="">Pilih Kategori</option>

                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="invalid-feedback" data-error="category_id"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">
                                HPP Dasar (Rp)
                            </label>

                            <input
                                type="text"
                                name="hpp"
                                id="hpp"
                                class="form-control money-input"
                                placeholder="Contoh: 150.000"
                                inputmode="numeric"
                                autocomplete="off"
                                value="0"
                                required
                            >

                            <div class="form-hint">
                                Digunakan jika produk tidak memiliki varian.
                            </div>

                            <div class="invalid-feedback" data-error="hpp"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">
                                Harga Jual Dasar (Rp)
                            </label>

                            <input
                                type="text"
                                name="price"
                                id="price"
                                class="form-control money-input"
                                placeholder="Contoh: 200.000"
                                inputmode="numeric"
                                autocomplete="off"
                                value="0"
                                required
                            >

                            <div class="form-hint">
                                Digunakan jika produk tidak memiliki varian.
                            </div>

                            <div class="invalid-feedback" data-error="price"></div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- PENGATURAN STOK --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-box me-2"></i>
                        Pengaturan Stok
                    </h3>
                </div>

                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        {{-- Nilai 0 ketika checkbox tidak dicentang --}}
                        <input
                            type="hidden"
                            name="track_stock"
                            value="0"
                        >

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="track_stock"
                            value="1"
                            id="track_stock"
                            checked
                        >

                        <label class="form-check-label fw-bold" for="track_stock">
                            Lacak stok produk
                        </label>

                        <div class="form-hint">
                            Aktifkan untuk produk yang memiliki stok barang jadi,
                            misalnya air mineral kemasan.
                        </div>
                    </div>

                    <div id="stock-section">
                        <label class="form-label required">
                            Stok Awal
                        </label>

                        <input
                            type="number"
                            name="stock"
                            id="stock"
                            class="form-control"
                            min="0"
                            step="1"
                            value="0"
                        >

                        <div class="form-hint">
                            Masukkan jumlah stok awal produk.
                        </div>

                        <div class="invalid-feedback" data-error="stock"></div>
                    </div>

                    <div id="stock-disabled-info" class="alert alert-info d-none mb-0">
                        Stok tidak dilacak. Sistem tidak akan mengurangi stok
                        produk ini ketika terjadi penjualan.
                    </div>
                </div>
            </div>

            {{-- VARIAN PRODUK --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">
                            <i class="ti ti-layers-intersect me-2"></i>
                            Varian Produk
                        </h3>

                        <div class="text-secondary small mt-1">
                            Opsional. Contoh: Regular, Large, atau Extra Large.
                        </div>
                    </div>

                    <div class="card-actions">
                        <button
                            type="button"
                            class="btn btn-outline-primary btn-sm"
                            id="btn-add-variant"
                        >
                            <i class="ti ti-plus me-1"></i>
                            Tambah Varian
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-1"></i>

                        Jika produk memiliki varian, isi HPP dan harga jual
                        masing-masing varian. Jika tidak memiliki varian,
                        gunakan HPP dan harga jual dasar di atas.
                    </div>

                    <div id="variants-container"></div>

                    <div id="empty-variants-message" class="text-center py-4">
                        <i class="ti ti-layers-subtract fs-1 text-secondary"></i>

                        <div class="fw-bold mt-2">
                            Belum ada varian
                        </div>

                        <div class="text-secondary small">
                            Klik tombol Tambah Varian jika produk memiliki pilihan ukuran.
                        </div>
                    </div>

                    <div id="variant-validation-errors"></div>
                </div>
            </div>

            {{-- KELOMPOK PILIHAN --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">
                            <i class="ti ti-list-check me-2"></i>
                            Kelompok Pilihan
                        </h3>

                        <div class="text-secondary small mt-1">
                            Opsional. Pilih kelompok pilihan yang tersedia untuk produk ini.
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if($optionGroups->isEmpty())
                        <div class="alert alert-warning mb-0">
                            <i class="ti ti-alert-triangle me-1"></i>

                            Belum ada kelompok pilihan dengan pilihan aktif.
                            Silakan tambahkan kelompok pilihan melalui menu
                            master kelompok pilihan terlebih dahulu.
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($optionGroups as $group)
                                <div class="col-md-6">
                                    <label class="form-selectgroup-item w-100">
                                        <input
                                            type="checkbox"
                                            name="option_group_ids[]"
                                            value="{{ $group->id }}"
                                            class="form-selectgroup-input option-group-checkbox"
                                        >

                                        <span class="form-selectgroup-label d-flex align-items-start p-3">
                                            <span class="me-3">
                                                <i class="ti ti-list-check fs-2"></i>
                                            </span>

                                            <span class="flex-fill">
                                                <span class="d-block fw-bold">
                                                    {{ $group->name }}
                                                </span>

                                                <span class="d-block text-secondary small mt-1">
                                                    @foreach($group->options as $option)
                                                        <span class="badge bg-secondary-lt me-1 mb-1">
                                                            {{ $option->name }}

                                                            @if((float) $option->price_adjustment > 0)
                                                                (+Rp {{ number_format((float) $option->price_adjustment, 0, ',', '.') }})
                                                            @endif
                                                        </span>
                                                    @endforeach
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="form-hint mt-3">
                            Contoh: kelompok Suhu untuk pilihan Hot/Iced,
                            atau Topping untuk pilihan tambahan.
                            Pilihan yang dicentang akan dikaitkan dengan produk.
                        </div>
                    @endif
                </div>
            </div>

            {{-- TOMBOL SIMPAN --}}
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
                        <a
                            href="{{ route('products.index') }}"
                            class="btn btn-outline-secondary"
                        >
                            <i class="ti ti-arrow-left me-1"></i>
                            Batal
                        </a>

                        <button
                            type="submit"
                            class="btn btn-primary"
                            id="btn-save-product"
                        >
                            <i class="ti ti-device-floppy me-1"></i>
                            Simpan Produk
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function () {
    let variantIndex = 0;

    const $form = $('#formCreateProduct');
    const $saveButton = $('#btn-save-product');

    // Escape teks agar aman saat dimasukkan ke HTML.
    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    // Format angka Rupiah untuk tampilan.
    function formatRupiah(value) {
        return new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 0
        }).format(Number(value || 0));
    }

    // Template baris varian.
    function variantRowHtml(index) {
        return `
            <div class="card card-sm mb-3 variant-row">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="fw-bold variant-title">
                            Varian Baru
                        </div>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger btn-remove-variant"
                            title="Hapus varian"
                        >
                            <i class="ti ti-trash me-1"></i>
                            Hapus
                        </button>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label required">
                                Nama Varian
                            </label>

                            <input
                                type="text"
                                name="variants[${index}][name]"
                                class="form-control variant-name"
                                placeholder="Contoh: Regular"
                                maxlength="100"
                                required
                            >

                            <div
                                class="invalid-feedback"
                                data-error="variants.${index}.name"
                            ></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label required">
                                HPP (Rp)
                            </label>

                            <input
                                type="text"
                                name="variants[${index}][hpp]"
                                class="form-control money-input"
                                placeholder="Contoh: 100.000"
                                inputmode="numeric"
                                autocomplete="off"
                                value="0"
                                required
                            >

                            <div
                                class="invalid-feedback"
                                data-error="variants.${index}.hpp"
                            ></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label required">
                                Harga Jual (Rp)
                            </label>

                            <input
                                type="text"
                                name="variants[${index}][price]"
                                class="form-control money-input"
                                placeholder="Contoh: 150.000"
                                inputmode="numeric"
                                autocomplete="off"
                                value="0"
                                required
                            >

                            <div
                                class="invalid-feedback"
                                data-error="variants.${index}.price"
                            ></div>
                        </div>

                        <div class="col-12">
                            <input
                                type="hidden"
                                name="variants[${index}][is_active]"
                                value="0"
                            >

                            <label class="form-check form-switch">
                                <input
                                    type="checkbox"
                                    name="variants[${index}][is_active]"
                                    value="1"
                                    class="form-check-input"
                                    checked
                                >

                                <span class="form-check-label">
                                    Varian aktif
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Tambah varian.
    function addVariant() {
        $('#variants-container').append(
            variantRowHtml(variantIndex)
        );

        variantIndex++;

        updateVariantState();
    }

    // Perbarui judul dan tampilan ketika jumlah varian berubah.
    function updateVariantState() {
        const $rows = $('.variant-row');

        $('#empty-variants-message').toggle($rows.length === 0);

        $rows.each(function (index) {
            $(this).find('.variant-title').text(
                'Varian ' + (index + 1)
            );
        });
    }

    $('#btn-add-variant').on('click', function () {
        addVariant();
    });

    // Hapus baris varian dari form.
    $(document).on('click', '.btn-remove-variant', function () {
        $(this).closest('.variant-row').remove();

        updateVariantState();
    });

    // Pengaturan stok.
    function updateStockState() {
        const trackStock = $('#track_stock').is(':checked');

        $('#stock-section').toggleClass('d-none', !trackStock);
        $('#stock-disabled-info').toggleClass('d-none', trackStock);

        $('#stock')
            .prop('disabled', !trackStock)
            .prop('required', trackStock);

        if (!trackStock) {
            $('#stock').val(0);
            clearFieldError('stock');
        }
    }

    $('#track_stock').on('change', updateStockState);

    updateStockState();

    // Bersihkan error validasi.
    function clearFieldError(field) {
        const $field = $form.find('[name="' + field + '"]');

        $field.removeClass('is-invalid');

        $('[data-error="' + field + '"]')
            .text('')
            .hide();
    }

    function clearAllErrors() {
        $form.find('.is-invalid').removeClass('is-invalid');

        $form.find('[data-error]')
            .text('')
            .hide();

        $('#create-alert-container').empty();
        $('#variant-validation-errors').empty();
    }

    // Tampilkan kesalahan validasi dari Laravel.
    function showValidationErrors(errors) {
        let messages = [];

        Object.keys(errors).forEach(function (field) {
            const fieldMessages = errors[field] || [];
            const message = fieldMessages[0] || 'Data tidak valid.';

            messages.push(message);

            // Format nama field Laravel, contoh:
            // variants.0.name menjadi variants[0][name].
            const inputName = field.replace(
                /^variants\.(\d+)\.(name|hpp|price|is_active)$/,
                'variants[$1][$2]'
            );

            const $input = $form.find('[name="' + inputName + '"]');

            if ($input.length) {
                $input.addClass('is-invalid');

                const $error = $('[data-error="' + field + '"]');

                if ($error.length) {
                    $error.text(message).show();
                } else {
                    $input.closest('.col-md-4')
                        .find('.invalid-feedback')
                        .text(message)
                        .show();
                }
            } else if (field.startsWith('variants.')) {
                $('#variant-validation-errors').append(
                    '<div class="text-danger small mb-1">' +
                    escapeHtml(message) +
                    '</div>'
                );
            }
        });

        const list = messages.map(function (message) {
            return '<li>' + escapeHtml(message) + '</li>';
        }).join('');

        $('#create-alert-container').html(`
            <div class="alert alert-danger alert-dismissible" role="alert">
                <div class="fw-bold mb-1">
                    Periksa kembali data yang diisi.
                </div>

                <ul class="mb-0">
                    ${list}
                </ul>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Tutup"
                ></button>
            </div>
        `);

        $('html, body').animate({
            scrollTop: $('#create-alert-container').offset().top - 100
        }, 300);
    }

    // Hilangkan error suatu field saat mulai diperbaiki.
    $form.on('input change', 'input, select, textarea', function () {
        $(this).removeClass('is-invalid');

        const fieldName = $(this).attr('name');

        if (fieldName) {
            const normalizedField = fieldName
                .replace(/^variants\[(\d+)\]\[(name|hpp|price|is_active)\]$/, 'variants.$1.$2');

            $('[data-error="' + normalizedField + '"]')
                .text('')
                .hide();
        }
    });

    // Simpan produk dengan AJAX.
    $form.on('submit', function (e) {
        e.preventDefault();

        clearAllErrors();

        if (!$form[0].checkValidity()) {
            $form[0].reportValidity();
            return;
        }

        const originalButtonHtml = $saveButton.html();

        $saveButton
            .prop('disabled', true)
            .html(
                '<span class="spinner-border spinner-border-sm me-2"></span>' +
                'Menyimpan produk...'
            );

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: (function () {
                const formData = $form.serializeArray();

                formData.forEach(function (item) {
                    const $input = $form.find('[name]').filter(function () {
                        return this.name === item.name &&
                            $(this).hasClass('money-input');
                    }).first();

                    if ($input.length) {
                        item.value = $input.val().replace(/\./g, '');
                    }
                });

                return $.param(formData);
            })(),
            dataType: 'json',

            success: function (response) {
                $('#create-alert-container').html(`
                    <div class="alert alert-success" role="alert">
                        <i class="ti ti-check me-1"></i>
                        ${escapeHtml(response.message || 'Produk berhasil ditambahkan.')}
                        Mengalihkan ke daftar produk...
                    </div>
                `);

                $form.find(':input').prop('disabled', true);

                const redirectUrl = response.redirect
                    || @json(route('products.index'));

                window.location.href = redirectUrl;
            },

            error: function (xhr) {
                if (xhr.status === 422) {
                    showValidationErrors(
                        xhr.responseJSON?.errors || {}
                    );
                    return;
                }

                const message = xhr.responseJSON?.message
                    || 'Terjadi kesalahan saat menyimpan produk.';

                $('#create-alert-container').html(`
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <i class="ti ti-alert-circle me-1"></i>
                        ${escapeHtml(message)}

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Tutup"
                        ></button>
                    </div>
                `);

                $('html, body').animate({
                    scrollTop: 0
                }, 300);
            },

            complete: function () {
                $saveButton
                    .prop('disabled', false)
                    .html(originalButtonHtml);
            }
        });
    });

    
    function formatMoneyInput(value) {
        const digits = String(value ?? '').replace(/\D/g, '');

        if (digits === '') {
            return '';
        }

        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    $(document).on('input', '.money-input', function () {
        const formatted = formatMoneyInput($(this).val());

        $(this).val(formatted);
    });

});
</script>
@endsection
