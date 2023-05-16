<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Account;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;
use NumberFormatter;

use App\Models\Account;

class AccountListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'accounts';

    /**
     * @return string
     */
    protected function textNotFound(): string
    {
        return __('You currently have no accounts created');
    }

    /**
     * @return TD[]
     */
    public function columns(): array
    {
        return [
            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (Account $account) => DropDown::make()
                    ->icon('options-vertical')
                    ->list([
                        Link::make(__('Edit'))
                            ->route('platform.accounts.edit', $account->id)
                            ->icon('pencil')
                            ->canSee(!$account->deleted_at),
                        Button::make(__('Active'))
                            ->icon('check')
                            ->confirm(__('The account will be reactivated.'))
                            ->method('activate',[
                                'id' => $account->id,
                            ])
                            ->canSee(!!$account->deleted_at),
                    ])),

            TD::make('name', __('Name'))
                ->sort()
                ->cantHide()
                ->render(fn (Account $account) => $account->name),

            TD::make('description', __('Description'))
                ->sort()
                ->cantHide()
                ->render(fn (Account $account) => $account->description),

            TD::make('badge_id', __('Currency'))
                ->sort()
                ->cantHide()
                ->render(fn (Account $account) => $account->currency->code),
            
            TD::make('init_amount', __('Initial Amount'))
                ->sort()
                ->cantHide()
                ->render(fn (Account $account) => number_format($account->init_amount, 2, ',', '.')),
            
            TD::make('type', __('Type'))
                ->cantHide()
                ->sort()
                ->render(fn (Account $account) => $account->type),
                
            TD::make('balance', __('Balance'))
                ->sort()
                ->cantHide()
                ->render(fn (Account $account) => number_format($account->balance ?? 0, 2, ',', '.')),
            
            TD::make('status', __('Estado'))
                ->sort()
                ->cantHide()
                ->render(fn (Account $account) =>  $account->deleted_at ? '<p class="text-danger m-0">'.__('Inactive').'</p>' : '<p class="text-success m-0">'.__('Active').'</p>'),

        ];
    }
}
