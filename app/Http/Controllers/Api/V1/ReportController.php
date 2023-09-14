<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Controllers\Reports\ReportController as Report;

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
                'incomes' => [
                    [
                        'name'   => 'Incomes',
                        'values' => array_map(fn ($v) => $v['amount'], $data['incomes']->toArray()),
                        'labels' => array_map(fn ($v) => $v['category'], $data['incomes']->toArray()),
                    ]
                ],
                'expensives' => [
                    [
                        'name'   => 'Expensives',
                        'values' => array_map(fn ($v) => is_array($v) ? $v['amount'] : $v->amount, $data['main_expensive']->toArray()),
                        'labels' => array_map(fn ($v) => is_array($v) ? $v['category'] : $v->category, $data['main_expensive']->toArray()),
                    ]
                ],
                'balances' => [
                    [
                        'name'   => 'Expensives',
                        'values' => array_map(fn ($v) => $v->amount, $data['balances']),
                        'labels' => array_map(fn ($v) => $v->date, $data['balances']),
                    ]
                ],
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
                'currency' => $currency,
                'movements' => []
            ];
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' => 'Datos no obtenidos',
                'detail' => $ex//->errorInfo[0]
            ], 400);
        }
    }
}