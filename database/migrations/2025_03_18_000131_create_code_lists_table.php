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
        Schema::create('code_lists', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->unsignedBigInteger('mail_id');
            $table->foreign('mail_id')
            ->references('id')
            ->on('mails')
            ->onDelete('cascade');  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('code_lists');
    }
};
