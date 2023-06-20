<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Heritage;

use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

use App\Models\Heritage;

class HeritageListLayout extends Table
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
                        Link::make(__('View'))
                            ->route('platform.heritages.year', $heritage->year)
                            ->icon('eye'),
                    ])),

            TD::make('year', __('Year'))
                ->sort()
                ->cantHide()
                ->render(fn (Heritage $heritage) => $heritage->year),
            
            TD::make('balance', __('Balance'))
                ->cantHide()
                ->render(function (Heritage $heritage){
                    return "<div class='d-flex'>".implode(", ", array_map(function ($v) {
                        $color = $v["amount"] > 0 ? 'success' : 'danger'; 
                        return "<p class='text-$color m-0'>".number_format($v["amount"], 2, ',', '.'). " " . $v["currency"]."</p>";
                    }, $heritage->balance->toArray()))."</div>";
                }),
                
        ];
    }
}
