<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Account;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

use App\Orchid\Layouts\Account\AccountListLayout;

use App\Models\Account;

class AccountListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $balance = \DB::select('select * from (SELECT @user_id := '.$request->user()->id.' i) alias, general_month_year');
        $total_balance = \DB::select('select * from (SELECT @user_id := '.$request->user()->id.' i) alias, general_balance');
        
        foreach ($total_balance as &$value) {
            $month = array_values(array_filter($balance, fn ($v) => $v->type === 'month' && $v->currency === $value->currency));
            if(count($month) > 0) {
                $value->month = $month[0]->balance;
            }
            $year = array_values(array_filter($balance, fn ($v) => $v->type === 'year' && $v->currency === $value->currency));
            if(count($year) > 0) {
                $value->year = $year[0]->balance;
            }
        }

        return [
            'accounts' => Account::withTrashed()
            ->where([
                ['user_id', $request->user()->id]
            ])
            ->withBalance()
            ->with('currency')
            ->paginate(),
            'balances' => $total_balance
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Accounts';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Register all your accounts';
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
                ->route('platform.accounts.create'),
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
            Layout::view('layouts.account.balance'),
            AccountListLayout::class,
        ];
    }



    /**
     * @param Request $request
     */
    public function activate(Request $request): void
    {
        Account::onlyTrashed()->find($request->get('id'))->restore();

        Toast::success(__('The account was activated.'));
    }
}
