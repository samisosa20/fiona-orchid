<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Heritage;

use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

use App\Models\Heritage;

class HeritageYearListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'heritages';

    /**
     * @return string
     */
    protected function textNotFound(): string
    {
        return __('You currently have no Heritages created');
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
                ->render(fn (Heritage $heritage) => DropDown::make()
                    ->icon('options-vertical')
                    ->list([
                        Link::make(__('Edit'))
                            ->route('platform.heritages.edit', $heritage->id)
                            ->icon('pencil'),
                    ])),

            TD::make('name', __('Name'))
                ->sort()
                ->cantHide()
                ->render(fn (Heritage $heritage) => $heritage->name),

            TD::make('year', __('Comercial Amount'))
                ->sort()
                ->cantHide()
                ->render(fn (Heritage $heritage) => number_format($heritage->comercial_amount, 2, ',', '.')),

            TD::make('year', __('Legal Amount'))
                ->sort()
                ->cantHide()
                ->render(fn (Heritage $heritage) => number_format($heritage->legal_amount, 2, ',', '.')),
            
            TD::make('currency', __('Currency'))
                ->sort()
                ->cantHide()
                ->render(fn (Heritage $heritage) => $heritage->currency->code),
        ];
    }
}
