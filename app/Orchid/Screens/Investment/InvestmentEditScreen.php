<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Investment;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

use App\Models\Investment;

use App\Orchid\Layouts\Investment\InvestmentEditLayout;

class InvestmentEditScreen extends Screen
{
    /**
     * @var Investment
     */
    public $investment;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @param Investment $investment
     *
     * @return array
     */
    public function query(Investment $investment): iterable
    {
        return [
            'investment' => $investment,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->investment->exist ? 'Edit Investment' : 'Create Investment';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return '';
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
                ->confirm(__('Once the Investment is deleted, all of its resources and data will be permanently deleted. Before deleting your investment, please download any data or information that you wish to retain.'))
                ->method('remove')
                ->canSee(!!$this->investment->id),

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

            Layout::block(InvestmentEditLayout::class)

        ];
    }

    /**
     * @param Investment    $investment
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Investment $investment, Request $request)
    {
        $investment->fill($request->collect('investment')->toArray())
            ->fill(['user_id' => $request->user()->id])
            ->save();

        Toast::info(__('Investment was saved.'));

        return redirect()->route('platform.investments');
    }

    /**
     * @param Investment $user
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Investment $investment)
    {
        $investment->delete();

        Toast::info(__('Investment was removed'));

        return redirect()->route('platform.investments');
    }

}
