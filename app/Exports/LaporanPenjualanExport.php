<?php

namespace App\Exports;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Enumerable;

class LaporanPenjualanExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithCustomStartCell,
    ShouldAutoSize,
    WithEvents
{
    protected Carbon $mulai;
    protected Carbon $akhir;

    public function __construct(
        string $tanggalMulai,
        string $tanggalAkhir,
        protected string $keyword = '',
        protected string $paymentMethod = ''
    ) {
        $this->mulai = Carbon::parse($tanggalMulai)->startOfDay();
        $this->akhir = Carbon::parse($tanggalAkhir)->endOfDay();
    }

    /**
     * Query dasar laporan.
     */
    protected function baseQuery(): Builder
    {
        $query = Order::query()
            ->where('status', 'paid')
            ->whereHas('payment', function ($query) {
                $query->whereBetween('paid_at', [
                    $this->mulai,
                    $this->akhir,
                ]);
            });

        if ($this->keyword !== '') {
            $query->where(
                'order_number',
                'like',
                '%' . $this->keyword . '%'
            );
        }

        if ($this->paymentMethod !== '') {
            $query->whereHas('payment', function ($query) {
                $query->where(
                    'payment_method',
                    $this->paymentMethod
                );
            });
        }

        return $query;
    }

    /**
     * Ambil semua transaksi sesuai filter,
     * tanpa batas pagination.
     */
   
    public function collection(): Enumerable
    {
        return $this->baseQuery()
            ->with('payment')
            ->orderByDesc(
                \App\Models\Payment::select('paid_at')
                    ->whereColumn('payments.order_id', 'orders.id')
                    ->limit(1)
            )
            ->get();
    }


    public function headings(): array
    {
        return [
            'No.',
            'Nomor Pesanan',
            'Tanggal Pembayaran',
            'Tipe Pesanan',
            'Metode Pembayaran',
            'Total',
        ];
    }

    public function map($order): array
    {
        static $nomor = 0;
        $nomor++;

        $metode = [
            'cash' => 'Tunai',
            'transfer' => 'Transfer',
            'qris' => 'QRIS',
        ];

        return [
            $nomor,
            $order->order_number,
            $order->payment?->paid_at?->format('d/m/Y H:i') ?? '-',
            $order->order_type === 'dine_in'
                ? 'Dine-in'
                : 'Takeaway',
            $metode[$order->payment?->payment_method ?? ''] ?? '-',
            (float) $order->total,
        ];
    }

    /**
     * Header tabel dimulai pada baris ke-10.
     */
    public function startCell(): string
    {
        return 'A13';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $summary = (clone $this->baseQuery())
                    ->selectRaw('
                        COUNT(*) as jumlah_transaksi,
                        COALESCE(SUM(total), 0) as total_omzet
                    ')
                    ->first();

                $jumlahTransaksi = (int) $summary->jumlah_transaksi;
                $totalOmzet = (float) $summary->total_omzet;
                $rataRata = $jumlahTransaksi > 0
                    ? $totalOmzet / $jumlahTransaksi
                    : 0;

                $rekap = (clone $this->baseQuery())
                    ->join(
                        'payments',
                        'payments.order_id',
                        '=',
                        'orders.id'
                    )
                    ->selectRaw('
                        payments.payment_method,
                        COUNT(orders.id) as jumlah_transaksi,
                        COALESCE(SUM(orders.total), 0) as total
                    ')
                    ->groupBy('payments.payment_method')
                    ->get()
                    ->keyBy('payment_method');

                // Judul laporan
                $sheet->setCellValue('A1', 'LAPORAN PENJUALAN POSCAFE');
                $sheet->mergeCells('A1:F1');

                $sheet->setCellValue(
                    'A2',
                    'Periode: ' .
                    $this->mulai->format('d/m/Y') .
                    ' - ' .
                    $this->akhir->format('d/m/Y')
                );
                $sheet->mergeCells('A2:F2');

                // Ringkasan
                $sheet->setCellValue('A4', 'Total Omzet');
                $sheet->setCellValue('B4', 'Jumlah Transaksi');
                $sheet->setCellValue('C4', 'Rata-rata Transaksi');

                $sheet->setCellValue('A5', $totalOmzet);
                $sheet->setCellValue('B5', $jumlahTransaksi);
                $sheet->setCellValue('C5', $rataRata);

                // Rekap pembayaran
                $sheet->setCellValue('A7', 'REKAP PEMBAYARAN');
                $sheet->setCellValue('A8', 'Metode');
                $sheet->setCellValue('B8', 'Jumlah Transaksi');
                $sheet->setCellValue('C8', 'Total Omzet');

                $metodePembayaran = [
                    'cash' => 'Tunai',
                    'transfer' => 'Transfer',
                    'qris' => 'QRIS',
                ];

                $baris = 9;

                foreach ($metodePembayaran as $kode => $label) {
                    $data = $rekap->get($kode);

                    $sheet->setCellValue('A' . $baris, $label);
                    $sheet->setCellValue(
                        'B' . $baris,
                        (int) ($data->jumlah_transaksi ?? 0)
                    );
                    $sheet->setCellValue(
                        'C' . $baris,
                        (float) ($data->total ?? 0)
                    );

                    $baris++;
                }

                // Format judul dan header
                $sheet->getStyle('A1')->getFont()
                    ->setBold(true)
                    ->setSize(16);

                $sheet->getStyle('A4:C4')->getFont()->setBold(true);
                $sheet->getStyle('A7:C8')->getFont()->setBold(true);
                $sheet->getStyle('A13:F13')->getFont()->setBold(true);

                // Format Rupiah sebagai angka dengan pemisah ribuan.
                $sheet->getStyle('A5')->getNumberFormat()
                    ->setFormatCode('#,##0');
                $sheet->getStyle('C5')->getNumberFormat()
                    ->setFormatCode('#,##0');
                $sheet->getStyle('C9:C11')->getNumberFormat()
                    ->setFormatCode('#,##0');
                $sheet->getStyle('F14:F' . max(14, $sheet->getHighestRow()))
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                $sheet->freezePane('A14');
            },
        ];
    }
}
