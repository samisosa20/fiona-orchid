<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Heritage;

use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;

use App\Models\Currency;
use App\Models\Movement;

class HeritageEditLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('heritage.name')
                ->type('text')
                ->max(100)
                ->required()
                ->title(__('Name'))
                ->placeholder(__('Name')),

            Input::make('heritage.comercial_amount')
                ->type('number')
                ->min(0)
                ->value(0)
                ->step(0.01)
                ->required()
                ->title(__('Comercial Amount')),
            
            Input::make('heritage.legal_amount')
                ->type('number')
                ->min(0)
                ->value(0)
                ->step(0.01)
                ->required()
                ->title(__('Legal Amount')),

            Select::make('heritage.badge_id')
                ->fromModel(Currency::class, 'code')
                ->empty()
                ->required()
                ->title(__('Currency')),

            Select::make('heritage.year')
                ->fromModel(Movement::where([['user_id', $this->query['user']->id]])
                ->distinct('year')
                ->selectRaw('year(date_purchase) as year'), 'year', 'year')
                ->empty()
                ->required()
                ->title(__('Year')),
        ];
    }
}
