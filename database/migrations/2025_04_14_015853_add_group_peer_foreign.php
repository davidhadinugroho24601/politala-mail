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
        Schema::table('groups', function (Blueprint $table) {

            $table->unsignedBigInteger('peer_id')->nullable();
    
            $table->foreign('peer_id')
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
