<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Account;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;

use App\Models\Movement;

class MovementsListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'movements';

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
                ->render(fn (Movement $movement) => DropDown::make()
                    ->icon('options-vertical')
                    ->list([
                        Link::make(__('Edit'))
                            ->route('platform.movement.edit', $movement->id)
                            ->icon('pencil'),
                        Button::make(__('Delete'))
                            ->icon('trash')
                            ->confirm(__('The movement will be delete.'))
                            ->method('remove',[
                                'id' => $movement->id,
                            ]),
                    ])),

            TD::make('category_id', __('Category'))
                ->sort()
                ->cantHide()
                ->render(fn (Movement $movement) => Link::make($movement->category->name)
                ->route('platform.movement.edit', $movement->id)),
                
            TD::make('amount', __('Amount'))
                ->sort()
                ->cantHide()
                ->render(function (Movement $movement){
                    $color = $movement->amount > 0 ? 'success' : 'danger'; 
                    return "<p class='text-$color m-0'>".number_format($movement->amount, 2, ',', '.')."</p>";
                }),

            TD::make('description', __('Description'))
                ->sort()
                ->cantHide()
                ->render(fn (Movement $movement) => $movement->description),


            TD::make('event_id', __('Event'))
                ->sort()
                ->cantHide()
                ->render(fn (Movement $movement) => $movement->event->name ?? ''),
            
            TD::make('date_purchase', __('Date Purchase'))
                ->sort()
                ->cantHide()
                ->render(fn (Movement $movement) => $movement->date_purchase->toDateTimeString()),

        ];
    }
}
