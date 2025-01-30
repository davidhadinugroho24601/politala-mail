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
        Schema::create('mail_code_availability', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id'); 
            $table->unsignedBigInteger('code_id'); 
            $table->timestamps();


            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->foreign('code_id')->references('id')->on('mail_code')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_code_availability');
    }
};
