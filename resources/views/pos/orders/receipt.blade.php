<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $order->order_number }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 16px;
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
        }

        .receipt {
            width: 58mm;
            margin: 0 auto;
        }

        .receipt.width-80 {
            width: 80mm;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin: 4px 0;
        }

        .item-name {
            margin-bottom: 3px;
            overflow-wrap: anywhere;
        }

        .item-detail {
            display: flex;
            justify-content: space-between;
            gap: 6px;
        }

        .total {
            font-size: 14px;
            font-weight: bold;
        }

        .actions {
            text-align: center;
            margin-bottom: 16px;
        }

        button, select {
            padding: 8px 12px;
            margin: 3px;
        }

        @media print {
            @page {
                margin: 2mm;
            }

            body {
                padding: 0;
                font-size: 11px;
            }

            .receipt {
                width: 100%;
                margin: 0;
            }

            .actions {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="actions">
    <label for="paperWidth">Ukuran kertas:</label>

    <select id="paperWidth">
        <option value="58">58 mm</option>
        <option value="80">80 mm</option>
    </select>

    <button type="button" onclick="window.print()">
        Cetak Struk
    </button>
</div>

<div class="receipt" id="receipt">

    <div class="center">
        <h3 style="margin: 0 0 5px;">POSCAFE</h3>
        <div>Terima kasih telah berkunjung</div>
    </div>

    <div class="line"></div>

    <div>No: {{ $order->order_number }}</div>
    <div>Bayar: {{ $order->payment->payment_number }}</div>
    <div>
        Tanggal:
        {{ $order->payment->paid_at->format('d/m/Y H:i') }}
    </div>
    <div>
        Tipe:
        {{ $order->order_type === 'dine_in' ? 'Makan di tempat' : 'Bawa pulang' }}
    </div>

    @if($order->meja)
        <div>Meja: {{ $order->meja->nomor }}</div>
    @endif

    <div class="line"></div>

    @foreach($order->items as $item)
        <div class="item-name bold">
            {{ $item->product_name }}
            @if($item->variant_name)
                - {{ $item->variant_name }}
            @endif
        </div>

        @if(is_array($item->selected_options))
            @foreach($item->selected_options as $option)
                <div>{{ $option['name'] ?? '' }}</div>
            @endforeach
        @endif

        @if($item->notes)
            <div>Catatan: {{ $item->notes }}</div>
        @endif

        <div class="item-detail">
            <span>
                {{ $item->quantity }} x
                {{ number_format($item->unit_price, 0, ',', '.') }}
            </span>
            <span>
                {{ number_format($item->subtotal, 0, ',', '.') }}
            </span>
        </div>

        <div style="margin-bottom: 8px;"></div>
    @endforeach

    <div class="line"></div>

    <div class="row">
        <span>Subtotal</span>
        <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
    </div>

    @if($order->discount > 0)
        <div class="row">
            <span>Diskon</span>
            <span>- Rp {{ number_format($order->discount, 0, ',', '.') }}</span>
        </div>
    @endif

    @if($order->tax > 0)
        <div class="row">
            <span>Pajak</span>
            <span>Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
        </div>
    @endif

    <div class="line"></div>

    <div class="row total">
        <span>TOTAL</span>
        <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
    </div>

    <div class="line"></div>

    <div class="row">
        <span>Metode</span>
        <span>{{ strtoupper($order->payment->payment_method) }}</span>
    </div>

    <div class="row">
        <span>Dibayar</span>
        <span>
            Rp {{ number_format($order->payment->amount_received, 0, ',', '.') }}
        </span>
    </div>

    @if($order->payment->payment_method === 'cash')
        <div class="row">
            <span>Kembalian</span>
            <span>
                Rp {{ number_format($order->payment->change_amount, 0, ',', '.') }}
            </span>
        </div>
    @endif

    <div class="line"></div>

    <div class="center">
        <p>Terima kasih 🙏</p>
        <p>Selamat menikmati!</p>
    </div>

</div>

<script>
    const paperWidth = document.getElementById('paperWidth');
    const receipt = document.getElementById('receipt');

    paperWidth.addEventListener('change', function () {
        receipt.classList.toggle(
            'width-80',
            this.value === '80'
        );
    });
</script>

</body>
</html>
