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
        Schema::create('user_solutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('crossword_id')->constrained();
            $table->json('solution_data');
            $table->boolean('completed')->default(false);
            $table->integer('score')->default(0);
            $table->integer('time_taken')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_solutions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['crossword_id']);
        });

        Schema::dropIfExists('user_solutions');
    }
};
