<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class StockMovementService
{
    /**
     * Menambahkan stok produk.
     *
     * Contoh:
     * - Purchase
     * - Delete Sale / restore stock
     *
     * Catatan:
     * Transaction dikendalikan oleh caller.
     */
    public function in(
        Product $product,
        int $qty,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $note = null,
        ?int $userId = null
    ): StockMovement {

        // ======================================================
        // VALIDASI QTY
        // ======================================================

        if ($qty <= 0) {

            throw new RuntimeException(
                'Qty stock masuk harus lebih dari 0.'
            );

        }


        // ======================================================
        // LOCK PRODUCT
        // ======================================================

        $lockedProduct = Product::lockForUpdate()
            ->find($product->id);


        if (!$lockedProduct) {

            throw new RuntimeException(
                'Produk tidak ditemukan.'
            );

        }


        // ======================================================
        // STOCK BEFORE
        // ======================================================

        $stockBefore =
            (int) $lockedProduct->stock;


        // ======================================================
        // STOCK AFTER
        // ======================================================

        $stockAfter =
            $stockBefore + $qty;


        // ======================================================
        // UPDATE PRODUCT STOCK
        // ======================================================

        $lockedProduct->stock =
            $stockAfter;

        $lockedProduct->save();


        // ======================================================
        // CREATE STOCK MOVEMENT
        // ======================================================

        return StockMovement::create([

            'product_id' =>
                $lockedProduct->id,

            'type' =>
                'in',

            'qty' =>
                $qty,

            'stock_before' =>
                $stockBefore,

            'stock_after' =>
                $stockAfter,

            'reference_type' =>
                $referenceType,

            'reference_id' =>
                $referenceId,

            'note' =>
                $note,

            'user_id' =>
                $userId ?? Auth::id(),

        ]);
    }


    /**
     * Mengurangi stok produk.
     *
     * Contoh:
     * - Sale
     * - Delete Purchase
     *
     * Catatan:
     * Transaction dikendalikan oleh caller.
     */
    public function out(
        Product $product,
        int $qty,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $note = null,
        ?int $userId = null
    ): StockMovement {

        // ======================================================
        // VALIDASI QTY
        // ======================================================

        if ($qty <= 0) {

            throw new RuntimeException(
                'Qty stock keluar harus lebih dari 0.'
            );

        }


        // ======================================================
        // LOCK PRODUCT
        // ======================================================

        $lockedProduct = Product::lockForUpdate()
            ->find($product->id);


        if (!$lockedProduct) {

            throw new RuntimeException(
                'Produk tidak ditemukan.'
            );

        }


        // ======================================================
        // STOCK BEFORE
        // ======================================================

        $stockBefore =
            (int) $lockedProduct->stock;


        // ======================================================
        // CHECK STOCK
        // ======================================================

        if ($stockBefore < $qty) {

            throw new RuntimeException(
                "Stok produk {$lockedProduct->name} tidak mencukupi. " .
                "Stok tersedia: {$stockBefore}, " .
                "dibutuhkan: {$qty}."
            );

        }


        // ======================================================
        // STOCK AFTER
        // ======================================================

        $stockAfter =
            $stockBefore - $qty;


        // ======================================================
        // UPDATE PRODUCT STOCK
        // ======================================================

        $lockedProduct->stock =
            $stockAfter;

        $lockedProduct->save();


        // ======================================================
        // CREATE STOCK MOVEMENT
        // ======================================================

        return StockMovement::create([

            'product_id' =>
                $lockedProduct->id,

            'type' =>
                'out',

            'qty' =>
                $qty,

            'stock_before' =>
                $stockBefore,

            'stock_after' =>
                $stockAfter,

            'reference_type' =>
                $referenceType,

            'reference_id' =>
                $referenceId,

            'note' =>
                $note,

            'user_id' =>
                $userId ?? Auth::id(),

        ]);
    }
}