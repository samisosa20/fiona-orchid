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
        Schema::create('heritages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('comercial_amount', $precision = 15, $scale = 2);
            $table->decimal('legal_amount', $precision = 15, $scale = 2);
            $table->unsignedBigInteger('badge_id');
            $table->foreign('badge_id')
            ->references('id')
            ->on('currencies')
            ->onUpdate('cascade');
            $table->integer('year');
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
        Schema::dropIfExists('heritages');
    }
};
