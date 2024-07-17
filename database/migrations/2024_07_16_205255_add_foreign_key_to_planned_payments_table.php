<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('planned_payments', function (Blueprint $table) {
            $table->dropForeign(['account_id']);

            // Agrega la clave foránea con `ON UPDATE CASCADE`
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planned_payments', function (Blueprint $table) {
            Schema::table('planned_payments', function (Blueprint $table) {
                $table->dropForeign(['account_id']);
            });
        });
    }
};
