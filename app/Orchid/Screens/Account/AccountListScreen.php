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
    public function query(): iterable
    {
        return [
            'accounts' => Account::withTrashed()
            ->where([
                ['user_id', 1]
            ])
            ->with('currency')
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
