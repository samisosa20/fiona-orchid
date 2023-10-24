<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

use App\Controllers\Reports\ReportController as Report;

use App\Models\Currency;
use App\Models\Movement;
class ReportController extends Controller
{
    public function report(Request $request)
    {
        try{
            $data = Report::report($request);
            $init_date = $request->query('start_date') ?? Carbon::now()->firstOfMonth()->format("Y-m-d");
            $end_date = $request->query('end_date') ?? Carbon::now()->lastOfMonth()->format("Y-m-d");
            $currency = $request->query('badge_id') ?? auth()->user()->badge_id;

            return [
                'incomes' => $data['incomes'],
                'expensives' =>  $data['main_expensive'],
                'balances' => $data['balances'],
                'group_expensive' => $data['group_expensive'],
                'list_expensives' => $data['list_expensives'],
                'list_incomes' => $data['incomes'],
                'credit_carts' => $data['credit_carts'],
                'metrics' => [
                    'open_balance'    => number_format($data['open_close']->open_balance, 2, ',', '.'),
                    'income' => number_format($data['open_close']->income, 2, ',', '.'),
                    'expensive'   => number_format($data['open_close']->expensive, 2, ',', '.'),
                    'utility'    => number_format($data['open_close']->utility, 2, ',', '.'),
                ],
                'init_date' => $init_date,
                'end_date' => $end_date,
                'currency' => Currency::find($currency),
                'movements' => [],
            ];
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' => 'Datos no obtenidos',
                'detail' => $ex//->errorInfo[0]
            ], 400);
        }
    }

    public function movementsByCategory (Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'category_id' => [
                    'required',
                ],
            ]);

            if($validator->fails()){
                return response([
                    'message' => 'data missing',
                    'detail' => $validator->errors()
                ], 400)->header('Content-Type', 'json');
            }
            $init_date = $request->all()['start_date'] ?? Carbon::now()->firstOfMonth()->format("Y-m-d");
            $end_date = $request->all()['end_date'] ?? Carbon::now()->lastOfMonth()->format("Y-m-d");
            $currency = $request->all()['badge_id'] ?? auth()->user()->badge_id;

            $movements = Movement::where([
                ['movements.user_id', auth()->user()->id],
                ['category_id', $request->all()['category_id']],
            ])
            ->with(['account', 'event'])
            ->whereDate('date_purchase', '>=', $init_date)
            ->whereDate('date_purchase', '<=', $end_date)
            ->whereHas('account', function ($query) use($currency){
                $query->where([
                    ['badge_id', $currency]
                ]);
            })
            ->get();

            return $movements;
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' => 'Datos no obtenidos',
                'detail' => $ex//->errorInfo[0]
            ], 400);
        }
    }
    
    public function movementsByGroup (Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'group_id' => [
                    'required',
                ],
            ]);

            if($validator->fails()){
                return response([
                    'message' => 'data missing',
                    'detail' => $validator->errors()
                ], 400)->header('Content-Type', 'json');
            }

            $init_date = $request->all()['start_date'] ?? Carbon::now()->firstOfMonth()->format("Y-m-d");
            $end_date = $request->all()['end_date'] ?? Carbon::now()->lastOfMonth()->format("Y-m-d");
            $currency = $request->all()['badge_id'] ?? auth()->user()->badge_id;

            $movements = Movement::where([
                ['movements.user_id', auth()->user()->id],
            ])
            ->with(['account', 'event'])
            ->with('category', function ($query) {
                $query->with('categoryFather');
            })
            ->whereDate('date_purchase', '>=', $init_date)
            ->whereDate('date_purchase', '<=', $end_date)
            ->whereHas('category', function ($query) use($request){
                $query->where([
                    ['group_id', $request->all()['group_id']]
                ]);
            })
            ->whereHas('account', function ($query) use($currency){
                $query->where([
                    ['badge_id', $currency]
                ]);
            })
            ->selectRaw('category_id, sum(amount) as amount')
            ->groupBy('category_id')
            ->orderByRaw('sum(amount) ASC')
            ->get();

            return $movements;
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' => 'Datos no obtenidos',
                'detail' => $ex//->errorInfo[0]
            ], 400);
        }
    }
}