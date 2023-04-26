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
        Schema::create('time_dimension', function (Blueprint $table) {
            $table->id();
            $table->date('db_date');
            $table->integer('year');
            $table->integer('month');
            $table->integer('day');
            $table->integer('quarter');
            $table->integer('week');
            $table->string('day_name', 9);
            $table->string('month_name', 9);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_dimension');
    }
};
