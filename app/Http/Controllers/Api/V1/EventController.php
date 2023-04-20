<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
 
use App\Models\Event;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = JWTAuth::user();
        $events = Event::where([
            ['user_id', $user->id]
        ])
        ->withBalance()
        ->get();

        return response()->json($events);
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
                'end_event' => [
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

            $user = JWTAuth::user();

            $event = Event::create(array_merge($request->input(), ['user_id' => $user->id]));

            return response()->json([
                'message' => 'Evento creado exitosamente',
                'data' => $event,
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
        $data = Event::with(['movements'])
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
        return response()->json($event);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                ],
                'end_event' => [
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

            $event->fill($request->input())->save();

            return response()->json([
                'message' => 'Evento editado exitosamente',
                'data' => $event,
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
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        try {
            $event->delete();
            return response()->json([
                'message' => 'Evento eliminado exitosamente',
                'data' => $event,
            ]);
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' =>  'Datos no guardados',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }

    /**
     * Display a listing of the resource that end_event less or equal today.
     *
     * @return \Illuminate\Http\Response
     */
    public function active()
    {
        $user = JWTAuth::user();
        $events = Event::where([
            ['user_id', $user->id],
        ])
        ->withBalance()
        /* ->with('movements')
        ->with(['movements' => function ($query) {
            $query->selectRaw('ifnull(sum(amount), 0), currencies.code as currency')
            ->join('accounts', 'accounts.id', 'account_id')
            ->join('currencies', 'currencies.id', 'badge_id')
            ->groupBy('currencies.code');
        }]) */
        ->whereDate('end_event', '>=', now())
        ->get();

        return response()->json($events);
    }
}
