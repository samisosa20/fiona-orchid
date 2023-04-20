<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
 
use App\Models\Heritage;

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
        ->get();
        foreach ($heritages as &$value) {
            $value->balance = Heritage::where([
                ['user_id', $user->id],
                ['year', $value->year]
            ])
            ->selectRaw('currencies.code as currency, cast(sum(comercial_amount) as float) as comercial_amount, cast(sum(legal_amount) as float) as legal_amount')
            ->addSelect([
                'movements' => DB::table('movements')
                ->selectRaw('cast(ifnull(sum(amount), 0) as float)')
                ->join('accounts', 'account_id', 'accounts.id')
                ->whereColumn([
                    [DB::raw('year(date_purchase)'), DB::raw($value->year)],
                    ['accounts.badge_id', 'heritages.badge_id']
                ])
            ])
            ->join('currencies', 'currencies.id', 'heritages.badge_id')
            ->groupBy('year', 'currencies.code', 'heritages.badge_id')
            ->get();
        }

        return response()->json($heritages);
    }

}
