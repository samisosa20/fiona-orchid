<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
 
use App\Models\Account;
use App\Models\Movement;

use App\Controllers\Accounts\AccountController as AccountControl;
use App\Controllers\Reports\ReportController;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        $accounts = Account::withTrashed()
        ->with(['currency', 'type'])
        ->withBalance()
        ->where([
            ['user_id', $user->id]
        ])
        ->get();

        return response()->json([
            'accounts' => $accounts,
            'balances' => AccountControl::totalBalance()
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
                'badge_id' => [
                    'required'
                ],
                'init_amount' => [
                    'required'
                ],
                'type_id' => [
                    'required',
                ],
                'badge_id' => [
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
    public function show($id, Request $request)
    {
        $user = auth()->user();
        $data = Account::withTrashed()
        ->with(['currency'])
        ->where([
            ['user_id', $user->id],
            ['id', $id]
        ])
        ->first();
        if($data) {
            $balance = ReportController::balanceByAccount($request, $data->id);

            $movements = Movement::where([
                ['account_id', $data->id],
                ['user_id', auth()->user()->id],
            ])
            ->filters()
            ->filter($request)
            ->with(['account', 'category', 'event', 'transferOut', 'transferIn'])
            ->orderBy('date_purchase', 'desc');

            if($request->query('end_date')){
                $listMovements['data'] = $movements->get();
            } else {
                $listMovements = $movements->paginate(50);
            }

            return response()->json([
                'balancesAccount' => [
                    [
                        'name'   => 'Balance',
                        'values' => array_map(fn ($v) => $v->amount, $balance),
                        'labels' => array_map(fn ($v) => $v->date, $balance),
                    ]
                ],
                'account' => $data,
                'movements' => $listMovements,
                'balances' => AccountControl::totalBalanceByAccount($data),
            ]);
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
                'type_id' => [
                    'required',
                ],
                'badge_id' => [
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
                'message' => 'Cuenta Inactivada exitosamente',
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function hardDestory($id)
    {
        try {
            $account = Account::withTrashed()->find($id);
            $account->forceDelete();
            return response()->json([
                'message' => 'Cuenta Eliminada exitosamente',
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
            $user = auth()->user();
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
            $user = auth()->user();
            $movements = DB::select('select * from (SELECT @user_id := '.$user->id.' i) alias, general_balance');
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
            $user = auth()->user();
            $movements = DB::select('select * from (SELECT @user_id := '.$user->id.' i) alias, general_month_year');
            $balance_total = DB::select('select * from (SELECT @user_id := '.$user->id.' i) alias, general_balance');

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
