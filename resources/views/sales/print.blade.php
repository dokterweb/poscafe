<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>
        {{ $sale->invoice }}
    </title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 20px;

            background: #eee;

            font-family: "Courier New", monospace;

            font-size: 14px;
        }

        .print-container {

            width: 210mm;

            min-height: 120mm;

            margin: 0 auto;

            padding: 18px 24px;

            background: #fff;

            box-shadow: 0 0 8px rgba(0,0,0,.15);

        }


        /* HEADER */

        .header {
            display: flex;

            justify-content: space-between;

            margin-bottom: 15px;
        }

        .header-left {
            padding-top: 20px;
        }

        .header-right {
            text-align: left;
        }


        /* CUSTOMER */

        .customer {
            margin-top: 5px;
        }


        /* TABLE */

        table {
            width: 100%;

            border-collapse: collapse;

            margin-top: 10px;
        }

        th,
        td {

            border: 1px solid #000;

            padding: 6px 8px;

        }

        th {
            text-align: center;
        }

        td.number {
            text-align: right;
        }

        td.center {
            text-align: center;
        }


        /* TOTAL */

        .total-row td {
            font-weight: bold;
        }


        /* TERBILANG */

        .terbilang {

            margin-top: 12px;

            padding-left: 8px;

        }


        /* SIGNATURE */

        .signature {

            display: flex;

            justify-content: space-between;

            margin-top: 25px;

            text-align: center;

        }

        .signature-box {

            width: 180px;

        }

        .signature-space {

            height: 60px;

        }


        /* PRINT */

        @media print {

            @page {

                size: A4 portrait;

                margin: 8mm;

            }

            body {

                padding: 0;

                background: #fff;

            }

            .print-container {

                width: 100%;

                min-height: auto;

                padding: 0;

                box-shadow: none;

            }

        }

    </style>

</head>


<body>


<div class="print-container">


    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div class="header">

        <div class="header-left">

            Toko Obat Maju

        </div>


        <div class="header-right">

            {{ $sale->date->format('d/m/Y') }}
            {{ now()->format('H:i') }}

            <br><br>

            Kepada Yth.

            <br>

            {{ $sale->customer?->name ?? 'Umum' }}

        </div>

    </div>


    {{-- =====================================================
         INVOICE
    ====================================================== --}}

    <div>

        No. Faktur :
        {{ $sale->invoice }}

    </div>


    {{-- =====================================================
         DETAIL
    ====================================================== --}}

    <table>

        <thead>

            <tr>

                <th style="width: 40px;">
                    No
                </th>

                <th>
                    Nama Barang
                </th>

                <th style="width: 70px;">
                    Pcs
                </th>

                <th style="width: 100px;">
                    Harga
                </th>

                <th style="width: 100px;">
                    Discount
                </th>

                <th style="width: 120px;">
                    Subtotal
                </th>

            </tr>

        </thead>


        <tbody>

            @foreach ($sale->details as $detail)

                <tr>

                    <td class="center">
                        {{ $loop->iteration }}
                    </td>

                    <td>
                        {{ $detail->product?->name ?? '-' }}
                    </td>

                    <td class="number">
                        {{ number_format($detail->qty, 0, ',', '.') }}
                    </td>

                    <td class="number">
                        {{ number_format($detail->price, 0, ',', '.') }}
                    </td>

                    <td class="number">
                        {{ number_format($detail->discount, 2, ',', '.') }}%
                    </td>

                    <td class="number">
                        {{ number_format($detail->total, 0, ',', '.') }}
                    </td>

                </tr>

            @endforeach


            {{-- TOTAL --}}

            <tr class="total-row">

                <td colspan="5" style="text-align:right;">
                    Total
                </td>

                <td class="number">

                    {{ number_format(
                        $sale->final_price,
                        0,
                        ',',
                        '.'
                    ) }}

                </td>

            </tr>

        </tbody>

    </table>


    {{-- =====================================================
         TERBILANG
    ====================================================== --}}

    <div class="terbilang">

        Terbilang :
        <strong>
            {{ terbilang($sale->final_price) }}
            Rupiah
        </strong>

    </div>


    {{-- =====================================================
         SIGNATURE
    ====================================================== --}}

    <div class="signature">

        <div class="signature-box">

            Hormat Kami

            <div class="signature-space"></div>

            ____________________

        </div>


        <div class="signature-box">

            Penerima

            <div class="signature-space"></div>

            ____________________

        </div>

    </div>


</div>


<script>

    window.onload = function () {

        window.print();

    };

</script>


</body>

</html>