<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Event;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;

use App\Models\Event;

class EventListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'events';

    /**
     * @return string
     */
    protected function textNotFound(): string
    {
        return __('You currently have no Events created');
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
                ->render(fn (Event $event) => DropDown::make()
                    ->icon('options-vertical')
                    ->list([
                        Link::make(__('Edit'))
                            ->route('platform.events', $event->year)
                            ->icon('eye-open'),
                    ])),

            TD::make('name', __('Name'))
                ->sort()
                ->cantHide()
                ->render(fn (Event $event) => $event->name),

                TD::make('balance', __('Balance'))
                ->cantHide()
                ->render(fn (Event $event) => implode(", ", array_map(fn ($v) => number_format($v["movements"], 2, ',', '.'). " " . $v["currency"], $event->balance->toArray()))),            

        ];
    }
}
