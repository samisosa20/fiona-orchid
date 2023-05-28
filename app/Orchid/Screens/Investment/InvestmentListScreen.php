<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Investment;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

use App\Orchid\Layouts\Investment\InvestmentListLayout;

use App\Models\Investment;
use App\Models\Movement;

class InvestmentListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $investments = Investment::where([
            ['user_id', $request->user()->id]
        ])
        ->paginate();

        $balances = Investment::where([
            ['user_id', $request->user()->id]
        ])
        ->selectRaw('currencies.code as currency, badge_id, sum(end_amount - init_amount) as valuation, sum(end_amount) as amount')
        ->join('currencies', 'currencies.id', 'investments.badge_id')
        ->groupByRaw('currencies.code, badge_id')
        ->get();

        foreach ($balances as &$value) {
            $value->profit = Movement:: where([
                ['movements.user_id', $request->user()->id],
                ['currencies.id', $value->badge_id],
                ])
                ->whereNotNull('investment_id')
                ->join('accounts', 'accounts.id', 'movements.account_id')
                ->join('currencies', 'currencies.id', 'accounts.badge_id')
                ->sum('amount') * 1;
        }
        
        foreach ($investments as &$investment) {
            $investment->balance = Movement:: where([
                ['movements.investment_id', $investment->id],
            ])
            ->sum('amount') * 1;
        }

        return [
            'investments' => $investments,
            'movements' => [],
            'balances' => $balances
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Investments';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Register all your Investments';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Add'))
                ->icon('plus')
                ->route('platform.investments.create'),
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
            Layout::view('layouts.investment.balance'),
            InvestmentListLayout::class,
            Layout::modal('movementsModal', [Layout::view('layouts.movement.list')])
                ->async('asyncGetMovements')
                ->title(__('Movements'))
                ->applyButton('Guardar')
                ->withoutApplyButton(),
        ];
    }

/**
     * @param Investment $investment
     *
     * @return array
     */
    public function asyncGetMovements(Investment $investment): iterable
    {
        return [
            'movements' => $investment->movements,
        ];
    }

    /**
     * @param Request $request
     */
    public function activate(Request $request): void
    {
        Investment::onlyTrashed()->find($request->get('id'))->restore();

        Toast::success(__('The Investment was activated.'));
    }
}
