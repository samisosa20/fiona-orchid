<?php

namespace App\Controllers\Accounts;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Models\Account;

class AccountController extends Controller
{

    static function totalBalance()
    {
        $balance = DB::select('select * from (SELECT @user_id := '.auth()->user()->id.' i) alias, general_month_year');
        $total_balance = DB::select('select * from (SELECT @user_id := '.auth()->user()->id.' i) alias, general_balance');
        
        foreach ($total_balance as &$value) {
            $month = array_values(array_filter($balance, fn ($v) => $v->type === 'month' && $v->currency === $value->currency));
            if(count($month) > 0) {
                $value->month = $month[0]->balance;
            }
            $year = array_values(array_filter($balance, fn ($v) => $v->type === 'year' && $v->currency === $value->currency));
            if(count($year) > 0) {
                $value->year = $year[0]->balance;
            }
        }

        return $total_balance;
    }
    
    static function totalBalanceByAccount(Account $account)
    {
        $balance_month = DB::select('select * from (SELECT @user_id := '.auth()->user()->id.' i, @account_id := '.$account->id.' a) alias, general_month_year_account');
        $balance_last_month = DB::select('select * from (SELECT @user_id := '.auth()->user()->id.' i, @account_id := '.$account->id.' a) alias, general_last_month_year_account');
        $balance_total = DB::select('select * from (SELECT @user_id := '.auth()->user()->id.'  i, @account_id := '.$account->id.' a) alias, general_balance_account');

        $balance_adjust = $balance_total = array_map(function($element) {
            $element->type = "total";
            return $element;
            }, $balance_total);

        foreach ($balance_adjust as &$value) {
            $month = array_values(array_filter($balance_month, fn ($v) => $v->type === 'month' && $v->currency === $value->currency));
            $last_month = array_values(array_filter($balance_last_month, fn ($v) => $v->type === 'month' && $v->currency === $value->currency));
            if(count($month) > 0) {
                $value->month = $month[0]->balance;
                if(count($last_month) > 0) {
                    $value->last_month = $last_month[0]->balance;
                    $current_month = floatval($month[0]->balance);
                    $last_month = floatval($last_month[0]->balance);
                    $value->month_variation = abs($last_month) == 0 ? 100 : round(($current_month - $last_month) / abs($last_month) * 100, 2);
                }
            }
            $year = array_values(array_filter($balance_month, fn ($v) => $v->type === 'year' && $v->currency === $value->currency));
            $last_year = array_values(array_filter($balance_last_month, fn ($v) => $v->type === 'year' && $v->currency === $value->currency));
            if(count($year) > 0) {
                $value->year = $year[0]->balance;
                if(count($last_year) > 0) {
                    $value->last_year = $last_year[0]->balance;
                    $current_year = floatval($year[0]->balance);
                    $last_year = floatval($last_year[0]->balance);
                    $value->year_variation = abs($last_year) == 0 ? 100 : round(($current_year - $last_year) / abs($last_year) * 100, 2);
                }
            }
        }

        return $balance_adjust;
    }

}