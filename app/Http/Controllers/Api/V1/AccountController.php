<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
 
use App\Models\Account;
use App\Models\Movement;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = JWTAuth::user();
        $accounts = Account::withTrashed()
        ->with(['currency'])
        ->withBalance()
        ->where([
            ['user_id', $user->id]
        ])
        ->get();

        return response()->json($accounts);
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
                'badge_id' => [
                    'required'
                ],
                'init_amount' => [
                    'required'
                ],
                'type' => [
                    'required',
                ],
            ]);

            if($validator->fails()){
                return response([
                    'message' => 'data missing',
                    'detail' => $validator->errors()
                ], 400)->header('Content-Type', 'json');
            }

            $user = JWTAuth::user();

            $account = Account::create(array_merge($request->input(), ['user_id' => $user->id]));

            return response()->json([
                'message' => 'Cuenta creada exitosamente',
                'data' => $account,
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = JWTAuth::user();
        $data = Account::withTrashed()
        ->with(['currency'])
        ->where([
            ['user_id', $user->id],
            ['id', $id]
        ])
        ->first();
        if($data) {
            $month_transfer = Movement::where([
                ['movements.user_id', $user->id],
                ['trm', '<>', 1],
                ['account_id', $id]
            ])
            ->whereRaw('month(date_purchase) = month(now()) and year(date_purchase) = year(now())')
            ->selectRaw('ifnull(sum(if(amount > 0, amount, 0)), 0) as incomes, ifnull(sum(if(amount < 0, amount, 0)), 0) as expensives')
            ->join('accounts', 'account_id', 'accounts.id')
            ->first();
            
            $month_balance = Movement::where([
                ['movements.user_id', $user->id],
                ['group_id', '<>', env('GROUP_TRANSFER_ID')],
                ['account_id', $id]
            ])
            ->whereRaw('month(date_purchase) = month(now()) and year(date_purchase) = year(now())')
            ->selectRaw('ifnull(sum(if(amount > 0, amount, 0)), 0) as incomes, ifnull(sum(if(amount < 0, amount, 0)), 0) as expensives')
            ->join('accounts', 'account_id', 'accounts.id')
            ->join('categories', 'movements.category_id', 'categories.id')
            ->first();

            $year_transfer = Movement::where([
                ['movements.user_id', $user->id],
                ['trm', '<>', 1],
                ['account_id', $id]
            ])
            ->whereRaw('year(date_purchase) = year(now())')
            ->selectRaw('ifnull(sum(if(amount > 0, amount, 0)), 0) as incomes, ifnull(sum(if(amount < 0, amount, 0)), 0) as expensives')
            ->join('accounts', 'account_id', 'accounts.id')
            ->first();
            
            $year_balance = Movement::where([
                ['movements.user_id', $user->id],
                ['group_id', '<>', env('GROUP_TRANSFER_ID')],
                ['account_id', $id]
            ])
            ->whereRaw('year(date_purchase) = year(now())')
            ->selectRaw('ifnull(sum(if(amount > 0, amount, 0)), 0) as incomes, ifnull(sum(if(amount < 0, amount, 0)), 0) as expensives')
            ->join('accounts', 'account_id', 'accounts.id')
            ->join('categories', 'movements.category_id', 'categories.id')
            ->first();
            
            $total_transfer = Movement::where([
                ['movements.user_id', $user->id],
                ['trm', '<>', 1],
                ['account_id', $id]
            ])
            ->selectRaw('ifnull(sum(if(amount > 0, amount, 0)), 0) as incomes, ifnull(sum(if(amount < 0, amount, 0)), 0) as expensives')
            ->join('accounts', 'account_id', 'accounts.id')
            ->first();
            
            $total_balance = Movement::where([
                ['movements.user_id', $user->id],
                ['group_id', '<>', env('GROUP_TRANSFER_ID')],
                ['account_id', $id]
            ])
            ->selectRaw('ifnull(sum(if(amount > 0, amount, 0)), 0) as incomes, ifnull(sum(if(amount < 0, amount, 0)), 0) as expensives')
            ->join('accounts', 'account_id', 'accounts.id')
            ->join('categories', 'movements.category_id', 'categories.id')
            ->first();

            $movements = \DB::select('select * from (SELECT @user_id := '.$user->id.' i, @account_id := '.$id.' a) alias, general_month_year_account');
            $balance_total = \DB::select('select * from (SELECT @user_id := '.$user->id.'  i, @account_id := '.$id.' a) alias, general_balance_account');

            $balance_adjust = $balance_total = array_map(function($element) {
                $element->type = "total";
                return $element;
              }, $balance_total);

            $month = [
                'type' => 'month',
                'incomes' => $month_transfer->incomes + $month_balance->incomes,
                'expensives' => $month_transfer->expensives + $month_balance->expensives,
            ];

            $year = [
                'type' => 'year',
                'incomes' => $year_transfer->incomes + $year_balance->incomes,
                'expensives' => $year_transfer->expensives + $year_balance->expensives,
            ];
            $total = [
                'type' => 'total',
                'incomes' => $total_transfer->incomes + $total_balance->incomes,
                'expensives' => $total_transfer->expensives + $total_balance->expensives,
            ];

            $data->month = $month;
            $data->year = $year;
            $data->total = $total;
            $data->balance = array_merge($movements, $balance_adjust);
            
            return response()->json($data);
        }
        return response([
            'message' =>  'Datos no encontrados',
            'detail' => 'La información no existe'
        ], 400);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function edit(Account $account)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Account $account)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                ],
                'badge_id' => [
                    'required'
                ],
                'init_amount' => [
                    'required'
                ],
                'type' => [
                    'required',
                ],
            ]);

            if($validator->fails()){
                return response([
                    'message' => 'data missing',
                    'detail' => $validator->errors()
                ], 400)->header('Content-Type', 'json');
            }

            $account->fill($request->input())->save();

            return response()->json([
                'message' => 'Cuenta editada exitosamente',
                'data' => $account,
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
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function destroy(Account $account)
    {
        try {
            $account->delete();
            return response()->json([
                'message' => 'Cuenta inactivada exitosamente',
                'data' => $account,
            ]);
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' =>  'Datos no guardados',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        try {
            $account = Account::withTrashed()->find($id)->restore();
            return response()->json([
                'message' => 'Cuenta Activada exitosamente',
                'data' => $account,
            ]);
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' =>  'Datos no guardados',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }
    
    /**
     * Restore the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function movements(int $id)
    {
        try {
            $user = JWTAuth::user();
            $movements = Movement::where([
                ['account_id', $id],
                ['user_id', $user->id],
            ])
            ->with(['account', 'category', 'event', 'transferOut', 'transferIn'])
            ->orderBy('date_purchase', 'desc')
            ->get();
            return response()->json($movements);
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' =>  'Error al conseguir los datos',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }
    
    /**
     * Restore the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function balances()
    {
        try {
            $user = JWTAuth::user();
            $movements = \DB::select('select * from (SELECT @user_id := '.$user->id.' i) alias, general_balance');
            return response()->json($movements);
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' =>  'Error al conseguir los datos',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }
    
    /**
     * Restore the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function balancesMonthYear()
    {
        try {
            $user = JWTAuth::user();
            $movements = \DB::select('select * from (SELECT @user_id := '.$user->id.' i) alias, general_month_year');
            $balance_total = \DB::select('select * from (SELECT @user_id := '.$user->id.' i) alias, general_balance');

            $balance_adjust = $balance_total = array_map(function($element) {
                $element->type = "total";
                return $element;
              }, $balance_total);
            

            return response()->json(array_merge($movements, $balance_adjust));
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' =>  'Error al conseguir los datos',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }
}
