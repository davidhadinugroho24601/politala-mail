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
        Schema::table('attachments', function (Blueprint $table) {
            // $table->id();
            $table->string('description');
            // $table->text('path');
            // $table->unsignedBigInteger('mail_id')->nullable(); 
            // $table->timestamps();


            // $table->foreign('mail_id')
            // ->references('id')
            // ->on('mails')
            // ->onDelete('set null');  // example: cascade on delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
