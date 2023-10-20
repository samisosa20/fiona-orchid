<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement($this->createView());
    }
   
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement($this->dropView());
    }

     /**
     * Reverse the migrations.
     *
     * @return void
     */
    private function createView(): string
    {
        return "
            CREATE VIEW report_open_close_balance AS
            with get_open_move as (
                select ifnull(sum(amount), 0) as open_move from movements 
                join accounts on (accounts.id = account_id)
                inner join `currencies` on `badge_id` = `currencies`.`id` 
                where movements.user_id = user_id() and date(date_purchase) < init_date() 
                and badge_id = currency()
            ), get_init_open as (
                SELECT ifnull(sum(init_amount), 0) as init_open from accounts
                where user_id = user_id() and badge_id = currency()
                and date(created_at) < init_date()
            ), get_end_move as (
                select ifnull(sum(amount), 0) as end_move from movements 
                join accounts on (accounts.id = account_id)
                inner join `currencies` on `badge_id` = `currencies`.`id` 
                where movements.user_id = user_id() and date(date_purchase) <= end_date() 
                and badge_id = currency()
            ), get_init_end as (
                SELECT ifnull(sum(init_amount), 0) as init_end from accounts
                where user_id = user_id() and badge_id = currency()
                and date(created_at) <= end_date()
            ), sum_open as (
                select open_move + init_open as open_balance, end_move + init_end as end_balance from get_open_move join get_init_open join get_end_move join get_init_end
            ), income_expensive as (
                select ifnull(sum(if(amount > 0, amount, 0)), 0) as incomes, ifnull(sum(if(amount < 0, amount, 0)), 0) as expensives from movements
                join accounts on (accounts.id = account_id)
                where movements.user_id = user_id() and badge_id = currency() and category_id <> category_id()
                and date(date_purchase) >= init_date() and date(date_purchase) <= end_date()
            ), transfers as (
                select ifnull(sum(if(amount > 0, amount, 0)), 0) as incomest, ifnull(sum(if(amount < 0, amount, 0)), 0) as expensivest from movements
                join accounts on (accounts.id = account_id)
                where movements.user_id = user_id() and badge_id = currency() and trm <> 1
                and date(date_purchase) >= init_date() and date(date_purchase) <= end_date()
            ), get_balance as (
                select open_balance, incomes + incomest as incomes, expensives + expensivest as expensives, end_balance from sum_open join income_expensive join transfers
            )
            select * from get_balance
            ";
    }
   
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    private function dropView(): string
    {
        return "DROP VIEW IF EXISTS `report_open_close_balance`";
    }
};
