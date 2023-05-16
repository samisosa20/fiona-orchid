<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Event;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

use App\Models\Event;

use App\Orchid\Layouts\Event\EventEditLayout;

class EventEditScreen extends Screen
{
    /**
     * @var Event
     */
    public $event;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @param Event $event
     *
     * @return array
     */
    public function query(Event $event): iterable
    {
        return [
            'event' => $event,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->event->exist ? 'Edit Event' : 'Create Event';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Details such as name, email and password';
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [

            Button::make(__('Remove'))
                ->icon('trash')
                ->confirm(__('Once the Event is deleted, all of its resources and data will be permanently deleted. Before deleting your Event, please download any data or information that you wish to retain.'))
                ->method('remove')
                ->canSee(!!$this->event->id),

            Button::make(__('Save'))
                ->icon('check')
                ->method('save'),
        ];
    }

    /**
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [

            Layout::block(EventEditLayout::class)

        ];
    }

    /**
     * @param Event    $event
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Event $event, Request $request)
    {
        $event->fill($request->collect('event')->toArray())
            ->fill(['user_id' => $request->user()->id])
            ->save();

        Toast::info(__('Event was saved.'));

        return redirect()->route('platform.events');
    }

    /**
     * @param Event $user
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Event $event)
    {
        $event->delete();

        Toast::info(__('Event was removed'));

        return redirect()->route('platform.events');
    }

}
