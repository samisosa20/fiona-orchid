<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Budget;
use App\Models\Movement;
use App\Models\Category;
use App\Models\Currency;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $budgets = Budget::where([
            ['user_id', $user->id]
        ])
        ->with(['period', 'category', 'currency'])
            ->when($request->query('year'), function ($query) use ($request) {
                $query->where('year', $request->query('year'));
            })
            ->when($request->query('badge_id'), function ($query) use ($request) {
                $query->where('badge_id', $request->query('badge_id'));
            })
            ->get();

        return response()->json($budgets);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => [
                    'required',
                ],
                'amount' => [
                    'required',
                ],
                'badge_id' => [
                    'required',
                ],
                'period_id' => [
                    'required',
                ],
                'year' => [
                    'required',
                ],
            ]);

            if ($validator->fails()) {
                return response([
                    'message' => 'data missing',
                    'detail' => $validator->errors()
                ], 400)->header('Content-Type', 'json');
            }

            $user = auth()->user();

            $budget = Budget::create(array_merge($request->input(), ['user_id' => $user->id]));

            return response()->json([
                'message' => 'Presupuesto creado exitosamente',
                'data' => $budget,
            ]);
        } catch (\Illuminate\Database\QueryException $ex) {
            return response([
                'message' =>  'Datos no guardados',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Budget  $budget
     * @return \Illuminate\Http\Response
     */
    public function show(Budget $budget)
    {
        return response()->json($budget->with(['period', 'category', 'currency'])->first());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Budget  $budget
     * @return \Illuminate\Http\Response
     */
    public function edit(Budget $budget)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Budget  $budget
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Budget $budget)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => [
                    'required',
                ],
                'amount' => [
                    'required',
                ],
                'badge_id' => [
                    'required',
                ],
                'period_id' => [
                    'required',
                ],
                'year' => [
                    'required',
                ],
            ]);

            if ($validator->fails()) {
                return response([
                    'message' => 'data missing',
                    'detail' => $validator->errors()
                ], 400)->header('Content-Type', 'json');
            }

            $budget->fill($request->input())->save();

            return response()->json([
                'message' => 'Presupuesto editado exitosamente',
                'data' => $budget,
            ]);
        } catch (\Illuminate\Database\QueryException $ex) {
            return response([
                'message' =>  'Datos no guardados',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Budget  $budget
     * @return \Illuminate\Http\Response
     */
    public function destroy(Budget $budget)
    {
        try {
            $budget->delete();
            return response()->json([
                'message' => 'Presupuesto eliminado exitosamente',
                'data' => $budget,
            ]);
        } catch (\Illuminate\Database\QueryException $ex) {
            return response([
                'message' =>  'Datos no guardados',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }

    /**
     * Display a listing of budget per year.
     *
     * @return \Illuminate\Http\Response
     */
    public function listYear()
    {
        $currencies = Budget::select('currencies.code as currency')
            ->distinct()
            ->where([
                ['user_id', auth()->user()->id],
            ])
            ->join('currencies', 'budgets.badge_id', '=', 'currencies.id')
            ->get();

        foreach ($currencies as &$currency) {
            $currency->years = Budget::where([
                ['user_id', auth()->user()->id],
                ['currencies.code', $currency->currency],
            ])
                ->select('year')
                ->distinct()
                ->join('currencies', 'budgets.badge_id', '=', 'currencies.id')
                ->get();
            foreach ($currency->years as &$year) {
                $year->incomes = (float)Budget::where([
                    ['user_id', auth()->user()->id],
                    ['year', $year->year],
                ])
                    ->whereHas('currency', function ($query) use ($currency) {
                        $query->where([
                            ['code', $currency->currency],
                        ]);
                    })
                    ->whereHas('category', function ($query) use ($currency) {
                        $query->where([
                            ['group_id', 2],
                        ]);
                    })
                    ->sum('amount');
                $year->expensives = (float)Budget::where([
                    ['user_id', auth()->user()->id],
                    ['year', $year->year],
                ])
                    ->whereHas('currency', function ($query) use ($currency) {
                        $query->where([
                            ['code', $currency->currency],
                        ]);
                    })
                    ->whereHas('category', function ($query) use ($currency) {
                        $query->where([
                            ['group_id', '<>', 2],
                        ]);
                    })
                    ->sum('amount');
            }
        }

        return response()->json($currencies);
    }

    /**
     * Display budget report
     *
     * @return \Illuminate\Http\Response
     */
    public function reportBudget(Request $request)
    {
        $categories = Category::where([
            ['user_id', auth()->user()->id],
            ['group_id', '<>', env('GROUP_TRANSFER_ID')]
        ])
            ->whereNull('category_id')
            ->get();

        foreach ($categories as &$category) {
            $sub_categories = Category::where([
                ['user_id', auth()->user()->id],
                ['category_id', $category->id]
            ])
                ->get();
            $movements_main = [];
            $budgets_main = [];

            foreach ($sub_categories as &$sub_category) {
                $budgets = Budget::where([
                    ['user_id', auth()->user()->id],
                    ['category_id', $sub_category->id],
                    ['year', $request->year],
                    ['badge_id', $request->badge_id],
                ])
                    ->with(['period'])
                    ->first();

                if ($budgets) {

                    if ($budgets->period->id === 1) {
                        $budgets->amount = $budgets->amount * 12;
                    }
                }
                $sub_category->budget = $budgets;
                array_push($budgets_main, $budgets);

                $movements = (float)Movement::where([
                    ['movements.user_id', auth()->user()->id],
                    ['category_id', $sub_category->id],
                    ['badge_id', $request->badge_id],
                ])
                    ->whereYear('date_purchase', $request->year)
                    ->join('accounts', 'accounts.id', 'account_id')
                    ->join('currencies', 'currencies.id', 'badge_id')
                    ->sum('amount');

                $sub_category->movements = $movements;
                array_push($movements_main, $movements);
            }

            $movements = (float)Movement::where([
                ['movements.user_id', auth()->user()->id],
                ['category_id', $category->id],
                ['badge_id', $request->badge_id],
            ])
                ->whereYear('date_purchase', $request->year)
                ->join('accounts', 'accounts.id', 'account_id')
                ->join('currencies', 'currencies.id', 'badge_id')
                ->sum('amount');

            $budgets = Budget::where([
                ['user_id', auth()->user()->id],
                ['category_id', $category->id],
                ['year', $request->year],
                ['badge_id', $request->badge_id],
            ])
                ->with(['period'])
                ->first();
            if ($budgets) {
                if ($budgets->period->id === 1) {
                    $budgets->amount = $budgets->amount * 12;
                }
            }

            array_push($budgets_main, $budgets);
            array_push($movements_main, $movements);

            $category->sub_categories = $sub_categories;
            $category->movements = $movements_main;
            $category->budgets = $budgets_main;
        }

        $sumsMove = 0;
        $sumsBudget = 0;

        foreach ($categories as $category) {
            foreach ($category['movements'] as $movement) {
                $sumsMove += (float)$movement;
            }
            foreach ($category['budgets'] as $budget) {
                if($budget) {
                    $value = $budget->category->group_id > 2 ? $budget->amount * -1 : $budget->amount;
    
                    $sumsBudget += $value;
                }
            }
        }

        return response()->json([
            'incomes' => array_values(array_filter($categories->toArray(), fn ($v) => $v['group_id'] == 2)),
            'expensives' => array_values(array_filter($categories->toArray(), fn ($v) => $v['group_id'] > 2)),
            'totalMovements' => round($sumsMove, 2),
            'totalBudgets' => round($sumsBudget, 2),
        ]);
    }
}
