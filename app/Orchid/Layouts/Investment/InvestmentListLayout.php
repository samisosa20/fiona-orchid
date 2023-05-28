<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Investment;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Actions\ModalToggle;

use App\Models\Investment;

class InvestmentListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'investments';

    /**
     * @return string
     */
    protected function textNotFound(): string
    {
        return __('You currently have no Investments created');
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
                ->render(fn (Investment $event) => DropDown::make()
                    ->icon('options-vertical')
                    ->list([
                        Link::make(__('Edit'))
                            ->route('platform.investments.edit', $event->id)
                            ->icon('pencil'),
                        ModalToggle::make(__('Movements'))
                            ->icon('directions')
                            ->modal('movementsModal')
                            ->asyncParameters([
                                'event_id' => $event->id,
                            ]),
                    ])),

            TD::make('name', __('Name'))
                ->sort()
                ->cantHide()
                ->render(fn (Investment $investment) => ModalToggle::make($investment->name)
                ->modal('movementsModal')
                ->asyncParameters([
                    'investment_id' => $investment->id,
                ])),

            TD::make('init_amount', __('Start Amount'))
                ->sort()
                ->cantHide()
                ->render(fn (Investment $investment) => number_format($investment->init_amount, 2, ',', '.')),

            TD::make('end_amount', __('Current Amount'))
                ->sort()
                ->cantHide()
                ->render(fn (Investment $investment) => number_format($investment->end_amount, 2, ',', '.')),
            
            TD::make('valuation', __('Investment Valuation'))
                ->sort()
                ->cantHide()
                ->render(fn (Investment $investment) => round(($investment->end_amount - $investment->init_amount) / $investment->init_amount * 100, 2).'%'),
            
            TD::make('profit', __('Profits'))
                ->sort()
                ->cantHide()
                ->render(fn (Investment $investment) => number_format($investment->balance, 2, ',', '.')),

            TD::make('badge_id', __('Currency'))
                ->sort()
                ->cantHide()
                ->render(fn (Investment $investment) => $investment->currency->code),          
            
            TD::make('date_investment', __('Date'))
                ->sort()
                ->cantHide()
                ->render(fn (Investment $investment) => $investment->date_investment),          

        ];
    }
}
