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
            CREATE OR REPLACE VIEW report_global_balance AS
            WITH getValueByDate AS (
                select DATE_FORMAT(date_purchase, \"%b-01\") as date, round(ifnull(sum(amount),0), 2) as amount from movements
                join accounts on accounts.id = movements.account_id
                join currencies on currencies.id = accounts.badge_id and currencies.id = currency()
                where date(date_purchase) >= init_date() and date(date_purchase) <= end_date() and movements.user_id = user_id() and accounts.deleted_at is null
                GROUP by DATE_FORMAT(date_purchase, \"%b-01\")
            ), getinitValue AS (
                SELECT DATE_FORMAT(init_date(), \"%b-%d\") as date, SUM(IFNULL(amount, 0)) AS open_amount from movements 
                join accounts on (accounts.id = account_id)
                inner join `currencies` on `badge_id` = `currencies`.`id` 
                where movements.user_id = user_id() and date(date_purchase) < init_date() 
                and badge_id = currency()
            ), init_money AS (
                SELECT DATE_FORMAT(init_date(), \"%b-%d\") as date, SUM(IFNULL(init_amount, 0)) AS init_amount from accounts
                where user_id = user_id() and badge_id = currency()
                and date(created_at) < init_date()
            ), getDate AS (
                SELECT DATE_FORMAT(db_date, '%b-%d') AS date, day, month FROM time_dimension WHERE db_date BETWEEN init_date() and end_date() and day = 1 order by month
            ),getAcumValue AS (
                SELECT d.date, ifnull(amount,0) + ifnull(open_amount, 0) + ifnull(init_amount, 0) as amount
                FROM getDate d
                left join getValueByDate a on (a.date = d.date)
                LEFT JOIN getinitValue b on (d.date = b.date)
                LEFT JOIN init_money c on (d.date = c.date)
            )
            SELECT * FROM getAcumValue
            ";
    }
   
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    private function dropView(): string
    {
        return "DROP VIEW IF EXISTS `report_global_balance`";
    }
};
