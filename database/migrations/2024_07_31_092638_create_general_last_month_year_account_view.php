<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement($this->createView());
    }
   
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement($this->dropView());
    }

     /**
     * Reverse the migrations.
     *
     * @return void
     */
    private function createView(): string
    {
        return "
            CREATE OR REPLACE VIEW general_last_month_year_account AS
            with get_amount_month as (
                SELECT code , if(month(a.created_at) = month(DATE_SUB(now(), INTERVAL 1 MONTH)) and year(a.created_at) = year(DATE_SUB(now(), INTERVAL 1 MONTH)), a.init_amount, 0) as init_amount, (select ifnull(sum(amount), 0) from movements where account_id = a.id and month(date_purchase) = month(DATE_SUB(now(), INTERVAL 1 MONTH)) and year(date_purchase) = year(DATE_SUB(now(), INTERVAL 1 MONTH))) as balance from accounts a
                join currencies b on (a.badge_id = b.id)
                where a.user_id = user_id() and a.id = account_id()
            ), sum_amount_month as (
                select 'month' as type, code as currency, sum(init_amount + balance) as balance from get_amount_month 
                group by code
                order by sum(init_amount + balance) desc
            ), get_amount_year as (
                SELECT code , if(date(a.created_at) <= DATE(DATE_SUB(now(), INTERVAL 1 YEAR)), a.init_amount, 0) as init_amount, (select ifnull(sum(amount), 0) from movements where account_id = a.id and date(date_purchase) <= DATE(DATE_SUB(now(), INTERVAL 1 YEAR))) as balance from accounts a
                join currencies b on (a.badge_id = b.id)
                where a.user_id = user_id() and a.id = account_id()
            ), sum_amount_year as (
                select 'year' as type, code as currency, sum(init_amount + balance) as balance from get_amount_year 
                group by code
                order by sum(init_amount + balance) desc
            ), union_balance as (
                select * from sum_amount_month
                union
                select * from sum_amount_year
            )
            SELECT * from union_balance
            ";
    }
   
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    private function dropView(): string
    {
        return "DROP VIEW IF EXISTS `general_last_month_year_account`";
    }
};
