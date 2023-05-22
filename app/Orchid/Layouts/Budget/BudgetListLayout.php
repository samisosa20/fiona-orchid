<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Budget;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Actions\ModalToggle;

use App\Models\Budget;

class BudgetListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'budgets';

    /**
     * @return string
     */
    protected function textNotFound(): string
    {
        return __('You currently have no Budgets created');
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
                ->canSee(!$this->query['year'])
                ->render(fn (Budget $budget) => DropDown::make()
                    ->icon('options-vertical')
                    ->list([
                        Link::make(__('Edit'))
                            ->route('platform.budgets.edit', $budget->year)
                            ->icon('pencil'),
                    ])),

            TD::make('year', __('Year'))
                ->sort()
                ->cantHide()
                ->canSee(!$this->query['year'])
                ->render(fn (Budget $budget) => $budget->year),
            
            TD::make('category_id', __('Category'))
                ->sort()
                ->cantHide()
                ->canSee(!!$this->query['year'])
                ->render(fn (Budget $budget) => ModalToggle::make($budget->category->name)
                ->modal('asyncEditBudgetModal')
                ->modalTitle($budget->category->name . ' - ' . $this->query['year'])
                ->method('editBudget')
                ->asyncParameters([
                    'budget' => $budget->id,
                ])),
            
            TD::make('amount', __('Amount'))
                ->sort()
                ->cantHide()
                ->canSee(!!$this->query['year'])
                ->render(fn (Budget $budget) => number_format($budget->amount)),
            
            TD::make('period_id', __('Period'))
                ->sort()
                ->cantHide()
                ->canSee(!!$this->query['year'])
                ->render(fn (Budget $budget) => $budget->period->name),
    
            TD::make('badge_id', __('Currency'))
                ->sort()
                ->cantHide()
                ->render(fn (Budget $budget) => $budget->currency->code ?? $budget->currency),         

        ];
    }
}
