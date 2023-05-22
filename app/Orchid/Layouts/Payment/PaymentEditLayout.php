<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Payment;

use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\DateTimer;
use Carbon\Carbon;

use App\Models\Account;
use App\Models\Category;


class PaymentEditLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Select::make('payment.account_id')
                ->fromModel(Account::class, 'name')
                ->empty()
                ->required()
                ->title(__('Account')),
            
            Select::make('payment.category_id')
                ->fromModel(Category::where([
                    ['categories.user_id', $this->query['user']->id],
                    ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')]
                    ])
                ->selectRaw('categories.id, if(categories.category_id is null, categories.name, concat(categories.name, " (", b.name, ")")) as title, categories.category_id as category_father')
                ->leftJoin('categories as b', 'b.id', 'categories.category_id')
                ->orderBy('categories.name'), 'title')
                ->empty()
                ->required()
                ->title(__('Category')),

            Input::make('payment.amount')
                ->type('number')
                ->value(0)
                ->step(0.01)
                ->required()
                ->title(__('Amount')),

            TextArea::make('payment.description')
                ->type('text')
                ->max(255)
                ->title(__('Description'))
                ->placeholder(__('Description')),

            DateTimer::make('payment.start_date')
                ->allowInput()
                ->format('Y-m-d')
                ->required()
                ->title(__('Start Date')),

            DateTimer::make('payment.end_date')
                ->allowInput()
                ->format('Y-m-d')
                ->title(__('End Date')),

            Input::make('payment.specific_day')
                ->type('number')
                ->value(1)
                ->min(1)
                ->max(31)
                ->required()
                ->title(__('Choose specific day')),
        ];
    }
}
