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
        Schema::table('mails', function (Blueprint $table) {
            
            $table->unsignedBigInteger('target_id')->nullable(); 
            $table->unsignedBigInteger('final_id')->nullable(); 

            $table->foreign('target_id')
            ->references('id')
            ->on('groups')
            ->onDelete('set null');  // example: cascade on delete

            $table->foreign('final_id')
            ->references('id')
            ->on('groups')
            ->onDelete('set null'); 

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
