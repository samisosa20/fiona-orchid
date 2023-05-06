<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
 
use App\Models\Heritage;
use App\Models\Movement;
use App\Models\Account;

class HeritageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = JWTAuth::user();
        $heritages = Heritage::where([
            ['user_id', $user->id]
        ])
        ->with('currency')
        ->when($request->query('year'), function ($query) use ($request) {
            $query->where('year', $request->query('year'));
        })
        ->orderBy('year', 'desc')
        ->get();

        return response()->json($heritages);
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

            $user = JWTAuth::user();

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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function consolidate(Request $request)
    {
        $user = JWTAuth::user();

        $heritages = Heritage::where([
            ['user_id', $user->id]
        ])
        ->distinct('year')
        ->select('year')
        ->orderBy('year')
        ->get();
        foreach ($heritages as &$value) {
            $value->balance = Movement:: where([
                ['movements.user_id', $user->id],
            ])
            ->whereYear('date_purchase', '=', $value->year)
            ->selectRaw('year(date_purchase) as year, currencies.code as currency, badge_id, cast(ifnull(sum(amount), 0) as float) as movements')
            ->join('accounts', 'accounts.id', 'movements.account_id')
            ->join('currencies', 'currencies.id', 'accounts.badge_id')
            ->groupByRaw('year(date_purchase), currencies.code, badge_id')
            ->get();
            foreach ($value->balance  as &$balance) {
                $init_amout = Account::where([
                    ['user_id', $user->id],
                    ['badge_id', $balance->badge_id],
                ])
                ->selectRaw('sum(init_amount) as amount')
                ->whereNull('deleted_at')
                ->first();
                $balance->movements = $balance->movements + $init_amout->amount;
                $comercial_amount = Heritage::where([
                    ['user_id', $user->id],
                    ['year', $value->year],
                    ['badge_id', $balance->badge_id],
                ])
                ->selectRaw('cast(ifnull(sum(comercial_amount), 0) as float) as comercial_amount')
                ->first();
                $legal_amount = Heritage::where([
                    ['user_id', $user->id],
                    ['year', $value->year],
                    ['badge_id', $balance->badge_id],
                ])
                ->selectRaw('cast(ifnull(sum(legal_amount), 0) as float) as legal_amount')
                ->first();
                $balance->comercial_amount = $comercial_amount->comercial_amount;
                $balance->legal_amount = $legal_amount->legal_amount;
            }
        }

        return response()->json($heritages);
    }

}
