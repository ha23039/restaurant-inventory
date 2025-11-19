<?php

namespace App\Exports;

use App\Models\CashFlow;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class CashFlowExport implements FromView, ShouldAutoSize, WithTitle, WithEvents
{
    protected array $filters;

    protected string $dateFrom;

    protected string $dateTo;

    public function __construct(array $filters = [], string $dateFrom = '', string $dateTo = '')
    {
        $this->filters = $filters;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function view(): View
    {
        $query = CashFlow::with(['user', 'sale']);

        // Apply filters
        if (! empty($this->filters['date_from']) && ! empty($this->filters['date_to'])) {
            $query->byDateRange($this->filters['date_from'], $this->filters['date_to']);
        }

        if (! empty($this->filters['category'])) {
            $query->byCategory($this->filters['category']);
        }

        if (! empty($this->filters['type'])) {
            $query->byType($this->filters['type']);
        }

        if (! empty($this->filters['search'])) {
            $query->search($this->filters['search']);
        }

        if (! empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }

        if (! empty($this->filters['amount_min'])) {
            $query->where('amount', '>=', $this->filters['amount_min']);
        }

        if (! empty($this->filters['amount_max'])) {
            $query->where('amount', '<=', $this->filters['amount_max']);
        }

        $transactions = $query->orderBy('flow_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('exports.cash-flow', [
            'transactions' => $transactions,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
        ]);
    }

    public function title(): string
    {
        return 'Flujo de Efectivo';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Style header row
                $event->sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '3B82F6'],
                    ],
                ]);

                // Auto-filter
                $event->sheet->setAutoFilter('A1:H1');
            },
        ];
    }
}
