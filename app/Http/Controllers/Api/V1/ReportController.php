<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

use App\Models\Movement;
use App\Models\Account;
use App\Models\Category;

class ReportController extends Controller
{
    public function report(Request $request)
    {
        try{
            $user = JWTAuth::user();

            $init_date = '2023-04-01';
            $end_date = '2023-04-30';

            $close_open = Movement::where([
                ['movements.user_id', $user->id],
                ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')]
            ])
            ->whereDate('date_purchase', '>=', $init_date)
            ->whereDate('date_purchase', '<=', $end_date)
            ->selectRaw('currencies.code as currency,
            cast(ifnull(sum(if(amount > 0 , amount, 0)), 0) as float) as income,
            cast(ifnull(sum(if(amount < 0 , amount, 0)), 0) as float) as expensive,
            cast(ifnull(sum(amount), 0) as float) as utility')
            ->join('accounts', 'account_id', 'accounts.id')
            ->join('currencies', 'badge_id', 'currencies.id')
            ->join('categories', 'movements.category_id', 'categories.id')
            ->groupBy('currencies.code')
            ->get();

            foreach ($close_open as &$value) {
                $open_balance = Movement::selectRaw('cast(ifnull(sum(amount), 0) as float) as amount')
                ->where([
                    ['movements.user_id', $user->id],
                    ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')],
                    ['currencies.code', $value->currency],
                ])
                ->whereDate('date_purchase', '<', $init_date)
                ->join('accounts', 'account_id', 'accounts.id')
                ->join('currencies', 'badge_id', 'currencies.id')
                ->join('categories', 'movements.category_id', 'categories.id')
                ->first();

                $open_init_amount = Account::where([
                    ['user_id', $user->id],
                    ['currencies.code', $value->currency],
                ])
                ->withTrashed()
                ->whereDate('accounts.created_at', '<', $init_date)
                ->selectRaw('cast(ifnull(sum(init_amount), 0) as float) as amount')
                ->join('currencies', 'badge_id', 'currencies.id')
                ->first();
                
                $income_init_amount = Account::where([
                    ['user_id', $user->id],
                    ['currencies.code', $value->currency],
                ])
                ->whereDate('accounts.created_at', '>=', $init_date)
                ->whereDate('accounts.created_at', '<=', $end_date)
                ->selectRaw('cast(ifnull(sum(init_amount), 0) as float) as amount')
                ->join('currencies', 'badge_id', 'currencies.id')
                ->first();

                $value->open_balance = $open_balance->amount + $open_init_amount->amount;
                $value->income = $value->income + $income_init_amount->amount;
                $value->utility = $value->utility + $income_init_amount->amount + $open_balance->amount + $open_init_amount->amount;
            }

            $incomes = Movement::where([
                ['movements.user_id', $user->id],
                ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')],
                ['amount', '>', 0]
            ])
            ->whereDate('date_purchase', '>=', $init_date)
            ->whereDate('date_purchase', '<=', $end_date)
            ->selectRaw('categories.name as category, currencies.code as currency,
            cast(ifnull(sum(amount), 0) as float) as amount')
            ->join('categories', 'movements.category_id', 'categories.id')
            ->join('accounts', 'account_id', 'accounts.id')
            ->join('currencies', 'badge_id', 'currencies.id')
            ->groupBy('categories.name', 'currencies.code')
            ->orderByRaw('currencies.code, ifnull(sum(amount), 0) desc')
            ->get();
            
            $expensives = Movement::where([
                ['movements.user_id', $user->id],
                ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')],
                ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')],
                ['amount', '<', 0]
            ])
            ->whereDate('date_purchase', '>=', $init_date)
            ->whereDate('date_purchase', '<=', $end_date)
            ->selectRaw('categories.name as category, currencies.code as currency,
            cast(ifnull(sum(amount), 0) as float) as amount')
            ->join('categories', 'movements.category_id', 'categories.id')
            ->join('accounts', 'account_id', 'accounts.id')
            ->join('currencies', 'badge_id', 'currencies.id')
            ->groupBy('categories.name', 'currencies.code')
            ->orderByRaw('currencies.code, ifnull(sum(amount), 0)')
            ->get();

            $main_expensive = Category::where([
                ['user_id', $user->id],
                ['group_id', '<>', env('GROUP_TRANSFER_ID')],
            ])
            ->whereNull('category_id')
            ->get();

            return response()->json([
                'open_close' => $close_open,
                'incomes' => $incomes,
                'main_expensive' => $main_expensive,
                'list_expensives' => $expensives,
            ]);
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' => 'Datos no obtenidos',
                'detail' => $ex//->errorInfo[0]
            ], 400);
        }
    }
}