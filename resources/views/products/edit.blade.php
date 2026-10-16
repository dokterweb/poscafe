
@extends('layouts.app')

@section('title', 'Edit Produk')

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

                <h2 class="page-title">Edit Produk</h2>
                <div class="text-secondary mt-1">
                    Perbarui informasi produk, varian, dan kelompok pilihan.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        <div id="edit-alert-container"></div>

        <form
            id="formEditProduct"
            action="{{ route('products.update', $product->id) }}"
            method="POST"
        >
            @csrf
            @method('PUT')

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
                            <label class="form-label">Barcode</label>
                            <input
                                type="text"
                                class="form-control"
                                value="{{ $product->barcode }}"
                                readonly
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Nama Produk</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                maxlength="150"
                                value="{{ $product->name }}"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <select name="category_id" class="form-select">
                                <option value="">Pilih Kategori</option>

                                @foreach($categories as $category)
                                    <option
                                        value="{{ $category->id }}"
                                        @selected((string) $product->category_id === (string) $category->id)
                                    >
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Stok Saat Ini</label>
                            <input
                                type="number"
                                class="form-control"
                                value="{{ $product->stock }}"
                                readonly
                            >
                            <div class="form-hint">
                                Stok tidak diubah melalui halaman edit ini.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">HPP Dasar (Rp)</label>
                            <input
                                type="text"
                                name="hpp"
                                class="form-control money-input"
                                inputmode="numeric"
                                autocomplete="off"
                                value="{{ number_format((float) $product->hpp, 0, ',', '.') }}"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Harga Jual Dasar (Rp)</label>
                            <input
                                type="text"
                                name="price"
                                class="form-control money-input"
                                inputmode="numeric"
                                autocomplete="off"
                                value="{{ number_format((float) $product->price, 0, ',', '.') }}"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Status Produk</label>
                            <select name="is_active" class="form-select" required>
                                <option value="1" @selected($product->is_active)>Aktif</option>
                                <option value="0" @selected(!$product->is_active)>Nonaktif</option>
                            </select>
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
                    <div class="form-check form-switch">
                        <input type="hidden" name="track_stock" value="0">

                        <input
                            type="checkbox"
                            name="track_stock"
                            value="1"
                            id="track_stock"
                            class="form-check-input"
                            @checked($product->track_stock)
                        >

                        <label class="form-check-label fw-bold" for="track_stock">
                            Lacak stok produk
                        </label>
                    </div>

                    <div class="form-hint mt-1">
                        Pengaturan ini menentukan apakah stok produk dilacak saat penjualan.
                    </div>

                    <div
                        id="stock-disabled-info"
                        class="alert alert-info mt-3 mb-0 {{ $product->track_stock ? 'd-none' : '' }}"
                    >
                        Stok produk tidak dilacak. Nilai stok saat ini tetap dipertahankan.
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
                            Setiap varian dapat memiliki HPP dan harga jual sendiri.
                        </div>
                    </div>

                    <div class="card-actions">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary"
                            id="btn-add-variant"
                        >
                            <i class="ti ti-plus me-1"></i>
                            Tambah Varian
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        Jika varian lama tidak lagi diperlukan, menghapusnya dari form
                        akan membuatnya nonaktif melalui controller. Riwayat transaksi
                        lama tetap perlu dipertahankan.
                    </div>

                    <div id="variants-container">
                        @foreach($product->variants as $variant)
                            <div class="card card-sm mb-3 variant-row">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <strong class="variant-title">
                                            Varian {{ $loop->iteration }}
                                        </strong>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger btn-remove-variant"
                                        >
                                            <i class="ti ti-trash me-1"></i>
                                            Hapus dari produk
                                        </button>
                                    </div>

                                    <input
                                        type="hidden"
                                        name="variants[{{ $loop->index }}][id]"
                                        value="{{ $variant->id }}"
                                    >

                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label required">Nama Varian</label>
                                            <input
                                                type="text"
                                                name="variants[{{ $loop->index }}][name]"
                                                class="form-control"
                                                maxlength="100"
                                                value="{{ $variant->name }}"
                                                required
                                            >
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label required">HPP (Rp)</label>
                                            <input
                                                type="text"
                                                name="variants[{{ $loop->index }}][hpp]"
                                                class="form-control money-input"
                                                inputmode="numeric"
                                                autocomplete="off"
                                                value="{{ number_format((float) $variant->hpp, 0, ',', '.') }}"
                                                required
                                            >
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label required">Harga Jual (Rp)</label>
                                            <input
                                                type="text"
                                                name="variants[{{ $loop->index }}][price]"
                                                class="form-control money-input"
                                                inputmode="numeric"
                                                autocomplete="off"
                                                value="{{ number_format((float) $variant->price, 0, ',', '.') }}"
                                                required
                                            >
                                        </div>

                                        <div class="col-12">
                                            <input
                                                type="hidden"
                                                name="variants[{{ $loop->index }}][is_active]"
                                                value="0"
                                            >

                                            <label class="form-check form-switch">
                                                <input
                                                    type="checkbox"
                                                    name="variants[{{ $loop->index }}][is_active]"
                                                    value="1"
                                                    class="form-check-input"
                                                    @checked($variant->is_active)
                                                >
                                                <span class="form-check-label">
                                                    Varian aktif
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div
                        id="empty-variants-message"
                        class="text-center py-4 {{ $product->variants->isNotEmpty() ? 'd-none' : '' }}"
                    >
                        <i class="ti ti-layers-subtract fs-1 text-secondary"></i>
                        <div class="fw-bold mt-2">Belum ada varian</div>
                        <div class="text-secondary small">
                            Klik Tambah Varian jika produk memiliki varian.
                        </div>
                    </div>
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
                            Pilih kelompok pilihan yang tersedia untuk produk ini.
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if($optionGroups->isEmpty())
                        <div class="alert alert-warning mb-0">
                            Belum ada kelompok pilihan dengan pilihan aktif.
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
                                            class="form-selectgroup-input"
                                            @checked($product->optionGroups->contains('id', $group->id))
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
                            id="btn-update-product"
                        >
                            <i class="ti ti-device-floppy me-1"></i>
                            Simpan Perubahan
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
(function ($) {
    'use strict';

    if (!$) {
        console.error('PosCafe: jQuery belum dimuat.');
        return;
    }

    $(document).ready(function () {
        const $form = $('#formEditProduct');
        const $saveButton = $('#btn-update-product');
        let variantIndex = {{ $product->variants->count() }};

        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        function formatMoney(value) {
            const digits = String(value ?? '').replace(/\D/g, '');

            if (digits === '') {
                return '';
            }

            return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // Format harga pada input lama maupun varian yang baru ditambahkan.
        $(document).on('input', '#formEditProduct .money-input', function () {
            $(this).val(formatMoney($(this).val()));
        });

        function updateVariantState() {
            const $rows = $('#variants-container .variant-row');

            $('#empty-variants-message').toggleClass(
                'd-none',
                $rows.length > 0
            );

            $rows.each(function (index) {
                $(this).find('.variant-title').text(
                    'Varian ' + (index + 1)
                );
            });
        }

        function variantRowHtml(index) {
            return `
                <div class="card card-sm mb-3 variant-row">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <strong class="variant-title">Varian Baru</strong>

                            <button type="button"
                                    class="btn btn-sm btn-outline-danger btn-remove-variant">
                                <i class="ti ti-trash me-1"></i>
                                Hapus dari produk
                            </button>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label required">Nama Varian</label>
                                <input type="text"
                                       name="variants[${index}][name]"
                                       class="form-control"
                                       maxlength="100"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label required">HPP (Rp)</label>
                                <input type="text"
                                       name="variants[${index}][hpp]"
                                       class="form-control money-input"
                                       inputmode="numeric"
                                       value="0"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label required">Harga Jual (Rp)</label>
                                <input type="text"
                                       name="variants[${index}][price]"
                                       class="form-control money-input"
                                       inputmode="numeric"
                                       value="0"
                                       required>
                            </div>

                            <div class="col-12">
                                <input type="hidden"
                                       name="variants[${index}][is_active]"
                                       value="0">

                                <label class="form-check form-switch">
                                    <input type="checkbox"
                                           name="variants[${index}][is_active]"
                                           value="1"
                                           class="form-check-input"
                                           checked>
                                    <span class="form-check-label">Varian aktif</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        $('#btn-add-variant').on('click', function (event) {
            event.preventDefault();

            $('#variants-container').append(
                variantRowHtml(variantIndex)
            );

            variantIndex++;
            updateVariantState();
        });

        $(document).on('click', '.btn-remove-variant', function (event) {
            event.preventDefault();

            $(this).closest('.variant-row').remove();
            updateVariantState();
        });

        // Tampilkan informasi stok berdasarkan pengaturan track_stock.
        function updateStockState() {
            const trackStock = $('#track_stock').is(':checked');

            $('#stock-disabled-info').toggleClass('d-none', trackStock);
        }

        $('#track_stock').on('change', updateStockState);

        updateStockState();
        updateVariantState();

        function clearErrors() {
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('[data-error]').text('').hide();
        }

        function showValidationErrors(errors) {
            const messages = [];

            Object.keys(errors).forEach(function (field) {
                (errors[field] || []).forEach(function (message) {
                    messages.push(message);
                });

                const inputName = field.replace(
                    /^variants\.(\d+)\.(id|name|hpp|price|is_active)$/,
                    'variants[$1][$2]'
                );

                const $input = $form.find('[name]').filter(function () {
                    return this.name === inputName;
                }).first();

                if ($input.length) {
                    $input.addClass('is-invalid');
                }
            });

            const list = messages.map(function (message) {
                return '<li>' + escapeHtml(message) + '</li>';
            }).join('');

            $('#edit-alert-container').html(`
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <strong>Periksa kembali data yang diisi.</strong>
                    <ul class="mb-0 mt-2">${list}</ul>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Tutup"></button>
                </div>
            `);

            $('html, body').animate({
                scrollTop: $('#edit-alert-container').offset().top - 100
            }, 300);
        }

        // Submit AJAX. Harga 150.000 dikirim sebagai 150000.
        $form.on('submit', function (event) {
            event.preventDefault();

            clearErrors();

            if (!$form[0].checkValidity()) {
                $form[0].reportValidity();
                return;
            }

            const formData = $form.serializeArray();
            const moneyValues = {};

            $form.find('.money-input').each(function () {
                moneyValues[this.name] = $(this).val().replace(/\./g, '');
            });

            formData.forEach(function (item) {
                if (Object.prototype.hasOwnProperty.call(moneyValues, item.name)) {
                    item.value = moneyValues[item.name];
                }
            });

            const originalButton = $saveButton.html();

            $saveButton
                .prop('disabled', true)
                .html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>' +
                    'Menyimpan...'
                );

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $.param(formData),
                dataType: 'json',

                success: function (response) {
                    $('#edit-alert-container').html(`
                        <div class="alert alert-success">
                            ${escapeHtml(response.message || 'Produk berhasil diperbarui.')}
                            Mengalihkan ke daftar produk...
                        </div>
                    `);

                    window.location.href =
                        response.redirect || @json(route('products.index'));
                },

                error: function (xhr) {
                    if (xhr.status === 422) {
                        showValidationErrors(
                            xhr.responseJSON?.errors || {}
                        );
                        return;
                    }

                    $('#edit-alert-container').html(`
                        <div class="alert alert-danger">
                            ${escapeHtml(
                                xhr.responseJSON?.message ||
                                'Gagal memperbarui produk. Periksa laravel.log.'
                            )}
                        </div>
                    `);

                    console.error('Update produk gagal:', xhr.responseText);
                },

                complete: function () {
                    $saveButton
                        .prop('disabled', false)
                        .html(originalButton);
                }
            });
        });
    });
})(window.jQuery);
</script>
@endsection