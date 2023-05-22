<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Report;


use Orchid\Screen\Action;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Illuminate\Http\Request;


use App\Orchid\Layouts\Reports\ChartLineLayout;
use App\Orchid\Layouts\Reports\ChartPieLayout;
use App\Orchid\Layouts\Reports\ReportFiltersLayout;

use App\Controllers\Reports\ReportController;

class ReportScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $data = ReportController::report($request);

        return [
            'incomes' => [
                [
                    'name'   => 'Incomes',
                    'values' => array_map(fn ($v) => $v['amount'], $data['incomes']->toArray()),
                    'labels' => array_map(fn ($v) => $v['category'], $data['incomes']->toArray()),
                ]
            ],
            'expensives' => [
                [
                    'name'   => 'Expensives',
                    'values' => array_map(fn ($v) => is_array($v) ? $v['amount'] : $v->amount, $data['main_expensive']->toArray()),
                    'labels' => array_map(fn ($v) => is_array($v) ? $v['category'] : $v->category, $data['main_expensive']->toArray()),
                ]
            ],
            'balances' => [
                [
                    'name'   => 'Expensives',
                    'values' => array_map(fn ($v) => $v->amount, $data['balances']),
                    'labels' => array_map(fn ($v) => $v->date, $data['balances']),
                ]
            ],
            'group_expensive' => $data['group_expensive'],
            'list_expensives' => $data['list_expensives'],
            'credit_carts' => $data['credit_carts'],
            'metrics' => [
                'open_balance'    => number_format($data['open_close']->open_balance, 2, ',', '.'),
                'income' => number_format($data['open_close']->income, 2, ',', '.'),
                'expensive'   => number_format($data['open_close']->expensive, 2, ',', '.'),
                'utility'    => number_format($data['open_close']->utility, 2, ',', '.'),
            ],
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Dashboard';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'See your reports';
    }

    /**
     * @return iterable|null
     */
    public function permission(): ?iterable
    {
        return [];
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return string[]|\Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            ReportFiltersLayout::class,
            Layout::metrics([
                'Init Balance'    => 'metrics.open_balance',
                'Incomes' => 'metrics.income',
                'Expensives' => 'metrics.expensive',
                'End Balance' => 'metrics.utility',
            ]),
            Layout::columns([
                ChartPieLayout::make('incomes', __('Incomes')),
                ChartPieLayout::make('expensives', __('Main Expensives')),
            ]),
            ChartLineLayout::make('balances', __('Balance')),
            Layout::columns([
                Layout::view('layouts.reports.group'),
                Layout::view('layouts.reports.expensives'),
            ]),
            Layout::columns([
                Layout::view('layouts.reports.creditcard'),
            ]),
        ];
    }
}
