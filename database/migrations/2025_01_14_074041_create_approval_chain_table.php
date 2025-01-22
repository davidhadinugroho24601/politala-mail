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
        Schema::create('approval_chain', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mail_id')->nullable(); 
            $table->unsignedBigInteger('group_id')->nullable(); 
            $table->enum('status', ['waiting', 'approved', 'finished', 'denied'])->default('waiting');
            $table->string('notes');



            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('set null');
            $table->foreign('group_id')->references('id')->on('mails')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_chain');
    }
};
