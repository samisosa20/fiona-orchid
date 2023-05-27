<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Investment;

use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\DateTimer;

use App\Models\Currency;

class InvestmentEditLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('investment.name')
                ->type('text')
                ->max(100)
                ->required()
                ->title(__('Name'))
                ->placeholder(__('Name')),

            Input::make('investment.init_amount')
                ->type('number')
                ->min(0)
                ->value(0)
                ->step(0.01)
                ->required()
                ->title(__('Start Amount')),
            
            Input::make('investment.end_amount')
                ->type('number')
                ->min(0)
                ->value(0)
                ->step(0.01)
                ->title(__('Current Amount')),

            Select::make('investment.badge_id')
                ->fromModel(Currency::class, 'code')
                ->empty()
                ->required()
                ->title(__('Currency')),

            DateTimer::make('investment.date_investment')
                ->allowInput()
                ->format('Y-m-d')
                ->required()
                ->title(__('Date Investment')),
        ];
    }
}
