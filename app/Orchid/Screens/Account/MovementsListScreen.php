<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Account;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

use App\Orchid\Layouts\Account\MovementsListLayout;
use App\Orchid\Layouts\Account\MovementsFiltersLayout;

use App\Models\Account;
use App\Models\Movement;

use App\Controllers\Reports\ReportController;
use App\Orchid\Layouts\Reports\ChartLineLayout;
class MovementsListScreen extends Screen
{
    /**
     * @var Account
     */
    public $account;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @param Account $account
     *
     * @return array
     */
    public function query(Account $account, Request $request): iterable
    {
        $balance = ReportController::balanceByAccount($request, $account->id);

        return [
            'balancesAccount' => [
                [
                    'name'   => 'Balance',
                    'values' => array_map(fn ($v) => $v->amount, $balance),
                    'labels' => array_map(fn ($v) => $v->date, $balance),
                ]
            ],
            'account' => $account,
            'movements' => Movement::where([
                ['account_id', $account->id],
                ['user_id', $request->user()->id],
            ])
            ->filters()
            ->filter($request)
            ->with(['account', 'category', 'event', 'transferOut', 'transferIn'])
            ->orderBy('date_purchase', 'desc')
            ->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->account->name." ".$this->account->currency->code;
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return $this->account->type->name;
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Movement'))
                ->icon('plus')
                ->route('platform.movement.create'),
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
            Layout::view('layouts.account.charts'),
            MovementsFiltersLayout::class,
            MovementsListLayout::class,
        ];
    }

    /**
     * @param Request $request
     */
    public function remove(Request $request): void
    {
        Movement::findOrFail($request->get('id'))->delete();

        Toast::info(__('Movement was removed'));
    }

}
