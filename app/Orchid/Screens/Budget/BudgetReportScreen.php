<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Budget;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;


use App\Models\Budget;
use App\Models\Movement;
use App\Models\Category;

class BudgetReportScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $categories = Category::where([
            ['user_id', $request->user()->id],
            ['group_id', '<>', env('GROUP_TRANSFER_ID')]
        ])
        ->whereNull('category_id')
        ->get();

        foreach ($categories as &$category) {
            $sub_categories = Category::where([
                ['user_id', $request->user()->id],
                ['category_id', $category->id]
                ])
                ->get();
            $movements_main = [];
            $budgets_main = [];

            foreach ($sub_categories as &$sub_category) {
                $budgets = Budget::where([
                    ['user_id', $request->user()->id],
                    ['category_id', $sub_category->id],
                    ['year', now()->format('Y')],
                    ])
                    ->get();
                    
                foreach ($budgets as &$budget) {
                    if($budget->period->name === 'Monthly') {
                        $budget->amount = $budget->amount * 12;
                    }
                }
                $sub_category->budget = $budgets;
                array_push($budgets_main, $budgets);

                $movements = Movement::where([
                    ['movements.user_id', $request->user()->id],
                    ['category_id', $sub_category->id],
                ])
                ->whereYear('date_purchase', now()->format('Y'))
                ->selectRaw('code, sum(amount) as amount')
                ->join('accounts', 'accounts.id', 'account_id')
                ->join('currencies', 'currencies.id', 'badge_id')
                ->groupBy('code')
                ->get();

                $sub_category->movements = $movements;
                array_push($movements_main, $movements);
            }
            $movements = Movement::where([
                    ['movements.user_id', $request->user()->id],
                    ['category_id', $sub_category->id],
                ])
                ->whereYear('date_purchase', now()->format('Y'))
                ->selectRaw('code, sum(amount) as amount')
                ->join('accounts', 'accounts.id', 'account_id')
                ->join('currencies', 'currencies.id', 'badge_id')
                ->groupBy('code')
                ->get();

            array_push($movements_main, $movements);

            $category->sub_categories = $sub_categories;
            $category->movements = $movements_main;
            $category->budgets = $budgets_main;
        }

        $sumsMove = [];
        $sumsBudget = [];
        
        foreach ($categories as $category) {
            foreach ($category['movements'] as $subArray) {
                foreach ($subArray as $item) {
                    $code = $item['code'];
                    $value = $item['amount'];
            
                    $sumsMove[$code] = isset($sumsMove[$code]) ? $sumsMove[$code] + $value : $value;
                }
            }
            foreach ($category['budgets'] as $subArray) {
                foreach ($subArray as $item) {
                    $code = $item->currency->code;
                    $value = $item->category->group_id > 2 ? $item->amount * -1 : $item->amount;

                    $sumsBudget[$code] = isset($sumsBudget[$code]) ? $sumsBudget[$code] + $value : $value;
                }
            }
        }
        //dd($sumsMove, $sumsBudget);

        return [
            'incomes' => array_values(array_filter($categories->toArray(), fn ($v) => $v['group_id'] == 2)),
            'expensives' => array_values(array_filter($categories->toArray(), fn ($v) => $v['group_id'] > 2)),
            'totalMovements' => $sumsMove,
            'totalBudgets' => $sumsBudget,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Budgets Reports';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'See your buget\'s report';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return string[]|\Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::view('layouts.budget.report'),
        ];
    }

}
