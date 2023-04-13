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
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->foreign('account_id')
            ->references('id')
            ->on('accounts')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')
            ->references('id')
            ->on('categories')
            ->onUpdate('cascade');
            $table->string('description')->nullable();
            $table->decimal('amount', $precision = 15, $scale = 2);
            $table->decimal('trm', $precision = 15, $scale = 2)->default(1);
            $table->dateTime('date_purchase');
            $table->unsignedBigInteger('transfer_id')->nullable();
            $table->foreign('transfer_id')
            ->references('id')
            ->on('movements')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->unsignedBigInteger('event_id')->nullable();
            $table->foreign('event_id')
            ->references('id')
            ->on('events')
            ->onUpdate('cascade')
            ->onDelete('set null');
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
        Schema::dropIfExists('movements');
    }
};
