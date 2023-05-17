<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Event;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Actions\ModalToggle;

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
                            ->route('platform.events.edit', $event->id)
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
                ->render(fn (Event $event) => ModalToggle::make($event->name)
                ->modal('movementsModal')
                ->asyncParameters([
                    'event_id' => $event->id,
                ])),

            TD::make('balance', __('Balance'))
                ->cantHide()
                ->render(function (Event $event){
                    return implode(", ", array_map(function ($v) {
                        $color = $v["movements"] > 0 ? 'success' : 'danger'; 
                        return "<p class='text-$color m-0'>".number_format($v["movements"], 2, ',', '.'). " " . $v["currency"]."</p>";
                    }, $event->balance->toArray()));
                }),            

        ];
    }
}
