<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Movement;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

use App\Models\Movement;

use App\Orchid\Layouts\Movement\MovementTypeLayout;
use App\Orchid\Layouts\Movement\MovementEditLayout;

class MovementEditScreen extends Screen
{
    /**
     * @var Movement
     */
    public $movement;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @param Movement $movement
     *
     * @return array
     */
    public function query(Movement $movement, Request $request): iterable
    {
        return [
            'movement' => $movement,
            'user' => $request->user(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->movement->exists ? 'Edit Movement' : 'Create Movement';
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
                ->confirm(__('Once the Movement is deleted, all of its resources and data will be permanently deleted. Before deleting your Movement, please download any data or information that you wish to retain.'))
                ->method('remove')
                ->canSee($this->movement->exists),

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

            Layout::block(MovementTypeLayout::class)
            ->title(__('Select Type')),
            Layout::block(Layout::view('layouts.movement.form'))

        ];
    }

    /**
     * @param Movement    $movement
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Movement $movement, Request $request)
    {
        $movement->fill($request->collect('movement')->toArray())
            ->fill(['user_id' => $request->user()->id])
            ->save();

        Toast::info(__('Movement was saved.'));

        return redirect()->back();
    }

    /**
     * @param Movement $user
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Movement $movement)
    {
        $movement->delete();

        Toast::info(__('Movement was removed'));

        return redirect()->route('platform.index');
    }

}
