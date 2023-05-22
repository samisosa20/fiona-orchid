<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Budget;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

use App\Orchid\Layouts\Budget\BudgetListLayout;

use App\Models\Budget;
use App\Models\Movement;

class BudgetListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $budgets = Budget::where([
            ['user_id', $request->user()->id]
        ])
        ->selectRaw('year, GROUP_CONCAT(currencies.code SEPARATOR ", ") as currency')
        ->join('currencies', 'currencies.id', 'budgets.badge_id')
        ->groupBy('year')
        ->paginate();


        return [
            'budgets' => $budgets,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Budgets';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Register all your Budgets';
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
                ->route('platform.budgets.create'),
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
            BudgetListLayout::class,
        ];
    }

}
