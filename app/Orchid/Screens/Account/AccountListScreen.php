<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Account;

use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

use App\Orchid\Layouts\Account\AccountFiltersLayout;
use App\Controllers\Accounts\AccountController;

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
        return [
            'accounts' => Account::where([
                ['user_id', $request->user()->id]
            ])
            ->when($request->query('status') === 'inactive', fn ($query) => $query->withTrashed())
            ->withBalance()
            ->with('currency')
            ->paginate(),
            'balances' => AccountController::totalBalance()
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
            AccountFiltersLayout::class,
            Layout::view('layouts.account.list'),
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
