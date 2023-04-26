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
            CREATE VIEW report_balance AS
            select DATE_FORMAT(time_dimension.db_date, \"%m-%d\") as date, round(ifnull(sum(amount),0), 2) as amount from time_dimension
            left join movements on (time_dimension.db_date = date(date_purchase) and user_id = user_id())
            left join accounts on accounts.id = movements.account_id
            left join currencies on currencies.id = accounts.badge_id and currencies.code = currency()
            where date(time_dimension.db_date) >= init_date() and date(time_dimension.db_date) <= end_date()
            GROUP by DATE_FORMAT(time_dimension.db_date, \"%m-%d\")
            ";
    }
   
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    private function dropView(): string
    {
        return "DROP VIEW IF EXISTS `report_balance`";
    }
};
