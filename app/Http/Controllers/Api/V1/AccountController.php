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
        ->withBalance()
        ->withIncomeExpensiveWithoutTransf()
        ->where([
            ['user_id', $user->id],
            ['id', $id]
        ])
        ->first();
        if($data) {
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
            ->with(['account', 'category', 'event', 'transfer'])
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
            $movements = Account::where([
                ['accounts.user_id', $user->id],
            ])
            ->selectRaw('cast(ifnull(sum(amount), 0) + sum(accounts.init_amount) as FLOAT) as balance, code as currency')
            ->leftJoin('movements', 'accounts.id', 'account_id')
            ->join('currencies', 'badge_id', 'currencies.id')
            ->groupBy('currencies.code')
            ->orderByRaw('cast(ifnull(sum(amount), 0) + sum(accounts.init_amount) as FLOAT) desc')
            ->take(3)
            ->get();
            return response()->json($movements);
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' =>  'Error al conseguir los datos',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }
}
