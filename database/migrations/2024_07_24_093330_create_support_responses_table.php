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
        Schema::create('support_responses', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->unsignedBigInteger('support_id');
            $table->foreign('support_id')
            ->references('id')
            ->on('supports')
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
        Schema::dropIfExists('support_responses');
    }
};
