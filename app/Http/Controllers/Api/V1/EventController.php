<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
 
use App\Models\Event;
use App\Models\Movement;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        $events = Event::where([
            ['user_id', $user->id]
        ])
        ->get();
        
        foreach ($events as &$event) {
            $event->balance = Movement:: where([
                ['movements.event_id', $event->id],
            ])
            ->selectRaw('currencies.code as currency, badge_id, cast(ifnull(sum(amount), 0) as float) as movements')
            ->join('accounts', 'accounts.id', 'movements.account_id')
            ->join('currencies', 'currencies.id', 'accounts.badge_id')
            ->groupByRaw('currencies.code, badge_id')
            ->get();
        }

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

            $user = auth()->user();

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
        $user = auth()->user();
        $data = Event::with(['movements'])
        ->where([
            ['user_id', $user->id],
            ['id', $id]
        ])
        ->first();

        $balances = Movement:: where([
            ['movements.event_id', $id],
        ])
        ->selectRaw('currencies.code as currency, badge_id, cast(ifnull(sum(amount), 0) as float) as movements')
        ->join('accounts', 'accounts.id', 'movements.account_id')
        ->join('currencies', 'currencies.id', 'accounts.badge_id')
        ->groupByRaw('currencies.code, badge_id')
        ->get()
        ->toArray();

        $balanceByCategory = Movement::where([
            ['movements.user_id', $user->id],
            ['movements.event_id', $id]
        ])
        ->selectRaw('movements.category_id, categories.name, currencies.code as currency, badge_id, sum(amount) as balance')
        ->join('categories', 'movements.category_id', '=', 'categories.id')
        ->join('accounts', 'accounts.id', 'movements.account_id')
        ->join('currencies', 'currencies.id', 'accounts.badge_id')
        ->groupBy('movements.category_id', 'categories.name', 'badge_id', 'currencies.code')
        ->orderBy('badge_id', 'asc')
        ->get();

        foreach ($balanceByCategory as &$value) {
            $balanceFilter = array_values(array_filter($balances, fn($v) => $v["currency"] === $value["currency"]));

            $value["percentage"] = round($value["balance"] / $balanceFilter[0]["movements"] * 100,2) . '%';
        }

        if($data) {
            return response()->json(array_merge($data->toArray(), ['categories' => $balanceByCategory]));
        }
        return response([
            'message' =>  'Datos no encontrados',
            'detail' => 'La información no existe'
        ], 400);
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
        $user = auth()->user();
        $events = Event::where([
            ['user_id', $user->id],
        ])
        ->whereDate('end_event', '>=', now())
        ->get();

        foreach ($events as &$event) {
            $event->balance = Movement:: where([
                ['movements.event_id', $event->id],
            ])
            ->selectRaw('currencies.code as currency, badge_id, cast(ifnull(sum(amount), 0) as float) as movements')
            ->join('accounts', 'accounts.id', 'movements.account_id')
            ->join('currencies', 'currencies.id', 'accounts.badge_id')
            ->groupByRaw('currencies.code, badge_id')
            ->get();
        }

        return response()->json($events);
    }
}
