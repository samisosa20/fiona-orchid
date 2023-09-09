<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
 
use App\Models\Budget;
use App\Models\Movement;
use App\Models\Category;

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
        ->when($request->query('year'), function ($query) use ($request) {
            $query->where('year', $request->query('year'));
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
        try{
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
                'month' => [
                    'required',
                ],
                'year' => [
                    'required',
                ],
            ]);

            if($validator->fails()){
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
        } catch(\Illuminate\Database\QueryException $ex){
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
        return response()->json($budget);
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
        try{
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
                'month' => [
                    'required',
                ],
                'year' => [
                    'required',
                ],
            ]);

            if($validator->fails()){
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
        } catch(\Illuminate\Database\QueryException $ex){
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
        } catch(\Illuminate\Database\QueryException $ex){
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
        $budgets =  Budget::where([
            ['user_id', auth()->user()->id]
        ])
        ->selectRaw('year, GROUP_CONCAT(distinct(currencies.code) SEPARATOR ", ") as currency')
        ->join('currencies', 'currencies.id', 'budgets.badge_id')
        ->groupBy('year')
        ->get();

        return response()->json($budgets);
    }
    
    /**
     * Display budget report
     *
     * @return \Illuminate\Http\Response
     */
    public function reportBudget()
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
                    ['movements.user_id', auth()->user()->id],
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
                    ['movements.user_id', auth()->user()->id],
                    ['category_id', $category->id],
                ])
                ->whereYear('date_purchase', now()->format('Y'))
                ->selectRaw('code, sum(amount) as amount')
                ->join('accounts', 'accounts.id', 'account_id')
                ->join('currencies', 'currencies.id', 'badge_id')
                ->groupBy('code')
                ->get();

            $budgets = Budget::where([
                ['user_id', auth()->user()->id],
                ['category_id', $category->id],
                ['year', now()->format('Y')],
                ])
                ->get();
                
            foreach ($budgets as &$budget) {
                if($budget->period->name === 'Monthly') {
                    $budget->amount = $budget->amount * 12;
                }
            }

            array_push($budgets_main, $budgets);
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

        return response()->json([
            'incomes' => array_values(array_filter($categories->toArray(), fn ($v) => $v['group_id'] == 2)),
            'expensives' => array_values(array_filter($categories->toArray(), fn ($v) => $v['group_id'] > 2)),
            'totalMovements' => $sumsMove,
            'totalBudgets' => $sumsBudget,
        ]);
    }

}
