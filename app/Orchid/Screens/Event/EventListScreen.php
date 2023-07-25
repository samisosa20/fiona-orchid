<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Event;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

use App\Orchid\Layouts\Event\EventListLayout;

use App\Models\Event;
use App\Models\Movement;

class EventListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $events = Event::where([
            ['user_id', $request->user()->id]
        ])
        ->paginate();
        
        foreach ($events as &$event) {
            $event->balance = Movement:: where([
                ['movements.event_id', $event->id],
            ])
            ->selectRaw('currencies.code as currency, badge_id, cast(ifnull(sum(amount), 0) as float) as movements')
            ->join('accounts', 'accounts.id', 'movements.account_id')
            ->join('currencies', 'currencies.id', 'accounts.badge_id')
            ->groupByRaw('currencies.code, badge_id')
            ->get();
        }


        return [
            'events' => $events,
            'movements' => []
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Events';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Register all your Events';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Add'))
                ->icon('plus')
                ->route('platform.events.create'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return string[]|\Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::view('layouts.account.list'),
            Layout::modal('movementsModal', [Layout::view('layouts.movement.list')])
                ->async('asyncGetMovements')
                ->title(__('Movements'))
                ->applyButton('Guardar')
                ->withoutApplyButton(),
        ];
    }

/**
     * @param Event $id
     *
     * @return array
     */
    public function asyncGetMovements(Event $id, Request $request): iterable
    {
        $event = Event::find($request->query('id'));

        return [
            'movements' => $event->movements,
            'events' => [],
        ];
    }

    /**
     * @param Request $request
     */
    public function activate(Request $request): void
    {
        Event::onlyTrashed()->find($request->get('id'))->restore();

        Toast::success(__('The Event was activated.'));
    }
}
