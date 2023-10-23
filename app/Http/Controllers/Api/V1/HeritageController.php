<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
 
use App\Models\Heritage;
use App\Models\Movement;
use App\Models\Account;
use App\Models\Investment;

class HeritageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $heritages = Heritage::where([
            ['user_id', $user->id]
        ])
        ->with('currency')
        ->when($request->query('year'), function ($query) use ($request) {
            $query->where('year', $request->query('year'));
        })
        ->orderBy('year', 'desc')
        ->get();

        $investments = Investment::select('currencies.code', DB::raw('SUM(investment_appreciations.amount) as amount'))
        ->leftJoin('investment_appreciations', function($join) {
            $join->on('investment_appreciations.investment_id', '=', 'investments.id')
                 ->where('investment_appreciations.date_appreciation', '=', DB::raw('(SELECT MAX(date_appreciation) FROM investment_appreciations WHERE investment_id = investments.id)'));
        })
        ->join('currencies', 'currencies.id', '=', 'investments.badge_id')
        ->groupBy('currencies.code')
        ->get();


        $movements =  Movement::where([
            ['movements.user_id', auth()->user()->id],
        ])
        ->when($request->query('year'), function ($query) use ($request) {
            $query->whereYear('date_purchase', '<=', $request->query('year'));
        })
        ->selectRaw('currencies.code as currency, badge_id, ifnull(sum(amount), 0) as movements')
        ->join('accounts', 'accounts.id', 'movements.account_id')
        ->join('currencies', 'currencies.id', 'accounts.badge_id')
        ->join('categories', 'movements.category_id', 'categories.id')
        ->groupByRaw('currencies.code, badge_id')
        ->get();

        // get information by currency code
        foreach ($movements  as &$balance) {

            $init_amout = Account::withTrashed()
            ->where([
                ['user_id', auth()->user()->id],
                ['badge_id', $balance->badge_id],
            ])
            ->selectRaw('sum(init_amount) as amount')
            ->when($request->query('year'), function ($query) use ($request) {
                $query->whereYear('created_at', '<=', $request->query('year'));
            })
            ->first();
            $balance->total_amount = round($balance->movements + $init_amout->amount, 2);
        }


        return response()->json([
            'heritages' => $heritages,
            'investments' => $investments->transform(function ($investment) {$investment->amount = (float) $investment->amount; return $investment;}),
            'balances' => $movements
        ]);
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
                'name' => [
                    'required',
                ],
                'comercial_amount' => [
                    'required',
                ],
                'legal_amount' => [
                    'required',
                ],
                'badge_id' => [
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

            $heritage = Heritage::create(array_merge($request->input(), ['user_id' => $user->id]));

            return response()->json([
                'message' => 'Patrimonio creado exitosamente',
                'data' => $heritage,
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
     * @param  \App\Models\Heritage  $heritage
     * @return \Illuminate\Http\Response
     */
    public function show(Heritage $heritage)
    {
        return response()->json($heritage);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Heritage  $heritage
     * @return \Illuminate\Http\Response
     */
    public function edit(Heritage $heritage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Heritage  $heritage
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Heritage $heritage)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                ],
                'comercial_amount' => [
                    'required',
                ],
                'legal_amount' => [
                    'required',
                ],
                'badge_id' => [
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

            $heritage->fill($request->input())->save();

            return response()->json([
                'message' => 'Patrimonio editado exitosamente',
                'data' => $heritage,
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
     * @param  \App\Models\Heritage  $heritage
     * @return \Illuminate\Http\Response
     */
    public function destroy(Heritage $heritage)
    {
        try {
            $heritage->delete();
            return response()->json([
                'message' => 'Patrimonio eliminado exitosamente',
                'data' => $heritage,
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
        $heritages =  Heritage::where([
            ['user_id', auth()->user()->id]
        ])
        ->distinct('year')
        ->select('year')
        ->orderBy('year')
        ->get();

        foreach ($heritages as &$value) {
            $value->balance = Movement::where([
                ['movements.user_id', auth()->user()->id],
            ])
            ->whereYear('date_purchase', '<=', $value->year)
            ->selectRaw('currencies.code as currency, badge_id, ifnull(sum(amount), 0) as movements')
            ->join('accounts', 'accounts.id', 'movements.account_id')
            ->join('currencies', 'currencies.id', 'accounts.badge_id')
            ->join('categories', 'movements.category_id', 'categories.id')
            ->groupByRaw('currencies.code, badge_id')
            ->get();

            // get information by currency code
            foreach ($value->balance  as &$balance) {

                $init_amout = Account::withTrashed()
                ->where([
                    ['user_id', auth()->user()->id],
                    ['badge_id', $balance->badge_id],
                ])
                ->selectRaw('sum(init_amount) as amount')
                ->whereYear('created_at', '<=', $value->year)
                ->first();
                
                $comercial_amount = Heritage::where([
                    ['user_id', auth()->user()->id],
                    ['year', $value->year],
                    ['badge_id', $balance->badge_id],
                ])
                ->selectRaw('ifnull(sum(comercial_amount), 0) as comercial_amount')
                ->first();
                
                $investments = Investment::select('currencies.code', DB::raw('SUM(investment_appreciations.amount) as amount'))
                ->leftJoin('investment_appreciations', function($join) {
                    $join->on('investment_appreciations.investment_id', '=', 'investments.id')
                         ->where('investment_appreciations.date_appreciation', '=', DB::raw('(SELECT MAX(date_appreciation) FROM investment_appreciations WHERE investment_id = investments.id)'));
                })
                ->where([
                    ['badge_id', $balance->badge_id],
                ])
                ->join('currencies', 'currencies.id', '=', 'investments.badge_id')
                ->groupBy('currencies.code')
                ->get();

                $balance->comercial_amount = $comercial_amount->comercial_amount;
                $balance->investments = (float)$investments[0]->amount;

                $balance->amount = round($comercial_amount->comercial_amount + $balance->movements + $init_amout->amount + $investments[0]->amount, 2);
            }
        }


        return response()->json($heritages);
    }

}
