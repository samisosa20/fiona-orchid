<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Budget;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

use App\Models\Budget;

use App\Orchid\Layouts\Budget\BudgetEditLayout;
use App\Orchid\Layouts\Budget\BudgetListLayout;

class BudgetEditScreen extends Screen
{
    /**
     * @var Budget
     */
    public $budget;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @param $year
     * @param Request $request
     *
     * @return array
     */
    public function query($year, Request $request): iterable
    {
        $budgets = array();
        $budgets = Budget::where([
            ['user_id', $request->user()->id],
            ['year', $year]
        ])
        ->paginate();

        return [
            'year' => $year,
            'budgets' => $budgets,
            'user' => $request->user()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Create Budget';
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

            Layout::block(BudgetEditLayout::class),
            BudgetListLayout::class,
            Layout::modal('asyncEditBudgetModal', BudgetEditLayout::class)
            ->async('asyncGetBudget'),
        ];
    }

    /**
     * @param $year
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save($year, Request $request)
    {
        Budget::create(array_merge($request->collect('budget')->toArray(), ['user_id' => $request->user()->id]));

        Toast::info(__('Budget was saved.'));

    }

    /**
     * @param Budget $budget
     *
     * @return array
     */
    public function asyncGetBudget(Budget $budget, Request $request): iterable
    {
        return [
            'budget' => $budget,
            'user' => $request->user()
        ];
    }

    /**
     * @param Request $request
     * @param Budget    $budget
     */
    public function editBudget(Request $request, $year, $name_funciton,Budget $budget): void
    {
        $budget->fill($request->input('budget'))->save();

        Toast::info(__('Budget was saved.'));
    }

    /**
     * @param Budget $budget
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Budget $budget)
    {
        $budget->delete();

        Toast::info(__('Budget was removed'));

        return redirect()->route('platform.budgets');
    }

}
