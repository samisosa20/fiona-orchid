<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Report;


use Orchid\Screen\Action;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Illuminate\Http\Request;


use App\Orchid\Layouts\Examples\ChartBarExample;
use App\Orchid\Layouts\Examples\ChartLineExample;

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
        dd($data, $data['open_close']);
        return [
            'charts'  => [
                [
                    'name'   => 'Some Data',
                    'values' => [25, 40, 30, 35, 8, 52, 17],
                    'labels' => ['12am-3am', '3am-6am', '6am-9am', '9am-12pm', '12pm-3pm', '3pm-6pm', '6pm-9pm'],
                ],
                [
                    'name'   => 'Another Set',
                    'values' => [25, 50, -10, 15, 18, 32, 27],
                    'labels' => ['12am-3am', '3am-6am', '6am-9am', '9am-12pm', '12pm-3pm', '3pm-6pm', '6pm-9pm'],
                ],
                [
                    'name'   => 'Yet Another',
                    'values' => [15, 20, -3, -15, 58, 12, -17],
                    'labels' => ['12am-3am', '3am-6am', '6am-9am', '9am-12pm', '12pm-3pm', '3pm-6pm', '6pm-9pm'],
                ],
                [
                    'name'   => 'And Last',
                    'values' => [10, 33, -8, -3, 70, 20, -34],
                    'labels' => ['12am-3am', '3am-6am', '6am-9am', '9am-12pm', '12pm-3pm', '3pm-6pm', '6pm-9pm'],
                ],
            ],
            'metrics' => [
                'open_balance'    => number_format($data['open_close']->open_balance),
                'income' => number_format($data['open_close']->income),
                'expensive'   => number_format($data['open_close']->expensive),
                'utility'    => number_format($data['open_close']->utility),
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
        return [
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return string[]|\Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::metrics([
                'Init Balance'    => 'metrics.open_balance',
                'Incomes' => 'metrics.income',
                'Expensives' => 'metrics.expensive',
                'End Balance' => 'metrics.utility',
            ]),
            Layout::columns([
                ChartLineExample::make('charts', 'Line Chart')
                    ->description('It is simple Line Charts with different colors.'),

                ChartBarExample::make('charts', 'Bar Chart')
                    ->description('It is simple Bar Charts with different colors.'),
            ]),
        ];
    }
}
