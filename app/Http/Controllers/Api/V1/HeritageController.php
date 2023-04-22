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
            ->selectRaw('year(date_purchase) as year, currencies.code as currency, cast(ifnull(sum(amount), 0) as float) as movements')
            ->addSelect([
                'comercial_amount' => DB::table('heritages')
                ->selectRaw('cast(sum(comercial_amount) as float)')
                ->whereColumn([
                    [DB::raw('year(date_purchase)'), DB::raw($value->year)],
                    ['accounts.badge_id', 'heritages.badge_id']
                ]),
                'legal_amount' => DB::table('heritages')
                ->selectRaw('cast(sum(legal_amount) as float)')
                ->whereColumn([
                    [DB::raw('year(date_purchase)'), DB::raw($value->year)],
                    ['accounts.badge_id', 'heritages.badge_id']
                ])
            ])
            ->join('accounts', 'accounts.id', 'movements.account_id')
            ->join('currencies', 'currencies.id', 'accounts.badge_id')
            ->groupByRaw('year(date_purchase), currencies.code')
            ->get();
        }

        return response()->json($heritages);
    }

}
