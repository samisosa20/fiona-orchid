<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

use App\Models\Movement;
use App\Models\Account;
use App\Models\Category;

class ReportController extends Controller
{
    public function report(Request $request)
    {
        try{
            $user = JWTAuth::user();

            $init_date = $request->query('init_date') ?? now()->format("Y-m-d");
            $end_date = $request->query('end_date') ?? now()->format("Y-m-d");
            $currency = $request->query('currency') ?? $user->badge_id;

            // get balance without transferns
            $close_open = Movement::where([
                ['movements.user_id', $user->id],
                ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')],
                ['currencies.id', $currency],
            ])
            ->whereDate('date_purchase', '>=', $init_date)
            ->whereDate('date_purchase', '<=', $end_date)
            ->selectRaw('ifnull(sum(if(amount > 0 , amount, 0)), 0) as income,
            ifnull(sum(if(amount < 0 , amount, 0)), 0) as expensive,
            ifnull(sum(amount), 0) as utility')
            ->join('accounts', 'account_id', 'accounts.id')
            ->join('currencies', 'badge_id', 'currencies.id')
            ->join('categories', 'movements.category_id', 'categories.id')
            ->first();

            $open_balance = Movement::selectRaw('ifnull(sum(amount), 0) as amount')
            ->where([
                ['movements.user_id', $user->id],
                ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')],
                ['currencies.id', $currency],
            ])
            ->whereDate('date_purchase', '<', $init_date)
            ->join('accounts', 'account_id', 'accounts.id')
            ->join('currencies', 'badge_id', 'currencies.id')
            ->join('categories', 'movements.category_id', 'categories.id')
            ->first();

            $open_init_amount = Account::where([
                ['user_id', $user->id],
                ['currencies.id', $currency],
            ])
            ->withTrashed()
            ->whereDate('accounts.created_at', '<', $init_date)
            ->selectRaw('ifnull(sum(init_amount), 0) as amount')
            ->join('currencies', 'badge_id', 'currencies.id')
            ->first();
            
            $income_init_amount = Account::where([
                ['user_id', $user->id],
                ['currencies.id', $currency],
            ])
            ->whereDate('accounts.created_at', '>=', $init_date)
            ->whereDate('accounts.created_at', '<=', $end_date)
            ->selectRaw('ifnull(sum(init_amount), 0) as amount')
            ->join('currencies', 'badge_id', 'currencies.id')
            ->first();

            //get transfer blanaces
            $close_open_transfer = Movement::where([
                ['movements.user_id', $user->id],
                ['movements.trm', '<>', 1],
                ['currencies.id', $currency],
            ])
            ->whereDate('date_purchase', '>=', $init_date)
            ->whereDate('date_purchase', '<=', $end_date)
            ->selectRaw('ifnull(sum(if(amount > 0 , amount, 0)), 0) as income,
            ifnull(sum(if(amount < 0 , amount, 0)), 0) as expensive,
            ifnull(sum(amount), 0) as utility')
            ->join('accounts', 'account_id', 'accounts.id')
            ->join('currencies', 'badge_id', 'currencies.id')
            ->join('categories', 'movements.category_id', 'categories.id')
            ->first();

            $close_open->open_balance = $open_balance->amount + $open_init_amount->amount;
            $close_open->income = $close_open->income + $income_init_amount->amount + $close_open_transfer->income;
            $close_open->expensive = $close_open->expensive + $close_open_transfer->expensive;
            $close_open->utility = $close_open->utility + $income_init_amount->amount + $open_balance->amount + $open_init_amount->amount + $close_open_transfer->utility;

            $incomes = Movement::where([
                ['movements.user_id', $user->id],
                ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')],
                ['amount', '>', 0],
                ['currencies.id', $currency],
            ])
            ->whereDate('date_purchase', '>=', $init_date)
            ->whereDate('date_purchase', '<=', $end_date)
            ->selectRaw('categories.name as category,
            ifnull(sum(amount), 0) as amount')
            ->join('categories', 'movements.category_id', 'categories.id')
            ->join('accounts', 'account_id', 'accounts.id')
            ->join('currencies', 'badge_id', 'currencies.id')
            ->groupBy('categories.name')
            ->orderByRaw('ifnull(sum(amount), 0) desc')
            ->get();

            // income by transfer diferent currency
            $incomes_transfer = Movement::where([
                ['movements.user_id', $user->id],
                ['movements.trm', '<>', 1],
                ['amount', '>', 0],
                ['currencies.id', $currency],
            ])
            ->whereDate('date_purchase', '>=', $init_date)
            ->whereDate('date_purchase', '<=', $end_date)
            ->selectRaw('categories.name as category,
            ifnull(sum(amount), 0) as amount')
            ->join('categories', 'movements.category_id', 'categories.id')
            ->join('accounts', 'account_id', 'accounts.id')
            ->join('currencies', 'badge_id', 'currencies.id')
            ->groupBy('categories.name')
            ->orderByRaw('ifnull(sum(amount), 0) desc')
            ->first();

            if($incomes_transfer){
                $incomes->push($incomes_transfer);
            }
            
            $expensives = Movement::where([
                ['movements.user_id', $user->id],
                ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')],
                ['amount', '<', 0],
                ['currencies.id', $currency],
            ])
            ->whereDate('date_purchase', '>=', $init_date)
            ->whereDate('date_purchase', '<=', $end_date)
            ->selectRaw('categories.name as category,
            ifnull(sum(amount), 0)as amount')
            ->join('categories', 'movements.category_id', 'categories.id')
            ->join('accounts', 'account_id', 'accounts.id')
            ->join('currencies', 'badge_id', 'currencies.id')
            ->groupBy('categories.name')
            ->orderByRaw('ifnull(sum(amount), 0)')
            ->get();
            
            $expensives_transfer = Movement::where([
                ['movements.user_id', $user->id],
                ['movements.trm', '<>', 1],
                ['amount', '<', 0],
                ['currencies.id', $currency],
            ])
            ->whereDate('date_purchase', '>=', $init_date)
            ->whereDate('date_purchase', '<=', $end_date)
            ->selectRaw('categories.name as category,
            abs(ifnull(sum(amount), 0)) as amount')
            ->join('categories', 'movements.category_id', 'categories.id')
            ->join('accounts', 'account_id', 'accounts.id')
            ->join('currencies', 'badge_id', 'currencies.id')
            ->groupBy('categories.name')
            ->orderByRaw('ifnull(sum(amount), 0)')
            ->first();

            $main_expensive = \DB::table('categories as a')
            ->where([
                ['a.user_id', $user->id],
                ['a.group_id', '>', 2],
                ['currencies.id', $currency],
            ])
            ->whereDate('date_purchase', '>=', $init_date)
            ->whereDate('date_purchase', '<=', $end_date)
            ->selectRaw('if(a.category_id is null, a.name, b.name) as category, cast(abs(sum(amount)) as double(15, 2)) as amount')
            ->leftJoin('categories as b', 'a.category_id', 'b.id')
            ->join('movements', 'a.id', 'movements.category_id')
            ->join('accounts', 'accounts.id', 'movements.account_id')
            ->join('currencies', 'currencies.id', 'accounts.badge_id')
            ->groupByRaw('if(a.category_id is null, a.name, b.name)')
            ->orderBy('amount', 'desc')
            ->get();

            if($expensives_transfer){
                $main_expensive->push($expensives_transfer);
            }
            
            $group_expensive = \DB::table('categories as a')
            ->where([
                ['a.user_id', $user->id],
                ['a.group_id', '<>', env('GROUP_TRANSFER_ID')],
                ['currencies.id', $currency],
            ])
            ->whereDate('date_purchase', '>=', $init_date)
            ->whereDate('date_purchase', '<=', $end_date)
            ->selectRaw('b.name, cast(round(sum(amount)) as float) as amount')
            ->join('groups as b', 'a.group_id', 'b.id')
            ->join('movements', 'a.id', 'movements.category_id')
            ->join('accounts', 'accounts.id', 'movements.account_id')
            ->join('currencies', 'currencies.id', 'accounts.badge_id')
            ->groupByRaw('b.name')
            ->orderBy('amount', 'desc')
            ->get();

            $saving = 0;
            $income = 0;
            foreach ($group_expensive as &$value) {
                if($value->name === 'Ingresos') {
                    $income = $value->amount + $close_open_transfer->income;
                    $saving = $value->amount + $close_open_transfer->income;
                    $value->amount += $close_open_transfer->income;
                } else {
                    if($value->name === 'Gastos Fijos') {
                        $value->amount += $close_open_transfer->expensive;
                    }
                    $value->porcent = round(abs($value->amount) / $income * 100, 2);
                    $saving += $value->amount;
                }
            }

            $savingArray = [
                "name" => "Ahorros",
                "amount" => $saving < 0 ? 0 : $saving,
                "porcent" => $saving < 0 || $income === 0 ? 0.00 : round(abs($saving) / $income * 100, 2)
            ];

            $group_expensive->push($savingArray);

            $init = Carbon::parse($init_date);
            $end = Carbon::parse($end_date);

            if($init->diffInDays($end) < 90) {
                $balance = \DB::select('select date, cast(amount as float) as amount from (SELECT @user_id := '.$user->id.' u, @init_date := "'.$init_date.'" i, @end_date := "'.$end_date.'" e, @currency := '.$currency.' c, @group_id := '.env('GROUP_TRANSFER_ID').' g) alias, report_balance');
                
                // income by transfer diferent currency by date
                $incomes_transfer = Movement::where([
                    ['movements.user_id', $user->id],
                    ['movements.trm', '<>', 1],
                    ['currencies.id', $currency],
                ])
                ->whereDate('date_purchase', '>=', $init_date)
                ->whereDate('date_purchase', '<=', $end_date)
                ->selectRaw('DATE_FORMAT(date_purchase, "%b-%d") as date,
                ifnull(sum(amount), 0) as amount')
                ->join('accounts', 'account_id', 'accounts.id')
                ->join('currencies', 'badge_id', 'currencies.id')
                ->groupByRaw('DATE_FORMAT(date_purchase, "%b-%d")')
                ->orderByRaw('DATE_FORMAT(date_purchase, "%b-%d")')
                ->get()
                ->toArray();
            } else {
                $balance = \DB::select('select date, cast(amount as float) as amount from (SELECT @user_id := '.$user->id.' u, @init_date := "'.$init_date.'" i, @end_date := "'.$end_date.'" e, @currency := '.$currency.' c, @group_id := '.env('GROUP_TRANSFER_ID').' g) alias, report_global_balance');
                
                // income by transfer diferent currency by date
                $incomes_transfer = Movement::where([
                    ['movements.user_id', $user->id],
                    ['movements.trm', '<>', 1],
                    ['currencies.id', $currency],
                ])
                ->whereDate('date_purchase', '>=', $init_date)
                ->whereDate('date_purchase', '<=', $end_date)
                ->selectRaw('DATE_FORMAT(date_purchase, "%b-01") as date,
                ifnull(sum(amount), 0) as amount')
                ->join('accounts', 'account_id', 'accounts.id')
                ->join('currencies', 'badge_id', 'currencies.id')
                ->groupByRaw('DATE_FORMAT(date_purchase, "%b-01")')
                ->orderByRaw('DATE_FORMAT(date_purchase, "%b-01")')
                ->get()
                ->toArray();
            }

            $acumAux = 0;
            foreach ($balance as $key => &$value) {
                $data = array_filter($incomes_transfer, function ($transfer) use ($value) {
                    return strtotime($transfer['date']) === strtotime($value->date);
                });
                if(count($data) > 0){
                    $value->amount += array_values($data)[0]['amount'];
                }
                $prevAmount = $value->amount;
                $value->amount += $acumAux;
                $acumAux += $prevAmount;
            }

            return response()->json([
                'open_close' => $close_open,
                'incomes' => $incomes,
                'main_expensive' => $main_expensive,
                'group_expensive' => $group_expensive,
                'list_expensives' => $expensives,
                'balances' => $balance
            ]);
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' => 'Datos no obtenidos',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }
}