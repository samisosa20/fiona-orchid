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
        \DB::statement($this->createFunction());
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
    private function createFunction(): string
    {
        return "
            CREATE FUNCTION currency() RETURNS varchar(5)
                NO SQL
                DETERMINISTIC
            return @currency
        ";
    }
   
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    private function dropView(): string
    {
        return "DROP FUNCTION IF EXISTS currency";
    }
};
