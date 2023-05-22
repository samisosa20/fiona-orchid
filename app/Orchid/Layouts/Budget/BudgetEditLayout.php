<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Budget;

use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;

use App\Models\Currency;
use App\Models\Category;
use App\Models\Period;

class BudgetEditLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('budget.year')
                ->type('number')
                ->min(now()->format('Y'))
                ->value(now()->format('Y'))
                ->required()
                ->title(__('Year')),

            Select::make('budget.category_id')
                ->fromModel(Category::where([
                    ['categories.user_id', $this->query['user']->id],
                    ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')]
                ])
                ->selectRaw('categories.id, if(categories.category_id is null, categories.name, concat(categories.name, " (", b.name, ")")) as title, categories.category_id as category_father')
                ->leftJoin('categories as b', 'b.id', 'categories.category_id')
                ->orderBy('categories.name'), 'title')
                ->empty()
                ->title(__('Category')),

            Select::make('budget.badge_id')
                ->fromModel(Currency::class, 'code')
                ->empty()
                ->required()
                ->title(__('Currency')),
                
            Input::make('budget.amount')
                ->type('number')
                ->min(0)
                ->value(0)
                ->step(0.01)
                ->required()
                ->title(__('Amount')),
            
            Select::make('budget.period_id')
                ->fromModel(Period::class, 'name')
                ->empty()
                ->required()
                ->title(__('Period')),
            ];
    }
}
