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
        Schema::create('investment_appreciations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('investment_id');
            $table->foreign('investment_id')
            ->references('id')
            ->on('investments')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->decimal('amount', $precision = 15, $scale = 2);
            $table->date('date_appreciation');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_appreciations');
    }
};
