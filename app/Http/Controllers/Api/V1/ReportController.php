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

            $close_open = Movement::where([
                ['movements.user_id', $user->id],
                ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')],
                ['currencies.id', $currency],
            ])
            ->whereDate('date_purchase', '>=', $init_date)
            ->whereDate('date_purchase', '<=', $end_date)
            ->selectRaw('cast(ifnull(sum(if(amount > 0 , amount, 0)), 0) as decimal) as income,
            cast(ifnull(sum(if(amount < 0 , amount, 0)), 0) as decimal) as expensive,
            cast(ifnull(sum(amount), 0) as decimal) as utility')
            ->join('accounts', 'account_id', 'accounts.id')
            ->join('currencies', 'badge_id', 'currencies.id')
            ->join('categories', 'movements.category_id', 'categories.id')
            ->first();

            $open_balance = Movement::selectRaw('cast(ifnull(sum(amount), 0) as decimal) as amount')
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
            ->selectRaw('cast(ifnull(sum(init_amount), 0) as decimal) as amount')
            ->join('currencies', 'badge_id', 'currencies.id')
            ->first();
            
            $income_init_amount = Account::where([
                ['user_id', $user->id],
                ['currencies.id', $currency],
            ])
            ->whereDate('accounts.created_at', '>=', $init_date)
            ->whereDate('accounts.created_at', '<=', $end_date)
            ->selectRaw('cast(ifnull(sum(init_amount), 0) as decimal) as amount')
            ->join('currencies', 'badge_id', 'currencies.id')
            ->first();

            $close_open->open_balance = $open_balance->amount + $open_init_amount->amount;
            $close_open->income = $close_open->income + $income_init_amount->amount;
            $close_open->utility = $close_open->utility + $income_init_amount->amount + $open_balance->amount + $open_init_amount->amount;

            $incomes = Movement::where([
                ['movements.user_id', $user->id],
                ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')],
                ['amount', '>', 0],
                ['currencies.id', $currency],
            ])
            ->whereDate('date_purchase', '>=', $init_date)
            ->whereDate('date_purchase', '<=', $end_date)
            ->selectRaw('categories.name as category,
            cast(ifnull(sum(amount), 0) as decimal) as amount')
            ->join('categories', 'movements.category_id', 'categories.id')
            ->join('accounts', 'account_id', 'accounts.id')
            ->join('currencies', 'badge_id', 'currencies.id')
            ->groupBy('categories.name')
            ->orderByRaw('ifnull(sum(amount), 0) desc')
            ->get();
            
            $expensives = Movement::where([
                ['movements.user_id', $user->id],
                ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')],
                ['amount', '<', 0],
                ['currencies.id', $currency],
            ])
            ->whereDate('date_purchase', '>=', $init_date)
            ->whereDate('date_purchase', '<=', $end_date)
            ->selectRaw('categories.name as category,
            cast(ifnull(sum(amount), 0) as decimal) as amount')
            ->join('categories', 'movements.category_id', 'categories.id')
            ->join('accounts', 'account_id', 'accounts.id')
            ->join('currencies', 'badge_id', 'currencies.id')
            ->groupBy('categories.name')
            ->orderByRaw('ifnull(sum(amount), 0)')
            ->get();

            $main_expensive = \DB::table('categories as a')
            ->where([
                ['a.user_id', $user->id],
                ['a.group_id', '>', 2],
                ['currencies.id', $currency],
            ])
            ->whereDate('date_purchase', '>=', $init_date)
            ->whereDate('date_purchase', '<=', $end_date)
            ->selectRaw('if(a.category_id is null, a.name, b.name) as category, cast(round(abs(sum(amount))) as decimal) as amount')
            ->leftJoin('categories as b', 'a.category_id', 'b.id')
            ->join('movements', 'a.id', 'movements.category_id')
            ->join('accounts', 'accounts.id', 'movements.account_id')
            ->join('currencies', 'currencies.id', 'accounts.badge_id')
            ->groupByRaw('if(a.category_id is null, a.name, b.name)')
            ->orderBy('amount', 'desc')
            ->get();
            
            $group_expensive = \DB::table('categories as a')
            ->where([
                ['a.user_id', $user->id],
                ['a.group_id', '<>', env('GROUP_TRANSFER_ID')],
                ['currencies.id', $currency],
            ])
            ->whereDate('date_purchase', '>=', $init_date)
            ->whereDate('date_purchase', '<=', $end_date)
            ->selectRaw('b.name, cast(round(sum(amount)) as decimal) as amount')
            ->join('groups as b', 'a.group_id', 'b.id')
            ->join('movements', 'a.id', 'movements.category_id')
            ->join('accounts', 'accounts.id', 'movements.account_id')
            ->join('currencies', 'currencies.id', 'accounts.badge_id')
            ->groupByRaw('b.name')
            ->orderBy('amount', 'desc')
            ->get();

            foreach ($group_expensive as &$value) {
                $saving = 0;
                if($value->name === 'Ingresos') {
                    $income = $value->amount;
                    $saving = $value->amount;
                } else {
                    $value->porcent = round(abs($value->amount) / $income * 100, 2);
                    $saving += $value->amount;
                }
            }
            $savingArray = [
                "name" => "Ahorros",
                "amount" => $saving < 0 ? 0 : $saving,
                "porcent" => $saving < 0 ? 0.00 : round(abs($value->amount) / $income * 100, 2)
            ];

            $group_expensive->push($savingArray);

            $init = Carbon::parse($init_date);
            $end = Carbon::parse($end_date);

            if($init->diffInDays($end) < 90) {
                $balance = \DB::select('select date, cast(amount as decimal) as amount from (SELECT @user_id := '.$user->id.' u, @init_date := "'.$init_date.'" i, @end_date := "'.$end_date.'" e, @currency := '.$currency.' c, @group_id := '.env('GROUP_TRANSFER_ID').' g) alias, report_balance');
            } else {
                $balance = \DB::select('select date, cast(amount as decimal) as amount from (SELECT @user_id := '.$user->id.' u, @init_date := "'.$init_date.'" i, @end_date := "'.$end_date.'" e, @currency := '.$currency.' c, @group_id := '.env('GROUP_TRANSFER_ID').' g) alias, report_global_balance');
            }


            $acumAux = 0;
            foreach ($balance as $key => &$value) {
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
                'detail' => $ex//->errorInfo[0]
            ], 400);
        }
    }
}