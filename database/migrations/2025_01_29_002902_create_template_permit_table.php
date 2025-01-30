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
        Schema::create('template_permit', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id'); 
            $table->unsignedBigInteger('user_id'); 
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('mail_templates')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_permit');
    }
};
