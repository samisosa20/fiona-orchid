<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Account;

use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

use App\Models\Account;

use App\Orchid\Layouts\Account\AccountEditLayout;

class AccountEditScreen extends Screen
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
    public function query(Account $account): iterable
    {
        return [
            'account' => $account,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->account->exists ? 'Edit Account' : 'Create Account';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Details such as name, email and password';
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [

            Button::make(__('Remove'))
                ->icon('trash')
                ->confirm(__('Once the account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.'))
                ->method('remove')
                ->canSee($this->account->exists),

            Button::make(__('Save'))
                ->icon('check')
                ->method('save'),
        ];
    }

    /**
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [

            Layout::block(AccountEditLayout::class)

        ];
    }

    /**
     * @param Account    $account
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Account $account, Request $request)
    {
        $account->fill($request->collect('account')->toArray())
            ->fill(['user_id' => $request->user()->id])
            ->save();

        Toast::info(__('Account was saved.'));

        return redirect()->route('platform.accounts');
    }

    /**
     * @param Account $user
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Account $account)
    {
        $account->delete();

        Toast::info(__('Account was removed'));

        return redirect()->route('platform.accounts');
    }

}
