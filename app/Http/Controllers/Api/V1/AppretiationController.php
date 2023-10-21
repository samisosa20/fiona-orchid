<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
 
use App\Models\InvestmentAppreciation;

class AppretiationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $appretiations = InvestmentAppreciation::where([
            ['user_id', auth()->user()->id]
        ])
        ->with('investment')
        ->get();

        return response()->json($appretiations);
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
                'investment_id' => [
                    'required',
                ],
                'amount' => [
                    'required',
                ],
                'date_appreciation' => [
                    'required',
                    'date_format:Y-m-d'
                ],
            ]);

            if($validator->fails()){
                return response([
                    'message' => 'data missing',
                    'detail' => $validator->errors()
                ], 400)->header('Content-Type', 'json');
            }

            $user = auth()->user();

            $payment = InvestmentAppreciation::create(array_merge($request->input(), ['user_id' => $user->id]));

            return response()->json([
                'message' => 'Valorizacion creada exitosamente',
                'data' => $payment,
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
        $data = InvestmentAppreciation::where([
            ['user_id', auth()->user()->id],
            ['investment_id', $id]
        ])
        ->with(['investment'])
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
     * @param  \App\Models\InvestmentAppreciation  $payment
     * @return \Illuminate\Http\Response
     */
    public function edit(InvestmentAppreciation $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $investment = InvestmentAppreciation::find($id);
        try{
            $validator = Validator::make($request->all(), [
                'investment_id' => [
                    'required',
                ],
                'amount' => [
                    'required',
                ],
                'date_appreciation' => [
                    'required',
                    'date_format:Y-m-d'
                ],
            ]);

            if($validator->fails()){
                return response([
                    'message' => 'data missing',
                    'detail' => $validator->errors()
                ], 400)->header('Content-Type', 'json');
            }

            $investment->fill(array_merge($request->input(), ['user_id' => auth()->user()->id]))->save();

            return response()->json([
                'message' => 'Valorizacion editada exitosamente',
                'data' => $investment,
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
     * @param  int  $investment
     * @return \Illuminate\Http\Response
     */
    public function destroy($investment)
    {
        try {
            InvestmentAppreciation::find($investment)->delete();
            return response()->json([
                'message' => 'Valorizacion eliminada exitosamente',
                'data' => $investment,
            ]);
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' =>  'Datos no guardados',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }

}
