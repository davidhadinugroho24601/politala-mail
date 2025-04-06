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
        Schema::table('approval_chain', function (Blueprint $table) {

        $table->unsignedBigInteger('path_detail_id')->nullable();

        $table->foreign('path_detail_id')
            ->references('id')
            ->on('path_details')
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
