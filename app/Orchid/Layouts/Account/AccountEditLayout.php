<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Account;

use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Select;

use App\Models\Currency;
use App\Models\TypeAccount;

class AccountEditLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('account.name')
                ->type('text')
                ->max(100)
                ->required()
                ->title(__('Name'))
                ->placeholder(__('Name')),
            
            TextArea::make('account.description')
                ->type('text')
                ->max(255)
                ->title(__('Description'))
                ->placeholder(__('Description')),
                
            Select::make('account.type_id')
                ->fromModel(TypeAccount::class, 'name')
                ->empty()
                ->required()
                ->title(__('Type')),

            Select::make('account.badge_id')
                ->fromModel(Currency::class, 'code')
                ->empty()
                ->required()
                ->title(__('Currency')),

            Input::make('account.init_amount')
                ->type('number')
                ->min(0)
                ->value(0)
                ->step(0.01)
                ->required()
                ->title(__('Current Amount')),
            
            Input::make('account.limit')
                ->type('number')
                ->min(0)
                ->value(0)
                ->step(0.01)
                ->title(__('Credit card limit')),
        ];
    }
}
