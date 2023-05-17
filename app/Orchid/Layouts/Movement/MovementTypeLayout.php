<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Movement;

use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Select;

use App\Models\Currency;

use App\Controllers\Types\CommonTypesController;

class MovementTypeLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Select::make('movement.type')
                ->options([
                    'Movement',
                    'Transfer'
                ])
                ->required()
                ->title(__('Type Movement')),

            Input::make('movement.amount')
                ->type('number')
                ->value(0)
                ->step(0.01)
                ->required()
                ->title(__('Amount')),

            DateTimer::make('movement.date_purchase')
                ->allowInput()
                ->format('Y-m-d H:m')
                ->format24hr()
                ->required()
                ->value(now())
                ->title(__('Date purchase')),
        ];
    }
}