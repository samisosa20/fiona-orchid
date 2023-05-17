<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Heritage;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

use App\Models\Heritage;

use App\Orchid\Layouts\Heritage\HeritageEditLayout;

class HeritageEditScreen extends Screen
{
    /**
     * @var Heritage
     */
    public $heritage;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @param Heritage $heritage
     *
     * @return array
     */
    public function query(Heritage $heritage, Request $request): iterable
    {
        return [
            'user' => $request->user(),
            'heritage' => $heritage,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->heritage->id ? 'Edit Heritage' : 'Create Heritage';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return null;
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
                ->confirm(__('Once the Heritage is deleted, all of its resources and data will be permanently deleted. Before deleting your Heritage, please download any data or information that you wish to retain.'))
                ->method('remove')
                ->canSee(!!$this->heritage->id),

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

            Layout::block(HeritageEditLayout::class)

        ];
    }

    /**
     * @param Heritage    $heritage
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Heritage $heritage, Request $request)
    {
        $heritage->fill($request->collect('Heritage')->toArray())
            ->fill(['user_id' => $request->user()->id])
            ->save();

        Toast::info(__('Heritage was saved.'));

        return redirect()->route('platform.heritages');
    }

    /**
     * @param Heritage $user
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Heritage $heritage)
    {
        $heritage->delete();

        Toast::info(__('Heritage was removed'));

        return redirect()->route('platform.heritages');
    }

}
