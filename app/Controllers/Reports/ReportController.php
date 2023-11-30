<?php

namespace App\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\Movement;
use App\Models\Account;
use App\Models\Category;
use App\Models\Budget;

class ReportController extends Controller
{
    static function report(Request $request)
    {
        try {
            $user = $request->user();

            $init_date = $request->query('start_date') ?? Carbon::now()->firstOfMonth()->format("Y-m-d");
            $end_date = $request->query('end_date') ?? Carbon::now()->lastOfMonth()->format("Y-m-d");
            $currency = $request->query('badge_id') ?? $user->badge_id;


            $category = Category::where([
                ['user_id', $user->id],
                ['group_id', '=', env('GROUP_TRANSFER_ID')],
            ])
                ->first();

            // get balance without transferns
            $close_open = DB::select('select open_balance , incomes as income, expensives as expensive, end_balance as utility from (SELECT @user_id := ' . $user->id . ' u, @init_date := "' . $init_date . ' 00:00:00" i, @end_date := "' . $end_date . ' 23:59:59" e, @currency := ' . $currency . ' c, @category_id := ' . $category->id . ' g) alias, report_open_close_balance')[0];

            $incomes = Movement::where([
                ['movements.user_id', $user->id],
                ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')],
                ['amount', '>', 0],
                ['currencies.id', $currency],
            ])
                ->whereDate('date_purchase', '>=', $init_date)
                ->whereDate('date_purchase', '<=', $end_date)
                ->selectRaw('categories.id, categories.name as category,
            ifnull(sum(amount), 0) as amount')
                ->join('categories', 'movements.category_id', 'categories.id')
                ->join('accounts', 'account_id', 'accounts.id')
                ->join('currencies', 'badge_id', 'currencies.id')
                ->groupBy('categories.id', 'categories.name')
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

            if ($incomes_transfer) {
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
                ->selectRaw('categories.id, categories.name as category, categories.category_id as category_father,
            ifnull(sum(amount), 0) as amount')
                ->join('categories', 'movements.category_id', 'categories.id')
                ->join('accounts', 'account_id', 'accounts.id')
                ->join('currencies', 'badge_id', 'currencies.id')
                ->groupBy('categories.id', 'categories.name', 'categories.category_id')
                ->orderByRaw('ifnull(sum(amount), 0)')
                ->get();

            foreach($expensives as &$expense)
            {
                $expense->category_father = Category::where([
                    ['user_id', $user->id],
                    ['id', $expense->category_father]
                ])
                ->pluck('name')
                ->first();
            }

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

            $main_expensive = DB::table('categories as a')
                ->where([
                    ['a.user_id', $user->id],
                    ['a.group_id', '>', 2],
                    ['currencies.id', $currency],
                ])
                ->whereDate('date_purchase', '>=', $init_date)
                ->whereDate('date_purchase', '<=', $end_date)
                ->selectRaw('if(a.category_id is null, a.name, b.name) as category, abs(ifnull(sum(amount), 0)) as amount')
                ->leftJoin('categories as b', 'a.category_id', 'b.id')
                ->join('movements', 'a.id', 'movements.category_id')
                ->join('accounts', 'accounts.id', 'movements.account_id')
                ->join('currencies', 'currencies.id', 'accounts.badge_id')
                ->groupByRaw('if(a.category_id is null, a.name, b.name)')
                ->orderBy('amount', 'desc')
                ->get();

            foreach ($main_expensive as $result) {
                $result->amount = (float)$result->amount;
            }

            if ($expensives_transfer) {
                $main_expensive->push($expensives_transfer);
            }

            $group_expensive = DB::table('groups as a')
                ->where([
                    ['b.user_id', $user->id],
                    ['b.group_id', '<>', env('GROUP_TRANSFER_ID')],
                    ['badge_id', $currency],
                ])
                ->whereDate('date_purchase', '>=', $init_date)
                ->whereDate('date_purchase', '<=', $end_date)
                ->selectRaw('a.id, a.name, ifnull(sum(amount), 0) as amount')
                ->join('categories as b', 'b.group_id', 'a.id')
                ->join('movements', 'b.id', 'movements.category_id')
                ->join('accounts', 'accounts.id', 'movements.account_id')
                ->groupByRaw('a.id, a.name')
                ->orderBy('amount', 'desc')
                ->get();

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
                ->first();

            $saving = 0;
            $income = 0;
            foreach ($group_expensive as &$value) {
                if ($value->name === 'Ingresos') {
                    $income = $close_open->income;
                    $saving = $close_open->income;
                    $value->amount = $close_open->income;
                } else {
                    if ($value->name === 'Gastos Fijos') {
                        $value->amount += $close_open_transfer->expensive;
                    }
                    $value->amount = $value->amount * 1;
                    $value->porcent = $income == 0 ? 0.00 : round(abs($value->amount) / $income * 100, 2);
                    $saving += $value->amount;
                }
            }

            $savingArray = [
                "id" => 0,
                "name" => "Ahorros",
                "amount" => $saving < 0 ? 0 : $saving,
                "porcent" => $saving < 0 || $income == 0 ? 0.00 : round(abs($saving) / $income * 100, 2)
            ];

            $group_expensive->push($savingArray);

            $init = Carbon::parse($init_date);
            $end = Carbon::parse($end_date);

            if ($init->diffInDays($end) < 90) {
                $balance = DB::select('select date, amount from (SELECT @user_id := ' . $user->id . ' u, @init_date := "' . $init_date . ' 00:00:00" i, @end_date := "' . $end_date . ' 23:59:59" e, @currency := ' . $currency . ' c) alias, report_balance');
            } else {
                $balance = DB::select('select date, amount from (SELECT @user_id := ' . $user->id . ' u, @init_date := "' . $init_date . ' 00:00:00" i, @end_date := "' . $end_date . ' 23:59:59" e, @currency := ' . $currency . ' c) alias, report_global_balance');
            }

            $acumAux = 0;
            foreach ($balance as $key => &$value) {
                $prevAmount = $value->amount;
                $value->amount += $acumAux;
                $acumAux += $prevAmount;
            }

            $credit_carts = Account::where([
                ['user_id', $user->id],
                ['badge_id', $currency],
            ])
                ->withBalance()
                ->whereHas('type', fn ($query) => $query->where('id', '=', 3))
                ->get();

            $budgets_monthly = Budget::where([
                ['user_id', $user->id],
                ['badge_id', $currency],
                ['period_id', 1],
                ['year', '>=', $init->year],
                ['year', '<=', $end->year],
            ])
                ->with(['category'])
                ->addSelect([
                    'movement' => \DB::table('movements')
                        ->selectRaw('ifnull(sum(amount), 0)')
                        ->join('accounts', 'accounts.id', 'movements.account_id')
                        ->whereColumn('movements.category_id', 'budgets.category_id')
                        ->whereColumn('movements.user_id', 'budgets.user_id')
                        ->whereColumn('accounts.badge_id', 'budgets.badge_id')
                        ->whereDate('date_purchase', '>=', $init_date)
                        ->whereDate('date_purchase', '<=', $end_date),
                ])
                ->get();

            $diffMonth = $end->diffInMonths($init) + 1;
            
            foreach ($budgets_monthly as &$budget) {
                $budget->amount = $budget->amount * $diffMonth;
                $budget->movement = (float)$budget->movement;
            }

            return [
                'open_close' => $close_open,
                'incomes' => $incomes,
                'main_expensive' => $main_expensive,
                'group_expensive' => $group_expensive,
                'list_expensives' => $expensives,
                'balances' => $balance,
                'credit_carts' => $credit_carts,
                'budget' => $budgets_monthly,
            ];
        } catch (\Illuminate\Database\QueryException $ex) {
            return [
                'message' => 'Datos no obtenidos',
                'detail' => $ex->errorInfo[0]
            ];
        }
    }

    static function balanceByAccount(Request $request, int $account_id)
    {
        $user = $request->user();
        $end_date = Carbon::now()->format('Y-m-d');
        $init_date = Carbon::now()->subDays(15)->format('Y-m-d');

        $balance = DB::select('select date, amount from (SELECT @user_id := ' . $user->id . ' u, @init_date := "' . $init_date . ' 00:00:00" i, @end_date := "' . $end_date . ' 23:59:59" e, @account_id := ' . $account_id . ' a) alias, report_balance_account');

        $acumAux = 0;
        foreach ($balance as $key => &$value) {
            $prevAmount = $value->amount;
            $value->amount += $acumAux;
            $acumAux += $prevAmount;
        }

        return $balance;
    }
}
