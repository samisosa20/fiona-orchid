<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
 
use App\Models\PlannedPayment;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = JWTAuth::user();
        $payments = PlannedPayment::where([
            ['user_id', $user->id]
        ])
        ->with('category')
        ->with(['account' => function ($query) {
            $query->with('currency');
        }])
        ->get();

        return response()->json($payments);
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
                'account_id' => [
                    'required',
                ],
                'category_id' => [
                    'required',
                ],
                'amount' => [
                    'required',
                ],
                'start_date' => [
                    'required',
                    'date_format:Y-m-d'
                ],
                'specific_day' => [
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

            $payment = PlannedPayment::create(array_merge($request->input(), ['user_id' => $user->id]));

            return response()->json([
                'message' => 'Pago creado exitosamente',
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
        $user = JWTAuth::user();
        $data = PlannedPayment::with('category')
        ->with(['account' => function ($query) {
            $query->with('currency');
        }])
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
     * @param  \App\Models\PlannedPayment  $payment
     * @return \Illuminate\Http\Response
     */
    public function edit(PlannedPayment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PlannedPayment  $payment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PlannedPayment $payment)
    {
        try{
            $validator = Validator::make($request->all(), [
                'account_id' => [
                    'required',
                ],
                'category_id' => [
                    'required',
                ],
                'amount' => [
                    'required',
                ],
                'start_date' => [
                    'required',
                    'date_format:Y-m-d'
                ],
                'specific_day' => [
                    'required',
                ],
            ]);

            if($validator->fails()){
                return response([
                    'message' => 'data missing',
                    'detail' => $validator->errors()
                ], 400)->header('Content-Type', 'json');
            }

            $payment->fill($request->input())->save();

            return response()->json([
                'message' => 'Pago editado exitosamente',
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PlannedPayment  $payment
     * @return \Illuminate\Http\Response
     */
    public function destroy(PlannedPayment $payment)
    {
        try {
            $payment->delete();
            return response()->json([
                'message' => 'Pago eliminado exitosamente',
                'data' => $payment,
            ]);
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' =>  'Datos no guardados',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }

}
